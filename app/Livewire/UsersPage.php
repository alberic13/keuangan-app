<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class UsersPage extends Component
{
    public function render()
    {
        $search = trim((string) request('search'));
        $canManageUsers = auth()->user()?->hasRole('admin_keuangan') ?? false;
        $editingUser = $canManageUsers
            ? User::query()->with('roles')->find(request('edit_user'))
            : null;

        return view('livewire.users-page', [
            'users' => User::query()
                ->with('roles')
                ->when($search !== '', function (Builder $query) use ($search) {
                    $query->where(function (Builder $userQuery) use ($search) {
                        $userQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('username', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhereHas('roles', function (Builder $roleQuery) use ($search) {
                                $roleQuery->where('name', 'like', "%{$search}%");
                            });
                    });
                })
                ->orderBy('name')
                ->get(),
            'roles' => Role::query()->orderBy('name')->get(),
            'editingUser' => $editingUser,
            'canManageUsers' => $canManageUsers,
            'baseQuery' => request()->except(['edit_user']),
        ])->layout('layouts.app', [
            'pageTitle' => 'Manajemen Pengguna',
            'pageHeading' => 'Manajemen Pengguna',
            'activeNav' => 'users',
            'searchPlaceholder' => 'Cari pengguna...',
        ]);
    }
}
