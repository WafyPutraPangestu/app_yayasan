<?php

namespace App\Http\Controllers;

use App\Models\JenisKas;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class JenisKasController extends Controller
{
    /**
     * Menampilkan daftar semua jenis kas dengan paginasi.
     */
    public function index()
    {
        // Mengurutkan berdasarkan data terbaru dan menggunakan paginasi
        $jenisKas = JenisKas::latest()->paginate(10);
        return view('admin.jenis-kas.index', compact('jenisKas'));
    }

    /**
     * Menampilkan form untuk membuat jenis kas baru.
     */
    public function create()
    {
        return view('admin.jenis-kas.create');
    }

    /**
     * Menyimpan jenis kas baru ke dalam database.
     */
    public function store(Request $request)
    {
        // Validasi data yang masuk dari form, termasuk 'target_lunas'
        $validatedData =  $request->validate([
            'kode_jenis_kas' => 'required|string|max:20|unique:jenis_kas,kode_jenis_kas',
            'nama_jenis_kas' => 'required|string|max:100|unique:jenis_kas,nama_jenis_kas',
            'default_tipe' => ['required', Rule::in(['pemasukan', 'pengeluaran'])],
            'tipe_iuran' => ['required', Rule::in(['wajib', 'sukarela'])],
            'nominal_wajib' => 'nullable|numeric|min:0',
            'target_lunas' => 'nullable|numeric|min:0',
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
        ], [
            // Pesan error kustom dalam Bahasa Indonesia
            'nama_jenis_kas.required' => 'Nama jenis kas wajib diisi.',
            'nama_jenis_kas.unique' => 'Nama jenis kas ini sudah terdaftar.',
            'tipe_iuran.required' => 'Tipe iuran wajib dipilih.',
            'nominal_wajib.required_if' => 'Nominal wajib harus diisi jika tipe iuran adalah "Wajib".',
            'default_tipe.required' => 'Tipe default (pemasukan/pengeluaran) wajib dipilih.',
            'target_lunas.gte' => 'Target lunas tidak boleh lebih kecil dari nominal wajib per periode.',
        ]);

        // Logika tambahan: Jika tipe iuran adalah 'sukarela', pastikan nominal_wajib dan target_lunas bernilai null.
        if ($validatedData['tipe_iuran'] === 'sukarela') {
            $validatedData['nominal_wajib'] = null;
            $validatedData['target_lunas'] = null;
        }

        // Membuat record baru hanya dengan data yang sudah tervalidasi
        JenisKas::create($validatedData);

        return redirect()->route('jenis-kas.index')
            ->with('success', 'Jenis kas baru berhasil ditambahkan.');
    }

    /**
     * Menampilkan detail satu jenis kas.
     */
    public function show(JenisKas $jenisKa)
    {
        return view('admin.jenis-kas.show', compact('jenisKa'));
    }

    /**
     * Menampilkan form untuk mengedit jenis kas.
     * Menggunakan Route Model Binding ($jenisKas).
     */
    public function edit(JenisKas $jenisKa)
    {
        return view('admin.jenis-kas.edit', compact('jenisKa'));
    }

    /**
     * Memperbarui data jenis kas di dalam database.
     */
    public function update(Request $request, JenisKas $jenisKa)
    {
        // Validasi data untuk proses update, termasuk 'target_lunas'
        $validatedData = $request->validate([
            'kode_jenis_kas' => 'required|string|max:20|unique:jenis_kas,kode_jenis_kas,' . $jenisKa->id,
            'nama_jenis_kas' => ['required', 'string', 'max:100', Rule::unique('jenis_kas')->ignore($jenisKa->id)],
            'tipe_iuran' => ['required', Rule::in(['wajib', 'sukarela'])],
            'nominal_wajib' => 'required_if:tipe_iuran,wajib|nullable|numeric|min:0',
            // FIX: Menambahkan validasi untuk target_lunas
            'target_lunas' => 'nullable|numeric|min:0|gte:nominal_wajib',
            'default_tipe' => ['required', Rule::in(['pemasukan', 'pengeluaran'])],
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
        ], [
            'nama_jenis_kas.required' => 'Nama jenis kas wajib diisi.',
            'nama_jenis_kas.unique' => 'Nama jenis kas ini sudah terdaftar.',
            'tipe_iuran.required' => 'Tipe iuran wajib dipilih.',
            'nominal_wajib.required_if' => 'Nominal wajib harus diisi jika tipe iuran adalah "Wajib".',
            'target_lunas.gte' => 'Target lunas tidak boleh lebih kecil dari nominal wajib per periode.',
        ]);

        // Logika tambahan: Jika tipe iuran diubah menjadi 'sukarela', null-kan nominal_wajib dan target_lunas.
        if ($validatedData['tipe_iuran'] === 'sukarela') {
            $validatedData['nominal_wajib'] = null;
            $validatedData['target_lunas'] = null;
        }

        // Memperbarui record dengan data yang sudah tervalidasi
        $jenisKa->update($validatedData);

        return redirect()->route('jenis-kas.index')
            ->with('success', 'Jenis kas berhasil diperbarui.');
    }

    /**
     * Menghapus jenis kas dari database dengan pengecekan.
     */
    public function destroy(JenisKas $jenisKa)
    {
        // Pengecekan keamanan: Apakah jenis kas ini sudah pernah digunakan?
        // Kita asumsikan ada relasi 'kas' di model JenisKas: public function kas() { return $this->hasMany(Kas::class); }
        if ($jenisKa->kas()->exists()) {
            return redirect()->route('jenis-kas.index')
                ->with('error', 'Gagal! Jenis kas ini tidak dapat dihapus karena sudah digunakan dalam transaksi.');
        }

        // Jika aman, baru dihapus
        $jenisKa->delete();

        return redirect()->route('jenis-kas.index')
            ->with('success', 'Jenis kas berhasil dihapus.');
    }
}
