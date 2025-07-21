<?php

namespace App\Http\Controllers;

use App\Exports\AllDashboardDataExport;
use App\Mail\BulkPaymentReminder;
use App\Mail\PaymentReminder;
use App\Models\WajibkasProgress;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Kas;
use App\Models\JenisKas;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;

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
        $totalAnggota = User::where('role', 'user')->count();

        $bulanIni = Carbon::now();
        $jenisKasSukarela = JenisKas::where('tipe_iuran', 'sukarela')
            ->where('status', 'aktif')
            ->pluck('id');

        $iuranSukarelaBulanIni = [
            'total_pemasukan' => 0,
            'jumlah_transaksi' => 0,
            'transaksi' => []
        ];

        if ($jenisKasSukarela->isNotEmpty()) {
            $transaksiSukarela = Kas::where('tipe', 'pemasukan')
                ->whereIn('jenis_kas_id', $jenisKasSukarela)
                ->whereYear('tanggal', $bulanIni->year)
                ->whereMonth('tanggal', $bulanIni->month)
                ->with(['user', 'jenisKas'])
                ->get();

            $iuranSukarelaBulanIni = [
                'total_pemasukan' => $transaksiSukarela->sum('jumlah'),
                'jumlah_transaksi' => $transaksiSukarela->count(),
                'transaksi' => $transaksiSukarela
            ];
        }
        // ========== IURAN BULAN INI ==========
        $bulanIni = Carbon::now();
        $jenisKasWajib = JenisKas::where('tipe_iuran', 'wajib')->where('status', 'aktif')->get();

        $iuranBulanIni = [
            'sudah_bayar' => collect(),
            'belum_bayar' => collect(),
            'total_sudah' => 0,
            'total_belum' => $totalAnggota
        ];

        // Hitung yang sudah bayar iuran wajib bulan ini
        if ($jenisKasWajib->isNotEmpty()) {
            $userSudahBayarBulanIni = Kas::where('tipe', 'pemasukan')
                ->whereIn('jenis_kas_id', $jenisKasWajib->pluck('id'))
                ->whereYear('tanggal', $bulanIni->year)
                ->whereMonth('tanggal', $bulanIni->month)
                ->with('user')
                ->get()
                ->unique('user_id');

            $semuaUser = User::where('role', 'user')->get();
            $userBelumBayarBulanIni = $semuaUser->filter(function ($user) use ($userSudahBayarBulanIni) {
                return !$userSudahBayarBulanIni->pluck('user_id')->contains($user->id);
            });

            $iuranBulanIni = [
                'sudah_bayar' => $userSudahBayarBulanIni,
                'belum_bayar' => $userBelumBayarBulanIni,
                'total_sudah' => $userSudahBayarBulanIni->count(),
                'total_belum' => $userBelumBayarBulanIni->count()
            ];
        }

        // ========== GRAFIK PEMASUKAN & CASHFLOW ==========
        $chartDataPemasukan = [];
        $chartDataPengeluaran = [];
        $chartCashflow = [];
        $bulanLabels = [];

        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $tanggal = Carbon::create($tahunDipilih, $bulan, 1);
            $bulanLabels[] = $tanggal->format('M Y');

            $pemasukan = Kas::where('tipe', 'pemasukan')
                ->whereYear('tanggal', $tahunDipilih)
                ->whereMonth('tanggal', $bulan)
                ->sum('jumlah');

            $pengeluaran = Kas::where('tipe', 'pengeluaran')
                ->whereYear('tanggal', $tahunDipilih)
                ->whereMonth('tanggal', $bulan)
                ->sum('jumlah');

            $chartDataPemasukan[] = $pemasukan;
            $chartDataPengeluaran[] = $pengeluaran;
            $chartCashflow[] = $pemasukan - $pengeluaran;
        }



        // ========== TRACKING IURAN BULANAN (LOGIKA DIPERBAIKI) ==========
        $trackingBulanan = [];
        $semuaUser = User::where('role', 'user')->get();
        $totalAnggota = $semuaUser->count();

        foreach ($jenisKasWajib as $jenisKas) {
            $target = $jenisKas->target_lunas;
            $nominalPerBulan = $jenisKas->nominal_wajib ?? 10000; // Ambil dari nominal_wajib

            // Hitung berapa bulan diperlukan untuk lunas
            $bulanDibutuhkan = ceil($target / $nominalPerBulan);

            // Tracking untuk setiap user
            foreach ($semuaUser as $user) {
                $userId = $user->id;

                // Hitung total yang sudah dibayar user untuk jenis kas ini
                $totalBayarUser = Kas::where('user_id', $userId)
                    ->where('jenis_kas_id', $jenisKas->id)
                    ->where('tipe', 'pemasukan')
                    ->sum('jumlah');

                // Hitung berapa bulan yang sudah "tercover" dari pembayaran
                $bulanTercover = min($bulanDibutuhkan, floor($totalBayarUser / $nominalPerBulan));

                // Cari tanggal pembayaran pertama untuk menentukan start date
                $pembayaranPertama = Kas::where('user_id', $userId)
                    ->where('jenis_kas_id', $jenisKas->id)
                    ->where('tipe', 'pemasukan')
                    ->orderBy('tanggal', 'asc')
                    ->first();

                if ($pembayaranPertama) {
                    $startDate = Carbon::parse($pembayaranPertama->tanggal)->startOfMonth();

                    // Generate tracking untuk bulan-bulan yang tercover
                    for ($i = 0; $i < $bulanDibutuhkan; $i++) {
                        $currentMonth = $startDate->copy()->addMonths($i);
                        $tahun = $currentMonth->year;
                        $bulan = $currentMonth->month;

                        // Inisialisasi array jika belum ada
                        if (!isset($trackingBulanan[$jenisKas->id][$tahun][$bulan])) {
                            $trackingBulanan[$jenisKas->id][$tahun][$bulan] = [
                                'nama_kas' => $jenisKas->nama_jenis_kas,
                                'target' => $target,
                                'nominal_per_bulan' => $nominalPerBulan,
                                'sudah_bayar' => collect(),
                                'belum_bayar' => collect($semuaUser),
                                'total_sudah' => 0,
                                'total_belum' => $totalAnggota,
                                'total_terkumpul' => 0,
                            ];
                        }

                        // Jika bulan ini tercover oleh pembayaran user
                        if ($i < $bulanTercover) {
                            // Pindahkan user dari belum_bayar ke sudah_bayar
                            $trackingBulanan[$jenisKas->id][$tahun][$bulan]['sudah_bayar']->push($user);
                            $trackingBulanan[$jenisKas->id][$tahun][$bulan]['belum_bayar'] =
                                $trackingBulanan[$jenisKas->id][$tahun][$bulan]['belum_bayar']->reject(function ($u) use ($user) {
                                    return $u->id === $user->id;
                                });
                        }
                    }
                } else {
                    // Jika belum pernah bayar, masukkan ke semua bulan sebagai belum_bayar
                    for ($i = 0; $i < $bulanDibutuhkan; $i++) {
                        $currentMonth = Carbon::now()->addMonths($i);
                        $tahun = $currentMonth->year;
                        $bulan = $currentMonth->month;

                        if (!isset($trackingBulanan[$jenisKas->id][$tahun][$bulan])) {
                            $trackingBulanan[$jenisKas->id][$tahun][$bulan] = [
                                'nama_kas' => $jenisKas->nama_jenis_kas,
                                'target' => $target,
                                'nominal_per_bulan' => $nominalPerBulan,
                                'sudah_bayar' => collect(),
                                'belum_bayar' => collect([$user]),
                                'total_sudah' => 0,
                                'total_belum' => 1,
                                'total_terkumpul' => 0,
                            ];
                        } else {
                            $trackingBulanan[$jenisKas->id][$tahun][$bulan]['belum_bayar']->push($user);
                        }
                    }
                }
            }

            // Hitung ulang total untuk setiap bulan
            foreach ($trackingBulanan[$jenisKas->id] as $tahun => $bulanData) {
                foreach ($bulanData as $bulan => $data) {
                    $trackingBulanan[$jenisKas->id][$tahun][$bulan]['sudah_bayar'] =
                        $data['sudah_bayar']->unique('id');
                    $trackingBulanan[$jenisKas->id][$tahun][$bulan]['belum_bayar'] =
                        $data['belum_bayar']->unique('id');
                    $trackingBulanan[$jenisKas->id][$tahun][$bulan]['total_sudah'] =
                        $data['sudah_bayar']->count();
                    $trackingBulanan[$jenisKas->id][$tahun][$bulan]['total_belum'] =
                        $data['belum_bayar']->count();
                    $trackingBulanan[$jenisKas->id][$tahun][$bulan]['total_terkumpul'] =
                        $data['sudah_bayar']->count() * $nominalPerBulan;
                }
            }

            // Urutkan tahun dan bulan
            if (isset($trackingBulanan[$jenisKas->id])) {
                ksort($trackingBulanan[$jenisKas->id]);
                foreach ($trackingBulanan[$jenisKas->id] as &$tahunData) {
                    ksort($tahunData);
                }
            }
        }

        // ========== TRACKING IURAN WAJIB (PROGRESS PELUNASAN) - DIPERBAIKI ==========
        $trackingWajib = [];
        foreach ($jenisKasWajib as $jenisKas) {
            $semuaUser = User::where('role', 'user')->get();
            $targetPerUser = $jenisKas->target_lunas; // Target 600k per user

            $progressDetail = [];
            $totalUserLunas = 0;
            $totalTerkumpulSemuaUser = 0;

            foreach ($semuaUser as $user) {
                // Hitung total yang sudah dibayar user ini untuk jenis kas ini
                $totalBayarUser = Kas::where('user_id', $user->id)
                    ->where('jenis_kas_id', $jenisKas->id)
                    ->where('tipe', 'pemasukan')
                    ->sum('jumlah');

                // Tentukan status user
                $status = $totalBayarUser >= $targetPerUser ? 'lunas' : 'belum_lunas';
                if ($status === 'lunas') {
                    $totalUserLunas++;
                }

                // Hitung persentase progress per user
                $persentase = $targetPerUser > 0 ? min(100, ($totalBayarUser / $targetPerUser) * 100) : 0;

                // Tambahkan ke total terkumpul semua user
                $totalTerkumpulSemuaUser += $totalBayarUser;

                $progressDetail[] = [
                    'user' => $user,
                    'total_terbayar' => $totalBayarUser,
                    'status' => $status,
                    'persentase' => round($persentase, 2),
                    'sisa_bayar' => max(0, $targetPerUser - $totalBayarUser),
                    'target_user' => $targetPerUser
                ];
            }

            // Urutkan berdasarkan persentase tertinggi
            usort($progressDetail, function ($a, $b) {
                return $b['persentase'] <=> $a['persentase'];
            });

            $trackingWajib[$jenisKas->nama_jenis_kas] = [
                'target_per_user' => $targetPerUser,
                'total_anggota' => $semuaUser->count(),
                'total_user_lunas' => $totalUserLunas,
                'total_user_belum_lunas' => $semuaUser->count() - $totalUserLunas,
                'persentase_user_lunas' => $semuaUser->count() > 0 ?
                    round(($totalUserLunas / $semuaUser->count()) * 100, 2) : 0,
                'total_terkumpul_semua_user' => $totalTerkumpulSemuaUser,
                'target_total_semua_user' => $targetPerUser * $semuaUser->count(),
                'progress_keseluruhan' => ($targetPerUser * $semuaUser->count()) > 0 ?
                    round(($totalTerkumpulSemuaUser / ($targetPerUser * $semuaUser->count())) * 100, 2) : 0,
                'progress_detail' => $progressDetail
            ];
        }

        // ========== PERFORMA ANGGOTA (BULAN INI) ==========
        $performaUser = User::where('role', 'user')->get()->map(function ($user) use ($jenisKasWajib) {
            $tepatWaktu = 0;
            $terlambat = 0;
            $bulanIni = Carbon::now();

            foreach ($jenisKasWajib as $jenisKas) {
                $pembayaran = Kas::where('user_id', $user->id)
                    ->where('jenis_kas_id', $jenisKas->id)
                    ->where('tipe', 'pemasukan')
                    ->whereYear('tanggal', $bulanIni->year)
                    ->whereMonth('tanggal', $bulanIni->month)
                    ->get();

                foreach ($pembayaran as $bayar) {
                    if ($bayar->tanggal->day <= 15) {
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
        })->sortByDesc('persentase_tepat_waktu');

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

        $pengeluarankuanganPerJenis = JenisKas::with(['kas' => function ($query) {
            $query->where('tipe', 'pengeluaran');
        }])->get()->map(function ($jenisKas) {
            return [
                'id' => $jenisKas->id,
                'nama' => $jenisKas->nama_jenis_kas,
                'total' => $jenisKas->kas->sum('jumlah'),
                'keterangan' => $jenisKas->nama_jenis_kas
            ];
        });

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
            'trackingBulanan',
            'trackingWajib',
            'performaUser',
            'pemasukankuanganPerJenis',
            'pengeluarankuanganPerJenis',
            'chartDataPengeluaran',
            'iuranSukarelaBulanIni'

        ));
    }
    public function getIuranSukarelaDetail(Request $request)
    {
        $bulanIni = Carbon::now();
        $jenisKasSukarela = JenisKas::where('tipe_iuran', 'sukarela')
            ->where('status', 'aktif')
            ->pluck('id');

        $transaksiSukarela = Kas::where('tipe', 'pemasukan')
            ->whereIn('jenis_kas_id', $jenisKasSukarela)
            ->whereYear('tanggal', $bulanIni->year)
            ->whereMonth('tanggal', $bulanIni->month)
            ->with(['user', 'jenisKas'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'bulan' => $bulanIni->translatedFormat('F Y'),
                'total_pemasukan' => $transaksiSukarela->sum('jumlah'),
                'jumlah_transaksi' => $transaksiSukarela->count(),
                'transaksi' => $transaksiSukarela->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'user_name' => $item->user->name ?? 'Anonim',
                        'user_email' => $item->user->email ?? '-',
                        'jenis_kas' => $item->jenisKas->nama_jenis_kas ?? '-',
                        'jumlah' => $item->jumlah,
                        'tanggal' => $item->tanggal->format('d M Y'),
                        'keterangan' => $item->keterangan
                    ];
                }),
                'per_jenis_kas' => $transaksiSukarela->groupBy('jenis_kas_id')->map(function ($group) {
                    return [
                        'nama_jenis_kas' => $group->first()->jenisKas->nama_jenis_kas ?? '-',
                        'total' => $group->sum('jumlah'),
                        'jumlah_transaksi' => $group->count()
                    ];
                })->values()
            ]
        ]);
    }


    // ========== METHOD UNTUK GET DETAIL BELUM BAYAR (DIPERBAIKI) ==========
    public function getBelumBayarDetail(Request $request)
    {
        $tahun = $request->get('tahun', Carbon::now()->year);
        $bulan = $request->get('bulan', Carbon::now()->month);
        $jenisKasIdParam = $request->get('jenis_kas_id');

        if (!is_numeric($tahun) || !is_numeric($bulan)) {
            return response()->json(['success' => false, 'message' => 'Parameter tahun atau bulan tidak valid'], 400);
        }

        $jenisKas = JenisKas::find($jenisKasIdParam);
        if (!$jenisKas) {
            return response()->json(['success' => false, 'message' => 'Jenis kas tidak ditemukan'], 404);
        }

        $target = $jenisKas->target_lunas;
        $nominalPerBulan = $jenisKas->nominal_wajib ?? 10000;
        $bulanDibutuhkan = ceil($target / $nominalPerBulan);
        $targetDate = Carbon::create($tahun, $bulan, 1)->startOfMonth();

        $semuaUser = User::where('role', 'user')->get();
        $userSudahBayar = collect();
        $userBelumBayar = collect();

        foreach ($semuaUser as $user) {
            $totalBayarUser = Kas::where('user_id', $user->id)
                ->where('jenis_kas_id', $jenisKas->id)
                ->where('tipe', 'pemasukan')
                ->sum('jumlah');

            $bulanTercover = floor($totalBayarUser / $nominalPerBulan);

            // Cari pembayaran pertama untuk menentukan start date
            $pembayaranPertama = Kas::where('user_id', $user->id)
                ->where('jenis_kas_id', $jenisKas->id)
                ->where('tipe', 'pemasukan')
                ->orderBy('tanggal', 'asc')
                ->first();

            if ($pembayaranPertama) {
                $startDate = Carbon::parse($pembayaranPertama->tanggal)->startOfMonth();
                $endDate = $startDate->copy()->addMonths($bulanTercover - 1);

                // Cek apakah bulan target tercover oleh pembayaran
                if ($targetDate >= $startDate && $targetDate <= $endDate) {
                    // User sudah bayar untuk bulan ini
                    $userSudahBayar->push([
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'id_anggota' => $user->id_anggota,
                        'no_hp' => $user->no_hp,
                        'tanggal_bayar' => $pembayaranPertama->tanggal->format('d M Y'),
                        'jumlah' => $nominalPerBulan,
                        'tepat_waktu' => true,
                        'status_lunas' => $totalBayarUser >= $target ? 'Lunas' : 'Cicilan',
                        'sisa_bulan' => max(0, $bulanDibutuhkan - $bulanTercover),
                        'progress_percent' => min(100, ($totalBayarUser / $target) * 100)
                    ]);
                } else {
                    // User belum bayar untuk bulan ini
                    $userBelumBayar->push($user);
                }
            } else {
                // User belum pernah bayar
                $userBelumBayar->push($user);
            }
        }

        $namaBulan = Carbon::create($tahun, $bulan, 1)->format('F Y');

        return response()->json([
            'success' => true,
            'data' => [
                'tahun' => $tahun,
                'bulan' => $bulan,
                'nama_bulan' => $namaBulan,
                'jenis_kas' => $jenisKas->nama_jenis_kas,
                'jenis_kas_id' => $jenisKas->id,
                'target_lunas' => $target,
                'nominal_per_bulan' => $nominalPerBulan,
                'durasi_bulan' => $bulanDibutuhkan,
                'belum_bayar' => $userBelumBayar->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'id_anggota' => $user->id_anggota,
                        'no_hp' => $user->no_hp
                    ];
                }),
                'sudah_bayar' => $userSudahBayar,
                'total_belum' => $userBelumBayar->count(),
                'total_sudah' => $userSudahBayar->count(),
                'total_anggota' => $semuaUser->count()
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
                ->whereNotNull('tanggal')
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
    public function sendBulkReminders(Request $request)
    {
        $bulanIni = Carbon::now()->month;
        $tahunIni = Carbon::now()->year;
        $jenisKasId = $request->get('jenis_kas_id');

        $mandatoryJenisKases = JenisKas::where('tipe_iuran', 'wajib')
            ->where('status', 'aktif')
            ->get();

        $belumBayarUsers = collect();

        if ($jenisKasId) {
            // Kirim pengingat untuk jenis kas spesifik
            $jenisKasWajib = $mandatoryJenisKases->where('id', $jenisKasId)->first();

            if ($jenisKasWajib) {
                $userSudahBayar = Kas::where('jenis_kas_id', $jenisKasWajib->id)
                    ->where('tipe', 'pemasukan')
                    ->whereYear('tanggal', $tahunIni)
                    ->whereMonth('tanggal', $bulanIni)
                    ->pluck('user_id');

                $belumBayarUsers = User::where('role', 'user')
                    ->whereNotIn('id', $userSudahBayar)
                    ->get();
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Jenis Kas wajib tidak ditemukan.',
                ]);
            }
        } else {
            // Kirim pengingat ke semua anggota yang belum membayar *seluruh* iuran wajib bulan ini
            $semuaUser = User::where('role', 'user')->get();
            foreach ($semuaUser as $user) {
                $semuaSudahBayar = true;
                Log::info("Memeriksa status pembayaran user: {$user->name} (ID: {$user->id})");
                foreach ($mandatoryJenisKases as $jenisKas) {
                    $sudahBayar = Kas::where('user_id', $user->id)
                        ->where('jenis_kas_id', $jenisKas->id)
                        ->where('tipe', 'pemasukan')
                        ->whereYear('tanggal', $tahunIni)
                        ->whereMonth('tanggal', $bulanIni)
                        ->exists();

                    Log::info("  Jenis Kas: {$jenisKas->nama_jenis_kas} (ID: {$jenisKas->id}), Sudah Bayar: " . ($sudahBayar ? 'Ya' : 'Tidak'));

                    if (!$sudahBayar) {
                        $semuaSudahBayar = false;
                        break; // Jika belum bayar satu saja, anggap belum bayar semua
                    }
                }
                if (!$semuaSudahBayar) {
                    Log::warning("User {$user->name} (ID: {$user->id}) dianggap BELUM membayar semua iuran wajib.");
                    $belumBayarUsers->push($user);
                } else {
                    Log::info("User {$user->name} (ID: {$user->id}) dianggap SUDAH membayar semua iuran wajib.");
                }
            }
            $belumBayarUsers = $belumBayarUsers->unique('id');
        }

        $successCount = 0;
        $failedCount = 0;
        $failedEmails = [];

        foreach ($belumBayarUsers as $user) {
            // Kirim satu email saja ke user yang belum bayar semua iuran wajib
            $firstJenisKas = $mandatoryJenisKases->first(); // Ambil satu jenis kas saja untuk nama di email
            if ($firstJenisKas) {
                try {
                    Mail::to($user->email)->send(new BulkPaymentReminder($user, 'Iuran Wajib'));
                    $successCount++;
                } catch (\Exception $e) {
                    $failedCount++;
                    $failedEmails[] = $user->email;
                    Log::error("Gagal mengirim email ke {$user->email} untuk iuran wajib di bulan {$bulanIni}/{$tahunIni}: " . $e->getMessage());
                }
            }
        }

        $message = $jenisKasId
            ? "Berhasil mengirim {$successCount} email, gagal mengirim {$failedCount} email untuk jenis kas wajib."
            : "Berhasil mengirim {$successCount} email, gagal mengirim {$failedCount} email kepada anggota yang belum membayar seluruh iuran wajib bulan ini.";

        return response()->json([
            'success' => true,
            'message' => $message,
            'failed_emails' => $failedEmails,
            'total_recipients' => $belumBayarUsers->count()
        ]);
    }
    public function exportAllDataToExcel(Request $request)
    {
        // Ambil tahun yang dipilih, default tahun sekarang
        $tahunDipilih = $request->get('tahun', Carbon::now()->year);

        // ========== STATISTIK UTAMA ==========
        $totalPemasukan = Kas::where('tipe', 'pemasukan')->sum('jumlah');
        $totalPengeluaran = Kas::where('tipe', 'pengeluaran')->sum('jumlah');
        $saldo = $totalPemasukan - $totalPengeluaran;
        $totalAnggota = User::where('role', 'user')->count();

        // ========== IURAN SUKARELA BULAN INI ==========
        $bulanIni = Carbon::now();
        $jenisKasSukarela = JenisKas::where('tipe_iuran', 'sukarela')
            ->where('status', 'aktif')
            ->pluck('id');

        $iuranSukarelaBulanIni = collect();
        if ($jenisKasSukarela->isNotEmpty()) {
            $iuranSukarelaBulanIni = Kas::where('tipe', 'pemasukan')
                ->whereIn('jenis_kas_id', $jenisKasSukarela)
                ->whereYear('tanggal', $bulanIni->year)
                ->whereMonth('tanggal', $bulanIni->month)
                ->with(['user', 'jenisKas'])
                ->get();
        }

        // ========== IURAN BULAN INI (YANG BELUM BAYAR) ==========
        $bulanIni = Carbon::now();
        $jenisKasWajib = JenisKas::where('tipe_iuran', 'wajib')->where('status', 'aktif')->get();
        $userBelumBayarBulanIni = collect();
        if ($jenisKasWajib->isNotEmpty()) {
            $userSudahBayarBulanIni = Kas::where('tipe', 'pemasukan')
                ->whereIn('jenis_kas_id', $jenisKasWajib->pluck('id'))
                ->whereYear('tanggal', $bulanIni->year)
                ->whereMonth('tanggal', $bulanIni->month)
                ->with('user')
                ->get()
                ->unique('user_id');

            $semuaUser = User::where('role', 'user')->get();
            $userBelumBayarBulanIni = $semuaUser->filter(function ($user) use ($userSudahBayarBulanIni) {
                return !$userSudahBayarBulanIni->pluck('user_id')->contains($user->id);
            });
        }

        // ========== TRACKING IURAN WAJIB (PROGRESS PELUNASAN) ==========
        $trackingWajib = [];
        foreach ($jenisKasWajib as $jenisKas) {
            $semuaUser = User::where('role', 'user')->get();
            $targetPerUser = $jenisKas->target_lunas;

            $progressDetail = [];
            foreach ($semuaUser as $user) {
                $totalBayarUser = Kas::where('user_id', $user->id)
                    ->where('jenis_kas_id', $jenisKas->id)
                    ->where('tipe', 'pemasukan')
                    ->sum('jumlah');
                $status = $totalBayarUser >= $targetPerUser ? 'lunas' : 'belum_lunas';
                $persentase = $targetPerUser > 0 ? min(100, ($totalBayarUser / $targetPerUser) * 100) : 0;
                $progressDetail[] = [
                    'user' => $user,
                    'total_terbayar' => $totalBayarUser,
                    'status' => $status,
                    'persentase' => round($persentase, 2),
                    'sisa_bayar' => max(0, $targetPerUser - $totalBayarUser),
                    'target_user' => $targetPerUser
                ];
            }
            usort($progressDetail, function ($a, $b) {
                return $b['persentase'] <=> $a['persentase'];
            });
            $trackingWajib[$jenisKas->nama_jenis_kas] = $progressDetail;
        }

        // ========== PERFORMA ANGGOTA (BULAN INI) ==========
        $performaUser = User::where('role', 'user')->get()->map(function ($user) use ($jenisKasWajib) {
            $tepatWaktu = 0;
            $terlambat = 0;
            $bulanIni = Carbon::now();
            foreach ($jenisKasWajib as $jenisKas) {
                $pembayaran = Kas::where('user_id', $user->id)
                    ->where('jenis_kas_id', $jenisKas->id)
                    ->where('tipe', 'pemasukan')
                    ->whereYear('tanggal', $bulanIni->year)
                    ->whereMonth('tanggal', $bulanIni->month)
                    ->get();
                foreach ($pembayaran as $bayar) {
                    if ($bayar->tanggal->day <= 15) {
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
        })->sortByDesc('persentase_tepat_waktu')->values()->toArray();

        // ========== PEMASUKAN & PENGELUARAN PER JENIS KAS ==========
        $pemasukankuanganPerJenis = JenisKas::with(['kas' => function ($query) {
            $query->where('tipe', 'pemasukan');
        }])->get()->map(function ($jenisKas) {
            return [
                'nama' => $jenisKas->nama_jenis_kas,
                'total' => $jenisKas->kas->sum('jumlah')
            ];
        })->toArray();

        $pengeluarankuanganPerJenis = JenisKas::with(['kas' => function ($query) {
            $query->where('tipe', 'pengeluaran');
        }])->get()->map(function ($jenisKas) {
            return [
                'nama' => $jenisKas->nama_jenis_kas,
                'total' => $jenisKas->kas->sum('jumlah'),
            ];
        })->toArray();

        return Excel::download(new AllDashboardDataExport([
            'Statistik Utama' => [
                ['Total Pemasukan', $totalPemasukan],
                ['Total Pengeluaran', $totalPengeluaran],
                ['Saldo Akhir', $saldo],
                ['Total Anggota', $totalAnggota],
            ],
            'Iuran Sukarela Bulan Ini' => $iuranSukarelaBulanIni->map(function ($item) {
                return [
                    'Nama Anggota' => $item->user->name ?? 'Anonim',
                    'Email Anggota' => $item->user->email ?? '-',
                    'Jenis Kas' => $item->jenisKas->nama_jenis_kas ?? '-',
                    'Jumlah' => $item->jumlah,
                    'Tanggal' => $item->tanggal->format('d M Y'),
                    'Keterangan' => $item->keterangan,
                ];
            })->toArray(),
            'Belum Bayar Iuran Wajib Bulan Ini' => $userBelumBayarBulanIni->map(function ($user) {
                return [
                    'ID Anggota' => $user->id_anggota,
                    'Nama Anggota' => $user->name,
                    'Email Anggota' => $user->email,
                    'Nomor HP' => $user->no_hp,
                ];
            })->toArray(),
            'Progress Iuran Wajib' => collect($trackingWajib)->flatMap(function ($progressDetails, $namaKas) {
                return collect($progressDetails)->map(function ($detail) use ($namaKas) {
                    return [
                        'Jenis Kas' => $namaKas,
                        'ID Anggota' => $detail['user']->id_anggota,
                        'Nama Anggota' => $detail['user']->name,
                        'Total Terbayar' => $detail['total_terbayar'],
                        'Sisa Bayar' => $detail['sisa_bayar'],
                        'Target User' => $detail['target_user'],
                        'Status' => $detail['status'],
                        'Persentase' => $detail['persentase'] . '%',
                    ];
                });
            })->toArray(),
            'Performa Anggota Bulan Ini' => collect($performaUser)->map(function ($item) {
                return [
                    'ID Anggota' => $item['user']->id_anggota,
                    'Nama Anggota' => $item['user']->name,
                    'Tepat Waktu' => $item['tepat_waktu'],
                    'Terlambat' => $item['terlambat'],
                    'Persentase Tepat Waktu' => round($item['persentase_tepat_waktu']) . '%',
                ];
            })->toArray(),
            'Pemasukan per Jenis Kas' => $pemasukankuanganPerJenis,
            'Pengeluaran per Jenis Kas' => $pengeluarankuanganPerJenis,
        ]), 'dashboard_data.xlsx');
    }
}
