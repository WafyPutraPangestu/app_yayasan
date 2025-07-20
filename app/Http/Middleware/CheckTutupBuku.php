<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTutupBuku
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // Kalau ada tanggal di request (POST/PUT)
        if ($request->has('tanggal')) {
            $tanggal = Carbon::parse($request->tanggal);

            if ($tanggal->format('Y-m') !== now()->format('Y-m')) {
                return back()->withErrors([
                    'error' => 'Transaksi bulan ini sudah ditutup, tidak bisa diubah.'
                ]);
            }
        }

        // Kalau ada Model di route binding (edit/delete)
        if ($request->route('ka')) {
            $ka = $request->route('ka');
            if ($ka->tanggal->format('Y-m') !== now()->format('Y-m')) {
                return back()->withErrors([
                    'error' => 'Transaksi bulan ini sudah ditutup, tidak bisa diubah.'
                ]);
            }
        }

        return $next($request);
    }
}
