<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ChekUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the user is authenticated and has the 'user' role
        if ($request->user() && $request->user()->role === 'user') {
            return $next($request);
        }

        // If not, redirect to a specific route or show an error
        return redirect()->route('home')->with('error', 'You do not have access to this resource.');
    }
}
