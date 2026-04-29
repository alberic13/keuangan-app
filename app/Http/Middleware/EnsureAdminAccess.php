<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $adminEmail = (string) env('ADMIN_EMAIL', 'admin@man2.test');

        if (! $user || strtolower($user->email) !== strtolower($adminEmail)) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => 'Akses hanya untuk admin.',
            ]);
        }

        return $next($request);
    }
}
