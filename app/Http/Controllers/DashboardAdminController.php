<?php

namespace App\Http\Controllers;

use App\Mail\PaymentReminder;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Kas;
use App\Models\JenisKas;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class DashboardAdminController extends Controller
{
    public function index(Request $request)
    {
        // Ambil tahun yang dipilih, default tahun sekarang
        $tahunDipilih = $request->get('tahun', Carbon::now()->year);

        // ========== STATISTIK UTAMA ==========
        $totalPemasukan = Kas::where('tipe', 'pemasukan')->sum('jumlah');
        $totalPengeluaran = Kas::where('tipe', 'pengeluaran')->sum('jumlah');
        $saldo = $totalPemasukan - $totalPengeluaran;
        $totalAnggota = User::where('role', '!=', 'admin')->count();

        // ========== IURAN BULAN INI ==========
        $bulanIni = Carbon::now();
        $userSudahBayarBulanIni = Kas::where('tipe', 'pemasukan')
            ->whereYear('tanggal', $bulanIni->year)
            ->whereMonth('tanggal', $bulanIni->month)
            ->with('user')
            ->get()
            ->unique('user_id');
        $semuaUser = User::where('role', '!=', 'admin')->get();
        $userBelumBayarBulanIni = $semuaUser->filter(function ($user) use ($userSudahBayarBulanIni) {
            return !$userSudahBayarBulanIni->pluck('user_id')->contains($user->id);
        });
        $iuranBulanIni = [
            'sudah_bayar' => $userSudahBayarBulanIni,
            'belum_bayar' => $userBelumBayarBulanIni,
            'total_sudah' => $userSudahBayarBulanIni->count(),
            'total_belum' => $userBelumBayarBulanIni->count()
        ];

        // ========== GRAFIK PEMASUKAN & CASHFLOW (DIPERBAIKI) ==========
        $chartDataPemasukan = [];
        $chartDataPengeluaran = [];
        $chartCashflow = [];
        $bulanLabels = [];

        // Loop untuk 12 bulan dalam tahun yang dipilih
        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $tanggal = Carbon::create($tahunDipilih, $bulan, 1);
            $bulanLabels[] = $tanggal->format('M Y');

            $pemasukankuanganBulan = Kas::where('tipe', 'pemasukan')
                ->whereYear('tanggal', $tahunDipilih)
                ->whereMonth('tanggal', $bulan)
                ->sum('jumlah');

            $pengeluarankuanganBulan = Kas::where('tipe', 'pengeluaran')
                ->whereYear('tanggal', $tahunDipilih)
                ->whereMonth('tanggal', $bulan)
                ->sum('jumlah');

            $chartDataPemasukan[] = $pemasukankuanganBulan;
            $chartDataPengeluaran[] = $pengeluarankuanganBulan;
            $chartCashflow[] = $pemasukankuanganBulan - $pengeluarankuanganBulan;
        }

        // Debug: Tampilkan data untuk memastikan
        // dd($chartDataPemasukan, $chartCashflow, $bulanLabels);

        // ========== TRACKING IURAN 4 TAHUN TERAKHIR ==========
        $tahunSekarang = Carbon::now()->year;
        $dataIuran = [];
        for ($tahun = $tahunSekarang; $tahun >= $tahunSekarang - 3; $tahun--) {
            $dataIuran[$tahun] = [];
            for ($bulan = 1; $bulan <= 12; $bulan++) {
                $userSudahBayar = Kas::where('tipe', 'pemasukan')
                    ->whereYear('tanggal', $tahun)
                    ->whereMonth('tanggal', $bulan)
                    ->with('user')
                    ->get()
                    ->unique('user_id');
                $userSudahBayarIds = $userSudahBayar->pluck('user_id')->toArray();
                $userBelumBayar = $semuaUser->filter(function ($user) use ($userSudahBayarIds) {
                    return !in_array($user->id, $userSudahBayarIds);
                });
                $dataIuran[$tahun][$bulan] = [
                    'sudah_bayar' => $userSudahBayar->pluck('user')->values(),
                    'belum_bayar' => $userBelumBayar->values(),
                    'total_sudah' => $userSudahBayar->count(),
                    'total_belum' => $userBelumBayar->count()
                ];
            }
        }

        // ========== PERFORMA ANGGOTA ==========
        $performaUser = $semuaUser->map(function ($user) {
            $tepatWaktu = 0;
            $terlambat = 0;
            for ($i = 11; $i >= 0; $i--) {
                $tanggal = Carbon::now()->subMonths($i);
                $pembayaran = Kas::where('user_id', $user->id)
                    ->where('tipe', 'pemasukan')
                    ->whereYear('tanggal', $tanggal->year)
                    ->whereMonth('tanggal', $tanggal->month)
                    ->first();
                if ($pembayaran) {
                    if ($pembayaran->tanggal->day <= 15) {
                        $tepatWaktu++;
                    } else {
                        $terlambat++;
                    }
                }
            }
            $totalPembayaran = $tepatWaktu + $terlambat;
            $persentaseTepatWaktu = $totalPembayaran > 0 ? ($tepatWaktu / $totalPembayaran) * 100 : 0;
            return [
                'user' => $user,
                'tepat_waktu' => $tepatWaktu,
                'terlambat' => $terlambat,
                'persentase_tepat_waktu' => $persentaseTepatWaktu
            ];
        })->filter()->sortByDesc('persentase_tepat_waktu');

        // ========== PEMASUKAN & PENGELUARAN PER JENIS KAS ==========
        $pemasukankuanganPerJenis = JenisKas::with(['kas' => function ($query) {
            $query->where('tipe', 'pemasukan');
        }])->get()->map(function ($jenisKas) {
            return [
                'id' => $jenisKas->id,
                'nama' => $jenisKas->nama_jenis_kas,
                'total' => $jenisKas->kas->sum('jumlah')
            ];
        });
        $pengeluarankuanganPerJenis = Kas::where('tipe', 'pengeluaran')
            ->select('keterangan', DB::raw('SUM(jumlah) as total'))
            ->groupBy('keterangan')
            ->orderBy('total', 'desc')
            ->get();
        // dd($chartDataPengeluaran);
        return view('admin.dashboard.index', compact(
            'totalPemasukan',
            'totalPengeluaran',
            'saldo',
            'totalAnggota',
            'iuranBulanIni',
            'chartDataPemasukan',
            'chartCashflow',
            'bulanLabels',
            'tahunDipilih',
            'dataIuran',
            'performaUser',
            'pemasukankuanganPerJenis',
            'pengeluarankuanganPerJenis',
            'chartDataPengeluaran'
        ));
    }

    public function getBelumBayarDetail(Request $request)
    {
        $tahun = $request->get('tahun', Carbon::now()->year);
        $bulan = $request->get('bulan', Carbon::now()->month);
        if (!is_numeric($tahun) || !is_numeric($bulan)) {
            return response()->json(['success' => false, 'message' => 'Parameter tahun atau bulan tidak valid'], 400);
        }
        $userSudahBayar = Kas::where('tipe', 'pemasukan')
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->pluck('user_id')
            ->unique()
            ->toArray();
        $userBelumBayar = User::where('role', '!=', 'admin')
            ->whereNotIn('id', $userSudahBayar)
            ->select('id', 'name', 'email', 'id_anggota', 'no_hp')
            ->get();
        $userSudahBayarDetail = Kas::where('tipe', 'pemasukan')
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->with('user:id,name,email,id_anggota,no_hp')
            ->get()
            ->unique('user_id')
            ->map(function ($kas) {
                return [
                    'id' => $kas->user->id,
                    'name' => $kas->user->name,
                    'email' => $kas->user->email,
                    'id_anggota' => $kas->user->id_anggota,
                    'no_hp' => $kas->user->no_hp,
                    'tanggal_bayar' => $kas->tanggal->format('d M Y'),
                    'jumlah' => $kas->jumlah,
                    'tepat_waktu' => $kas->tanggal->day <= 15
                ];
            });
        $namaBulan = Carbon::create($tahun, $bulan, 1)->format('F Y');
        return response()->json([
            'success' => true,
            'data' => [
                'tahun' => $tahun,
                'bulan' => $bulan,
                'nama_bulan' => $namaBulan,
                'belum_bayar' => $userBelumBayar,
                'sudah_bayar' => $userSudahBayarDetail,
                'total_belum' => $userBelumBayar->count(),
                'total_sudah' => $userSudahBayarDetail->count(),
                'total_anggota' => $userBelumBayar->count() + $userSudahBayarDetail->count()
            ]
        ]);
    }

    public function getChartData(Request $request)
    {
        // Gunakan tahun saat ini sebagai default jika tidak ada input
        $tahun = $request->get('tahun', Carbon::now()->year);

        // Validasi tahun
        if (!is_numeric($tahun)) {
            return response()->json(['success' => false, 'message' => 'Parameter tahun tidak valid'], 400);
        }

        $chartDataPemasukan = [];
        $chartDataPengeluaran = [];
        $chartCashflow = [];
        $bulanLabels = [];

        // Loop untuk 12 bulan dalam tahun yang dipilih
        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $tanggal = Carbon::create($tahun, $bulan, 1);
            $bulanLabels[] = $tanggal->format('M Y');

            // DIPERBAIKI: Query yang benar
            $pemasukan = Kas::where('tipe', 'pemasukan')
                ->whereYear('tanggal', $tahun)
                ->whereMonth('tanggal', $bulan)
                ->sum('jumlah');

            $pengeluaran = Kas::where('tipe', 'pengeluaran')
                ->whereYear('tanggal', $tahun)
                ->whereMonth('tanggal', $bulan)
                ->sum('jumlah');

            $chartDataPemasukan[] = $pemasukan;
            $chartDataPengeluaran[] = $pengeluaran;
            $chartCashflow[] = $pemasukan - $pengeluaran;
        }

        // Debug log
        Log::info("Chart data untuk tahun {$tahun}:", [
            'pemasukan' => $chartDataPemasukan,
            'pengeluaran' => $chartDataPengeluaran,
            'cashflow' => $chartCashflow
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'tahun' => $tahun,
                'bulan_labels' => $bulanLabels,
                'chart_pemasukan' => $chartDataPemasukan,
                'chart_pengeluaran' => $chartDataPengeluaran,
                'chart_cashflow' => $chartCashflow
            ]
        ]);
    }

    public function getTahunTersedia()
    {
        try {
            // Cek apakah tabel kas ada dan memiliki data
            $tahunTersedia = Kas::selectRaw('YEAR(tanggal) as tahun')
                ->distinct()
                ->whereNotNull('tanggal') // Pastikan tanggal tidak null
                ->orderBy('tahun', 'desc')
                ->pluck('tahun');

            $tahunSekarang = Carbon::now()->year;

            // Konversi ke array dan pastikan semua adalah integer
            $tahunArray = $tahunTersedia->map(function ($tahun) {
                return (int) $tahun;
            })->toArray();

            // Tambahkan tahun sekarang jika belum ada
            if (!in_array($tahunSekarang, $tahunArray)) {
                array_unshift($tahunArray, $tahunSekarang);
            }

            // Urutkan descending
            rsort($tahunArray);

            return response()->json([
                'success' => true,
                'data' => array_values($tahunArray)
            ]);
        } catch (\Exception $e) {
            // Log error untuk debugging
            Log::error('Error di getTahunTersedia: ' . $e->getMessage());

            // Return fallback data
            return response()->json([
                'success' => true,
                'data' => [Carbon::now()->year, Carbon::now()->year - 1, Carbon::now()->year - 2]
            ]);
        }
    }
    public function sendReminderEmail(Request $request)
    {
        $userId = $request->input('user_id');
        $user = User::find($userId);

        if ($user) {
            try {
                Mail::to($user->email)->send(new PaymentReminder($user));
                return response()->json(['success' => true, 'message' => 'Email peringatan pembayaran berhasil dikirim kepada ' . $user->name], 200);
            } catch (\Exception $e) {
                Log::error('Gagal mengirim email peringatan: ' . $e->getMessage());
                return response()->json(['success' => false, 'message' => 'Gagal mengirim email peringatan. Silakan coba lagi.'], 500);
            }
        }

        return response()->json(['success' => false, 'message' => 'Anggota tidak ditemukan.'], 404);
    }
}
