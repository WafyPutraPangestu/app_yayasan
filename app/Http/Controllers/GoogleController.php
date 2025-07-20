<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;


class GoogleController extends Controller
{
    public function index()
    {
        return view('auth.login');
    }
    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:8',
        ]);

        if (Auth::attempt($request->only('email', 'password'))) {
            return redirect()->intended('home');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }
    public function redirectToGoogle()
    {

        return Socialite::driver('google')->with(['prompt' => 'select_account'])->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            // Cari user berdasarkan google_id
            $user = User::where('google_id', $googleUser->id)->first();

            if ($user) {
                // Jika user sudah ada, langsung login
                Auth::login($user);
                return redirect()->intended('/home');
            } else {
                // Cek apakah user dengan email ini sudah terdaftar oleh admin
                $user = User::where('email', $googleUser->email)->first();

                if ($user) {
                    // Jika email ada (didaftarkan admin), sambungkan google_id dan login
                    $user->update(['google_id' => $googleUser->id, 'avatar' => $googleUser->avatar]);
                    Auth::login($user);
                    return redirect()->intended('/home');
                } else {

                    return redirect('/login')->with('error', 'Email Anda belum terdaftar oleh Admin. Silakan hubungi pengurus yayasan.');
                }
            }
        } catch (\Throwable $th) {

            return redirect('/login')->with('error', 'Terjadi kesalahan saat login dengan Google.');
        }
    }
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home')->with('success', 'Anda telah berhasil logout.');
    }
}
