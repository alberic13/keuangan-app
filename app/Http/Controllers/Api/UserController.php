<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    use ApiResponses;

    public function __construct(
        protected AuditLogService $auditLogs,
    ) {
    }

    public function index()
    {
        return $this->success(User::query()->with('roles')->orderBy('name')->get());
    }

    public function store(Request $request)
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

        return $this->success($user->load('roles'), 'Success', 201);
    }

    public function update(Request $request, User $user)
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

        return $this->success($user->load('roles'));
    }
}
