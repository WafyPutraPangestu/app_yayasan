<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class InvitationController extends Controller
{
    // Method untuk menampilkan form
    public function showPasswordForm(User $user)
    {
        // Cek dulu apakah user masih pending
        if ($user->status !== 'pending' || $user->password !== null) {
            return redirect('/login')->with('error', 'Link undangan tidak valid atau sudah digunakan.');
        }
        return view('auth.create-password', ['user' => $user]);
    }

    // Method untuk menyimpan password
    public function storePassword(Request $request, User $user)
    {
        // 1. Validasi password
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        // 2. Update data user
        $user->update([
            'password' => Hash::make($request->password),
            'email_verified_at' => now(), // Tandai email sebagai terverifikasi
            'status' => 'aktif', // <-- PENTING: Ubah status menjadi aktif
        ]);

        // 3. Login-kan user secara otomatis
        Auth::login($user);

        // 4. Arahkan ke dashboard
        return redirect('/dashboard')->with('success', 'Akun Anda telah berhasil diaktifkan!');
    }
}
