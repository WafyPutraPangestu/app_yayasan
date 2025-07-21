<?php

namespace App\Http\Controllers;

use App\Models\Kas;
use App\Models\JenisKas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ManajemenUserController extends Controller
{
    /**
     * Menampilkan dashboard untuk user.
     * Berisi informasi total keuangan dari semua jenis kas secara dinamis dan tracking iuran wajib.
     */
    public function dashboard(Request $request)
    {
        // Ambil tahun dari parameter atau gunakan tahun saat ini
        $selectedYear = $request->get('year', date('Y'));

        // Ambil data kas per tahun (versi SQLite)
        $years = Kas::selectRaw("YEAR(tanggal) as year")
            ->groupBy('year')
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->filter(); // Hilangkan nilai null

        // Pastikan ada data tahun
        if ($years->isEmpty()) {
            $years = collect([date('Y')]);
        }

        // Pastikan selected year ada dalam data
        $currentYear = $years->contains($selectedYear) ? $selectedYear : $years->first();

        // Ambil data kas per bulan untuk tahun terpilih - perbaikan query
        $monthlyData = Kas::selectRaw("MONTH(tanggal) as month, SUM(jumlah) as total")
            ->whereRaw("YEAR(tanggal) = ?", [$currentYear])
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->keyBy('month'); // Gunakan keyBy untuk akses yang lebih mudah

        // Format data untuk chart - pastikan semua bulan ada
        $monthlyLabels = [];
        $monthlyTotals = [];

        for ($i = 1; $i <= 12; $i++) {
            $monthlyLabels[] = date('F', mktime(0, 0, 0, $i, 1));

            // Ambil data bulan ini atau 0 jika tidak ada
            $monthlyTotals[] = isset($monthlyData[$i]) ? (float)$monthlyData[$i]->total : 0;
        }

        // Data per jenis kas - perbaikan query
        $jenisKasData = JenisKas::with(['kas' => function ($query) use ($currentYear) {
            $query->whereRaw("YEAR( tanggal) = ?", [$currentYear]);
        }])->get()->map(function ($jenis) {
            return [
                'nama' => $jenis->nama_jenis_kas,
                'total' => $jenis->kas->sum('jumlah')
            ];
        })->filter(function ($item) {
            return $item['total'] > 0; // Hanya tampilkan yang ada datanya
        });

        // Hitung total pemasukan dari array yang sudah disiapkan
        $totalPemasukan = array_sum($monthlyTotals);

        // Ambil nilai bulan berjalan (bulan saat ini)
        $currentMonth = (int)date('n'); // 1-12
        $currentMonthTotal = $monthlyTotals[$currentMonth - 1] ?? 0; // array dimulai dari 0

        // Debug information - bisa dihapus setelah testing
        Log::info('Dashboard Debug:', [
            'selectedYear' => $selectedYear,
            'currentYear' => $currentYear,
            'currentMonth' => $currentMonth,
            'monthlyData' => $monthlyData->toArray(),
            'totalPemasukan' => $totalPemasukan,
            'currentMonthTotal' => $currentMonthTotal,
        ]);

        return view('user.dashboard', [
            'years' => $years,
            'currentYear' => $currentYear,
            'monthlyLabels' => $monthlyLabels,
            'monthlyTotals' => $monthlyTotals,
            'jenisKasData' => $jenisKasData,
            'totalPemasukan' => $totalPemasukan,
            'currentMonthTotal' => $currentMonthTotal,
        ]);
    }

    private function prepareChartData()
    {
        // Get data for the last 12 months
        $months = [];
        $currentMonth = now()->month;
        $currentYear = now()->year;

        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = $date->format('M Y');
        }

        // Get all kas types
        $jenisKasList = JenisKas::all();

        $data = [];
        foreach ($jenisKasList as $jenisKas) {
            $monthlyData = [];

            for ($i = 11; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $total = Kas::where('jenis_kas_id', $jenisKas->id)
                    ->whereMonth('tanggal', $date->month)
                    ->whereYear('tanggal', $date->year)
                    ->sum('jumlah');

                $monthlyData[] = $total;
            }

            $data[$jenisKas->nama_jenis_kas] = $monthlyData;
        }

        return [
            'months' => $months,
            'data' => $data
        ];
    }

    /**
     * Menampilkan riwayat pembayaran iuran wajib per bulan untuk user.
     */
    public function riwayat()
    {
        $user = Auth::user();
        $jenisKasWajib = JenisKas::where('tipe_iuran', 'wajib')->where('status', 'aktif')->get();
        $riwayatIuranWajibPerBulan = [];

        foreach ($jenisKasWajib as $jenisKas) {
            $target = $jenisKas->target_lunas;
            $nominalPerBulan = $jenisKas->nominal_wajib ?? 10000;
            $bulanDibutuhkan = ceil($target / $nominalPerBulan);
            $pembayaranPertama = Kas::where('user_id', $user->id)
                ->where('jenis_kas_id', $jenisKas->id)
                ->where('tipe', 'pemasukan')
                ->orderBy('tanggal', 'asc')
                ->first();

            $startDate = null;
            if ($pembayaranPertama) {
                $startDate = Carbon::parse($pembayaranPertama->tanggal)->startOfMonth();
            }

            $totalBayarUser = Kas::where('user_id', $user->id)
                ->where('jenis_kas_id', $jenisKas->id)
                ->where('tipe', 'pemasukan')
                ->sum('jumlah');
            $bulanTercover = floor($totalBayarUser / $nominalPerBulan);

            $trackingBulanan = [];
            if ($startDate) {
                for ($i = 0; $i < $bulanDibutuhkan; $i++) {
                    $currentMonth = $startDate->copy()->addMonths($i);
                    $tahun = $currentMonth->year;
                    $bulan = $currentMonth->month;
                    $status = $i < $bulanTercover ? 'Lunas' : 'Belum Bayar';
                    $trackingBulanan[$tahun][$bulan] = $status;
                }
            } else {
                // Jika belum pernah bayar, tandai semua bulan sebagai belum bayar
                $currentDate = Carbon::now()->startOfMonth();
                for ($i = 0; $i < $bulanDibutuhkan; $i++) {
                    $currentMonth = $currentDate->copy()->addMonths($i);
                    $tahun = $currentMonth->year;
                    $bulan = $currentMonth->month;
                    $trackingBulanan[$tahun][$bulan] = 'Belum Bayar';
                }
            }

            $riwayatIuranWajibPerBulan[$jenisKas->nama_jenis_kas] = $trackingBulanan;
        }

        return view('user.riwayat', compact('riwayatIuranWajibPerBulan'));
    }

    /**
     * Mendapatkan data statistik keuangan untuk chart - versi dinamis
     */
    public function getFinanceStats()
    {
        // Mengambil semua jenis kas
        $jenisKasList = JenisKas::all();

        // Data untuk 12 bulan terakhir
        $months = [];
        $statsData = [];

        // Inisialisasi array untuk setiap jenis kas
        foreach ($jenisKasList as $jenisKas) {
            $statsData[$jenisKas->nama_jenis_kas] = [];
        }

        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = $date->format('M');

            // Hitung total untuk setiap jenis kas di bulan ini
            foreach ($jenisKasList as $jenisKas) {
                $total = Kas::where('jenis_kas_id', $jenisKas->id)
                    ->whereYear('tanggal', $date->year)
                    ->whereMonth('tanggal', $date->month)
                    ->sum('jumlah');

                $statsData[$jenisKas->nama_jenis_kas][] = $total;
            }
        }

        return response()->json([
            'months' => $months,
            'data' => $statsData
        ]);
    }

    /**
     * Mendapatkan ringkasan kas per jenis untuk dashboard cards
     */
    public function getKasSummary()
    {
        $summary = JenisKas::leftJoin('kas', 'jenis_kas.id', '=', 'kas.jenis_kas_id')
            ->selectRaw('
                jenis_kas.id,
                jenis_kas.nama_jenis_kas,
                COALESCE(SUM(kas.jumlah), 0) as total_jumlah,
                COUNT(kas.id) as total_transaksi
            ')
            ->groupBy('jenis_kas.id', 'jenis_kas.nama_jenis_kas')
            ->get();

        return response()->json($summary);
    }

    /**
     * Mendapatkan data kas per bulan untuk jenis kas tertentu
     */
    public function getKasPerBulan($jenisKasId, $tahun = null)
    {
        $tahun = $tahun ?? now()->year;

        $data = [];
        for ($bulan = 1; $bulan <= 12; $bulan++) {
            $total = Kas::where('jenis_kas_id', $jenisKasId)
                ->whereYear('tanggal', $tahun)
                ->whereMonth('tanggal', $bulan)
                ->sum('jumlah');

            $data[] = [
                'bulan' => Carbon::create($tahun, $bulan, 1)->format('M'),
                'total' => $total
            ];
        }

        return response()->json($data);
    }

    public function iuranWajib()
    {
        return redirect()->route('user.riwayat');
    }
}
