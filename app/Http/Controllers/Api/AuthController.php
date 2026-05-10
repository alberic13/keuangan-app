<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use ApiResponses;

    public function __construct(
        protected AuditLogService $auditLogs,
    ) {
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()
            ->where('username', $credentials['login'])
            ->orWhere('email', $credentials['login'])
            ->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password) || ! $user->is_active) {
            throw ValidationException::withMessages([
                'login' => 'Username/email atau kata sandi tidak valid.',
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();
        $user->update(['last_login_at' => now()]);

        $this->auditLogs->log('auth.login', $user, null, ['last_login_at' => $user->last_login_at], null, $user);

        return $this->success([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->getRoleNames()->first(),
            ],
        ], 'Login berhasil');
    }

    public function logout(Request $request)
    {
        if ($request->user()) {
            $this->auditLogs->log('auth.logout', $request->user(), null, null, null, $request->user());
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return $this->success([], 'Logout berhasil');
    }

    public function me(Request $request)
    {
        $user = $request->user()->load('roles');

        return $this->success([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->getRoleNames()->first(),
            ],
        ]);
    }
}
