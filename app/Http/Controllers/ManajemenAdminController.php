<?php

namespace App\Http\Controllers;

use App\Exports\AnggotaExport;
use App\Models\User;
use App\Notifications\SendInvitationNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Maatwebsite\Excel\Facades\Excel;

class ManajemenAdminController extends Controller
{

    public function exportAnggotaExcel()
    {
        return Excel::download(new AnggotaExport, 'data-anggota.xlsx');
    }
    /**
     * Menampilkan daftar semua user (anggota dan admin).
     */
    public function index()
    {
        // Ambil semua data user, urutkan dari yang terbaru
        $users = User::whereIn('role', ['admin', 'user'])->orderBy('id_anggota', 'asc')->get();
        // Kirim data ke view
        return view('admin.manajemen-admin.index', compact('users'));
    }

    /**
     * Menampilkan form untuk membuat user baru.
     */
    public function create()
    {
        return view('admin.manajemen-admin.create');
    }

    /**
     * Menyimpan user baru ke dalam database.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'bin_binti' => ['nullable', 'string', 'max:100'], // Ditambahkan validasi untuk bin_binti
            'jenis_kelamin' => ['nullable', 'in:laki-laki,perempuan'], // Ditambahkan validasi untuk jenis_kelamin
            'email' => ['required', 'string', 'email', 'max:100', 'unique:users,email'],
            'role' => ['required', 'in:user,admin'],
            'tempat_lahir' => ['nullable', 'string', 'max:50'],
            'tanggal_lahir' => ['nullable', 'date'],
            'alamat' => ['nullable', 'string'], // Ditambahkan validasi untuk alamat
            'no_hp' => ['nullable', 'string', 'max:20'],
        ]);

        $tahunDuaDigit = Carbon::now()->format('y');
        $lastUser = User::where('id_anggota', 'like', $tahunDuaDigit . '-%')->orderBy('id_anggota', 'desc')->first();
        $nomorUrut = 1;
        if ($lastUser) {
            $nomorUrut = (int) substr($lastUser->id_anggota, 3) + 1;
        }
        $idAnggotaBaru = $tahunDuaDigit . '-' . str_pad($nomorUrut, 4, '0', STR_PAD_LEFT);

        $user = User::create([
            'id_anggota' => $idAnggotaBaru,
            'name' => $request->name,
            'bin_binti' => $request->bin_binti, // Ditambahkan
            'jenis_kelamin' => $request->jenis_kelamin, // Ditambahkan
            'email' => $request->email,
            'password' => null,
            'role' => $request->role,
            'status' => 'Pending',
            'tempat_lahir' => $request->tempat_lahir,
            'tanggal_lahir' => $request->tanggal_lahir,
            'alamat' => $request->alamat, // Ditambahkan
            'no_hp' => $request->no_hp,
        ]);

        $user->notify(new SendInvitationNotification());
        return redirect()->route('manajemen-admin.index')->with('success', 'User berhasil ditambahkan. Email undangan telah dikirim.');
        Log::info('Test log berhasil masuk');
    }

    /**
     * Menampilkan detail satu user.
     */
    public function show(User $manajemen_admin) // Route Model Binding
    {
        // Variabel $manajemen_admin otomatis berisi data user yang dicari
        return view('admin.manajemen-admin.show', ['user' => $manajemen_admin]);
    }

    /**
     * Menampilkan form untuk mengedit user.
     */
    public function edit(User $manajemen_admin) // Route Model Binding
    {
        return view('admin.manajemen-admin.edit', ['user' => $manajemen_admin]);
    }

    /**
     * Memperbarui data user di dalam database.
     */
    public function update(Request $request, User $manajemen_admin)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'bin_binti' => ['nullable', 'string', 'max:100'], // Ditambahkan validasi
            'jenis_kelamin' => ['nullable', 'in:laki-laki,perempuan'], // Ditambahkan validasi
            'email' => ['required', 'string', 'email', 'max:100', Rule::unique('users')->ignore($manajemen_admin->id)],
            'role' => ['required', 'in:user,admin'],
            'tempat_lahir' => ['nullable', 'string', 'max:50'],
            'tanggal_lahir' => ['nullable', 'date'],
            'alamat' => ['nullable', 'string'], // Ditambahkan validasi
            'no_hp' => ['nullable', 'string', 'max:20'],
        ]);

        $manajemen_admin->update($request->all());

        return redirect()->route('manajemen-admin.index')->with('success', 'Data user berhasil diperbarui.');
    }

    /**
     * Menghapus user dari database.
     */
    public function destroy(User $manajemen_admin)
    {
        $manajemen_admin->delete();

        return redirect()->route('manajemen-admin.index')->with('success', 'User berhasil dihapus.');
    }
}
