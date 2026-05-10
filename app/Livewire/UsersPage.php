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
        ])->layout('layouts.app', [
            'pageTitle' => 'Manajemen Pengguna',
            'pageHeading' => 'Manajemen Pengguna',
            'activeNav' => 'users',
            'searchPlaceholder' => 'Cari pengguna...',
        ]);
    }
}
