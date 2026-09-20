<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        // Also reject accounts restored through an existing session or remember cookie.
        if (Auth::guard('web')->check() && ! Auth::guard('web')->user()->is_active) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Akun tidak aktif. Silakan hubungi administrator.',
            ]);
        }

        return $next($request);
    }
}
