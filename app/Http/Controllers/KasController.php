<?php

namespace App\Http\Controllers;

use App\Models\Kas;
use App\Models\User;
use App\Models\JenisKas;
use App\Models\WajibKasProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class KasController extends Controller
{
    // ===================================================================================
    // BAGIAN API & LAPORAN
    // ===================================================================================

    /**
     * Menampilkan halaman utama manajemen kas.
     */
    public function index()
    {
        return view('admin.kas.index');
    }

    /**
     * Menyediakan data transaksi untuk ditampilkan di frontend via API.
     */
    public function data(Request $request)
    {
        $query = Kas::with(['user', 'jenisKas']);

        if ($request->filled('tipe') && in_array($request->tipe, ['pemasukan', 'pengeluaran'])) {
            $query->where('tipe', $request->tipe);
        }
        if ($request->filled('tahun')) {
            $query->whereYear('tanggal', $request->tahun);
        }
        if ($request->filled('bulan')) {
            $query->whereMonth('tanggal', $request->bulan);
        }

        $statsQuery = clone $query;
        $stats = $statsQuery->selectRaw('
            SUM(CASE WHEN tipe = "pemasukan" THEN jumlah ELSE 0 END) as total_pemasukan,
            SUM(CASE WHEN tipe = "pengeluaran" THEN jumlah ELSE 0 END) as total_pengeluaran,
            SUM(CASE WHEN tipe = "pemasukan" THEN jumlah ELSE -jumlah END) as saldo
        ')->first();

        $transaksi = $query->latest()->paginate(10);

        return response()->json([
            'data' => $transaksi->items(),
            'stats' => $stats,
            'totalItems' => $transaksi->total(),
            'currentPage' => $transaksi->currentPage(),
            'perPage' => $transaksi->perPage()
        ]);
    }

    /**
     * Menyediakan data laporan bulanan via API.
     */
    public function monthlyReport(Request $request)
    {
        $year = $request->filled('tahun') ? $request->tahun : date('Y');
        $report = Kas::selectRaw('
            MONTH(tanggal) as bulan,
            SUM(CASE WHEN tipe = "pemasukan" THEN jumlah ELSE 0 END) as pemasukan,
            SUM(CASE WHEN tipe = "pengeluaran" THEN jumlah ELSE 0 END) as pengeluaran
        ')
            ->whereYear('tanggal', $year)
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();
        return response()->json($report);
    }

    /**
     * Menyediakan daftar tahun yang memiliki transaksi via API.
     */
    public function getAvailableYears()
    {
        $years = Kas::selectRaw('YEAR(tanggal) as year')
            ->distinct()
            ->orderBy('year', 'DESC')
            ->pluck('year');
        return response()->json($years);
    }

    /**
     * Menyediakan data user (anggota) untuk form select dinamis via API.
     */ public function searchJenisKas(Request $request)
    {
        $search = $request->input('q');
        $tipe = $request->input('tipe');

        if (empty($search)) {
            return response()->json([]);
        }

        $query = JenisKas::where('status', 'aktif')
            ->where('nama_jenis_kas', 'LIKE', "%{$search}%");

        if ($tipe && in_array($tipe, ['pemasukan', 'pengeluaran'])) {
            $query->where('default_tipe', $tipe);
        }

        $jenisKas = $query->limit(10)->get([
            'id',
            'nama_jenis_kas',
            'tipe_iuran',
            'nominal_wajib',
            'target_lunas',
            'default_tipe'
        ]);

        $formattedJenisKas = $jenisKas->map(function ($jk) {
            return [
                'id' => $jk->id,
                'text' => $jk->nama_jenis_kas,
                'tipe_iuran' => $jk->tipe_iuran,
                'nominal_wajib' => $jk->nominal_wajib,
                'target_lunas' => $jk->target_lunas,
                'default_tipe' => $jk->default_tipe
            ];
        });

        return response()->json($formattedJenisKas);
    }
    public function getJenisKasDetail($id)
    {
        $jenisKas = JenisKas::find($id);

        if (!$jenisKas) {
            return response()->json(['error' => 'Jenis kas tidak ditemukan'], 404);
        }

        return response()->json($jenisKas);
    }
    public function searchUsers(Request $request)
    {
        $search = $request->input('q');
        if (empty($search)) {
            return response()->json([]);
        }
        $users = User::where('role', 'user')
            ->where(function ($query) use ($search) {
                $query->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('id_anggota', 'LIKE', "%{$search}%");
            })
            ->limit(10)
            ->get(['id', 'name', 'id_anggota']);
        $formattedUsers = $users->map(function ($user) {
            return ['id' => $user->id, 'text' => "{$user->name} ({$user->id_anggota})"];
        });
        return response()->json($formattedUsers);
    }

    // ===================================================================================
    // BAGIAN CRUD UTAMA
    // ===================================================================================

    /**
     * Menampilkan form untuk membuat transaksi kas baru.
     */
    public function create()
    {
        $jenisKas = JenisKas::where('status', 'aktif')->get();
        return view('admin.kas.create', compact('jenisKas'));
    }

    /**
     * Menyimpan transaksi kas baru ke dalam database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'tipe' => ['required', Rule::in(['pemasukan', 'pengeluaran'])],
            'tanggal' => 'required|date',
            'jumlah' => 'required|numeric|min:1',
        ]);

        DB::beginTransaction();
        try {
            if ($request->tipe === 'pemasukan') {
                $this->handlePemasukan($request);
            } else {
                $this->handlePengeluaran($request);
            }
            DB::commit();
            return redirect()->route('kas.index')->with('success', 'Transaksi kas berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Menampilkan form untuk mengedit transaksi kas.
     */
    public function edit(Kas $ka)
    {
        $jenisKas = JenisKas::where('status', 'aktif')->get();
        return view('admin.kas.edit', compact('ka', 'jenisKas'));
    }

    /**
     * Memperbarui data transaksi kas di dalam database.
     */
    public function update(Request $request, Kas $ka)
    {
        $request->validate([
            'jumlah' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string|max:255',
            'tanggal' => 'required|date',
        ]);

        $isWajib = $ka->jenisKas && $ka->jenisKas->tipe_iuran === 'wajib';
        $oldUserId = $ka->user_id;
        $oldJenisKasId = $ka->jenis_kas_id;

        DB::beginTransaction();
        try {
            $saldo = Kas::sum(DB::raw('CASE WHEN tipe = "pemasukan" THEN jumlah ELSE -jumlah END'));
            $perubahanJumlah = $request->jumlah - $ka->jumlah;
            if ($ka->tipe === 'pengeluaran' && $saldo - $perubahanJumlah < 0) {
                throw new \Exception('Saldo tidak mencukupi untuk melakukan perubahan ini.');
            }
            if ($ka->tipe === 'pemasukan' && $saldo - $perubahanJumlah < 0) {
                throw new \Exception('Mengurangi jumlah pemasukan ini akan membuat saldo menjadi negatif.');
            }

            $ka->update($request->only(['jumlah', 'keterangan', 'tanggal']));

            if ($isWajib) {
                $this->syncWajibKasProgress($oldUserId, $oldJenisKasId);
            }

            DB::commit();
            return redirect()->route('kas.index')->with('success', 'Transaksi kas berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Menghapus transaksi kas dari database.
     */
    public function destroy(Kas $ka)
    {
        $saldo = Kas::sum(DB::raw('CASE WHEN tipe = "pemasukan" THEN jumlah ELSE -jumlah END'));
        if ($ka->tipe === 'pemasukan' && ($saldo - $ka->jumlah < 0)) {
            return redirect()->route('kas.index')->with('error', 'Gagal! Menghapus pemasukan ini akan membuat saldo menjadi negatif.');
        }

        $isWajib = $ka->jenisKas && $ka->jenisKas->tipe_iuran === 'wajib';
        $userId = $ka->user_id;
        $jenisKasId = $ka->jenis_kas_id;

        DB::beginTransaction();
        try {
            $ka->delete();
            if ($isWajib) {
                $this->syncWajibKasProgress($userId, $jenisKasId);
            }
            DB::commit();
            return redirect()->route('kas.index')->with('success', 'Transaksi kas berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    // ===================================================================================
    // FUNGSI HELPER PRIBADI
    // ===================================================================================

    private function handlePemasukan(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'jenis_kas_id' => 'required|exists:jenis_kas,id',
        ]);
        $jenisKas = JenisKas::find($request->jenis_kas_id);
        if ($jenisKas->tipe_iuran === 'wajib') {
            $this->handleIuranWajib($request, $jenisKas);
        } else {
            $this->handleIuranSukarela($request, $jenisKas);
        }
    }

    private function handleIuranWajib(Request $request, JenisKas $jenisKas)
    {
        $request->validate([
            'bulan_iuran' => 'required|numeric|between:1,12',
            'tahun_iuran' => 'required|numeric|digits:4',
        ]);

        $wajibKasProgress = WajibKasProgress::where('user_id', $request->user_id)
            ->where('jenis_kas_id', $jenisKas->id)
            ->first();

        $totalTerbayarSaatIni = $wajibKasProgress ? $wajibKasProgress->total_terbayar : 0;
        $jumlahBayarBaru = $request->jumlah;
        $totalBayarSetelahIni = $totalTerbayarSaatIni + $jumlahBayarBaru;
        $sisaBayar = $jenisKas->target_lunas - $totalTerbayarSaatIni;

        if ($jenisKas->target_lunas && $totalBayarSetelahIni > $jenisKas->target_lunas) {
            throw new \Exception('Pembayaran melebihi target lunas untuk iuran wajib ' . $jenisKas->nama_jenis_kas . '. Sisa yang perlu dibayar adalah Rp ' . number_format($sisaBayar, 0, ',', '.') . '.');
        }

        Kas::create([
            'tipe' => 'pemasukan',
            'user_id' => $request->user_id,
            'jenis_kas_id' => $jenisKas->id,
            'jumlah' => $request->jumlah,
            'keterangan' => $request->keterangan ?? "Cicilan Iuran: {$jenisKas->nama_jenis_kas}",
            'tanggal' => $request->tanggal,
            'bulan_iuran' => $request->bulan_iuran,
            'tahun_iuran' => $request->tahun_iuran,
        ]);

        $this->syncWajibKasProgress($request->user_id, $jenisKas->id);
    }

    private function handleIuranSukarela(Request $request, JenisKas $jenisKas)
    {
        $request->validate(['keterangan' => 'required|string|max:255']);
        Kas::create([
            'tipe' => 'pemasukan',
            'user_id' => $request->user_id,
            'jenis_kas_id' => $jenisKas->id,
            'jumlah' => $request->jumlah,
            'keterangan' => $request->keterangan,
            'tanggal' => $request->tanggal,
        ]);
    }

    private function handlePengeluaran(Request $request)
    {
        $request->validate([
            'keterangan' => 'required|string|max:255',
            'jenis_kas_id' => 'required|exists:jenis_kas,id',
        ]);
        $saldo = Kas::sum(DB::raw('CASE WHEN tipe = "pemasukan" THEN jumlah ELSE -jumlah END'));
        if ($request->jumlah > $saldo) {
            throw new \Exception('Saldo tidak mencukupi untuk melakukan pengeluaran ini.');
        }
        Kas::create([
            'tipe' => 'pengeluaran',
            'user_id' => Auth::id(),
            'jenis_kas_id' => $request->jenis_kas_id,
            'jumlah' => $request->jumlah,
            'keterangan' => $request->keterangan,
            'tanggal' => $request->tanggal,
        ]);
    }

    private function syncWajibKasProgress($userId, $jenisKasId)
    {
        $jenisKas = JenisKas::find($jenisKasId);
        if (!$jenisKas || $jenisKas->tipe_iuran !== 'wajib') {
            return;
        }
        $progress = WajibKasProgress::firstOrNew([
            'user_id' => $userId,
            'jenis_kas_id' => $jenisKasId,
        ]);
        $totalTerbayar = Kas::where('user_id', $userId)
            ->where('jenis_kas_id', $jenisKasId)
            ->sum('jumlah');
        $progress->total_terbayar = $totalTerbayar;
        if ($jenisKas->target_lunas && $totalTerbayar >= $jenisKas->target_lunas) {
            $progress->status = 'lunas';
            $progress->tanggal_lunas = $progress->tanggal_lunas ?? now();
        } else {
            $progress->status = 'belum lunas';
            $progress->tanggal_lunas = null;
        }
        $progress->save();
    }
}
