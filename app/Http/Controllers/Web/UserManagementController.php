<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserManagementController extends Controller
{
    public function __construct(
        protected AuditLogService $auditLogs,
    ) {
    }

    public function store(Request $request): RedirectResponse
    {
        $this->ensureAnyRole(['admin_keuangan']);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', Rule::exists('roles', 'name')],
        ]);

        $user = User::query()->create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'] ?? null,
            'password' => $data['password'],
            'is_active' => $request->boolean('is_active', true),
        ]);
        $user->syncRoles([$data['role']]);

        $this->auditLogs->log('user.created', $user, null, $user->toArray(), null, $request->user());

        return $this->redirectBackWithMessage($request, 'User berhasil ditambahkan.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->ensureAnyRole(['admin_keuangan']);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($user->id)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:6'],
            'role' => ['required', Rule::exists('roles', 'name')],
        ]);

        $before = $user->toArray();
        $user->update([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'] ?? null,
            'password' => $data['password'] ?: $user->password,
            'is_active' => $request->boolean('is_active', $user->is_active),
        ]);
        $user->syncRoles([$data['role']]);

        $this->auditLogs->log('user.updated', $user, $before, $user->fresh()->toArray(), null, $request->user());

        return $this->redirectBackWithMessage($request, 'User berhasil diperbarui.');
    }
}
