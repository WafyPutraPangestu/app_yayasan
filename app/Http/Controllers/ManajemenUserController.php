<?php

namespace App\Http\Controllers;

use App\Models\Kas;
use App\Models\JenisKas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class ManajemenUserController extends Controller
{
    /**
     * Menampilkan dashboard untuk user.
     * Berisi informasi total keuangan dari semua jenis kas secara dinamis.
     */
    public function dashboard()
    {
        // Ambil data kas per tahun (versi SQLite)
        $years = Kas::selectRaw("strftime('%Y', tanggal) as year")
            ->groupBy('year')
            ->orderBy('year', 'desc')
            ->pluck('year');

        // Ambil tahun saat ini atau tahun terbaru yang ada data
        $currentYear = $years->first() ?? date('Y');

        // Ambil data kas per bulan untuk tahun terpilih
        $monthlyData = Kas::selectRaw("strftime('%m', tanggal) as month, SUM(jumlah) as total")
            ->whereRaw("strftime('%Y', tanggal) = ?", [$currentYear])
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Format data untuk chart - pastikan semua bulan ada
        $monthlyLabels = [];
        $monthlyTotals = [];

        for ($i = 1; $i <= 12; $i++) {
            $monthNum = str_pad($i, 2, '0', STR_PAD_LEFT);
            $monthlyLabels[] = date('F', mktime(0, 0, 0, $i, 1));

            // Cari data untuk bulan ini
            $monthData = $monthlyData->first(function ($item) use ($monthNum) {
                return $item->month === $monthNum;
            });

            $monthlyTotals[] = $monthData ? (float)$monthData->total : 0;
        }

        // Data per jenis kas
        $jenisKasData = JenisKas::with(['kas' => function ($query) use ($currentYear) {
            $query->whereRaw("strftime('%Y', tanggal) = ?", [$currentYear]);
        }])->get()->map(function ($jenis) {
            return [
                'nama' => $jenis->nama_jenis_kas,
                'total' => $jenis->kas->sum('jumlah')
            ];
        });

        // Hitung total pemasukan
        $totalPemasukan = array_sum($monthlyTotals);

        // Ambil nilai bulan berjalan
        $currentMonthIndex = date('n') - 1; // karena array dimulai dari 0
        $currentMonthTotal = $monthlyTotals[$currentMonthIndex] ?? 0;

        return view('user.dashboard', [
            'years' => $years,
            'currentYear' => $currentYear,
            'monthlyLabels' => $monthlyLabels,
            'monthlyTotals' => $monthlyTotals,
            'jenisKasData' => $jenisKasData,
            'totalPemasukan' => $totalPemasukan,
            'currentMonthTotal' => $currentMonthTotal // variabel baru untuk nilai bulan ini
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
     * Menampilkan riwayat pembayaran iuran user selama 4 tahun terakhir.
     */
    public function riwayat()
    {
        // Mendapatkan user yang sedang login
        $user = Auth::user();

        // Mengambil ID untuk jenis kas 'Iuran Rutin'
        $idIuran = JenisKas::all()->value('id');

        // Ambil semua transaksi iuran user selama 4 tahun terakhir
        $pembayaran = Kas::where('user_id', $user->id)
            ->where('jenis_kas_id', $idIuran)
            ->where('tanggal', '>=', now()->subYears(4))
            ->get()
            ->keyBy(function ($item) {
                // Buat kunci berdasarkan tahun dan bulan (e.g., "2025-07")
                return $item->tanggal->format('Y-m');
            });

        // Buat rentang periode 48 bulan dari sekarang ke belakang
        $startPeriod = now()->subYears(4)->startOfMonth();
        $endPeriod = now()->endOfMonth();
        $period = CarbonPeriod::create($startPeriod, '1 month', $endPeriod);

        $riwayatPerTahun = [];
        // Loop melalui setiap bulan dalam rentang periode
        foreach ($period as $date) {
            $tahun = $date->format('Y');
            $bulan = $date->format('M'); // Jan, Feb, Mar
            $key = $date->format('Y-m'); // 2025-07

            // Cek apakah ada pembayaran di bulan ini
            $status = isset($pembayaran[$key]) ? 'Lunas' : 'Belum Bayar';

            // Kelompokkan berdasarkan tahun
            $riwayatPerTahun[$tahun][$bulan] = $status;
        }

        // Urutkan tahun dari yang terbaru ke terlama
        krsort($riwayatPerTahun);

        return view('user.riwayat', compact('riwayatPerTahun'));
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
}
