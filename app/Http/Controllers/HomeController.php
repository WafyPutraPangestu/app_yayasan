<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function index()
    {
        // Ambil data untuk chart usia
        $ageData = $this->getAgeDistribution();

        // Ambil data untuk chart status
        $statusData = $this->getStatusDistribution();

        // Statistik untuk cards di hero section
        $stats = [
            'tahun_berdiri' => 47,
            'anak_asuh' => User::where('status', 'Aktif')->count(),
            'program' => 15,
            'luas_masjid' => '1.400 m²',
            'jamaah_aktif' => User::where('status', 'Aktif')
                ->where('role', 'user')
                ->count(),
            // Asumsi jamaah aktif adalah yang statusnya Aktif
            'santri_binaan' => User::where('status', 'Aktif')->count(),
            'program_pendidikan' => '15',
            // Data untuk chart
            'total_users' => User::count(),
            'active_users' => User::where('status', 'Aktif')->count(),
            'pending_users' => User::where('status', 'Pending')->count(),
            'wafat_users' => User::where('status', 'Wafat')->count(),
            'nonaktif_users' => User::where('status', 'Nonaktif')->count(),
            'mengundurkan_diri_users' => User::where('status', 'Mengundurkan diri')->count(),
        ];

        return view('home', compact('ageData', 'statusData', 'stats'));
    }

    private function getAgeDistribution()
    {
        // Ambil semua user yang memiliki tanggal lahir
        $users = User::whereNotNull('tanggal_lahir')->get();

        $ageGroups = [
            '0-20' => 0,
            '21-40' => 0,
            '41-60' => 0,
            '61+' => 0
        ];

        foreach ($users as $user) {
            $age = Carbon::parse($user->tanggal_lahir)->age;

            if ($age <= 20) {
                $ageGroups['0-20']++;
            } elseif ($age <= 40) {
                $ageGroups['21-40']++;
            } elseif ($age <= 60) {
                $ageGroups['41-60']++;
            } else {
                $ageGroups['61+']++;
            }
        }

        // Format data untuk Chart.js
        return [
            'labels' => array_keys($ageGroups),
            'data' => array_values($ageGroups),
            'total' => array_sum($ageGroups)
        ];
    }

    private function getStatusDistribution()
    {
        $statusCounts = User::select('status')
            ->groupBy('status')
            ->selectRaw('count(*) as count, status')
            ->get()
            ->pluck('count', 'status')
            ->toArray();

        // Pastikan semua status ada, meski nilainya 0
        $allStatuses = ['Pending', 'Aktif', 'Nonaktif', 'Wafat', 'Mengundurkan diri'];

        foreach ($allStatuses as $status) {
            if (!isset($statusCounts[$status])) {
                $statusCounts[$status] = 0;
            }
        }

        return [
            'labels' => array_keys($statusCounts),
            'data' => array_values($statusCounts),
            'total' => array_sum($statusCounts)
        ];
    }

    // Method untuk mendapatkan data chart dalam format JSON (untuk AJAX)
    public function getChartData(Request $request)
    {
        $type = $request->get('type', 'age');

        if ($type === 'age') {
            return response()->json($this->getAgeDistribution());
        } elseif ($type === 'status') {
            return response()->json($this->getStatusDistribution());
        }

        return response()->json(['error' => 'Invalid chart type'], 400);
    }

    // Method untuk mendapatkan detail data berdasarkan filter
    public function getDetailData(Request $request)
    {
        $filter = $request->get('filter');
        $value = $request->get('value');

        $query = User::query();

        if ($filter === 'age') {
            $query->whereNotNull('tanggal_lahir');

            switch ($value) {
                case '0-20':
                    $query->whereRaw('TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) <= 20');
                    break;
                case '21-40':
                    $query->whereRaw('TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) BETWEEN 21 AND 40');
                    break;
                case '41-60':
                    $query->whereRaw('TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) BETWEEN 41 AND 60');
                    break;
                case '61+':
                    $query->whereRaw('TIMESTAMPDIFF(YEAR, tanggal_lahir, CURDATE()) > 60');
                    break;
            }
        } elseif ($filter === 'status') {
            $query->where('status', $value);
        }

        $users = $query->select('id_anggota', 'name', 'email', 'status', 'tanggal_lahir')
            ->paginate(10);

        return response()->json($users);
    }
}
