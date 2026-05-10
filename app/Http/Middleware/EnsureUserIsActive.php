<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->is_active) {
            return $next($request);
        }

        auth()->guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'User tidak aktif',
                'errors' => [
                    'auth' => ['Akun ini sudah dinonaktifkan.'],
                ],
            ], 403);
        }

        return redirect()
            ->route('login')
            ->withErrors(['login' => 'Akun ini sudah dinonaktifkan.']);
    }
}
