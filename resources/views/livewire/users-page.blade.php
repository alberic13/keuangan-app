@php
    $roleLabels = [
        'admin_keuangan' => 'Admin Keuangan',
        'bendahara' => 'Bendahara',
        'kepala_madrasah' => 'Kepala Madrasah',
        'waka' => 'Waka',
        'admin_tu' => 'Admin TU',
    ];
    $isEditingUser = $editingUser !== null;
    $userFormAction = $isEditingUser ? route('users.update', $editingUser) : route('users.store');
@endphp

<div class="space-y-8">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <div class="lg:col-span-4 bg-surface-container-lowest rounded-xl shadow-sm p-6">
            <div class="mb-4 flex items-start justify-between gap-4">
                <div>
                    <h3 class="text-lg font-headline font-bold">{{ $isEditingUser ? 'Edit Pengguna' : 'Tambah Pengguna' }}</h3>
                    <p class="text-sm text-on-surface-variant">
                        {{ $isEditingUser ? 'Perbarui data pengguna yang dipilih.' : 'Tambahkan akun pengguna baru.' }}
                    </p>
                </div>
                @if ($isEditingUser)
                    <a class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50" href="{{ route('users.index', $baseQuery) }}">
                        Batal
                    </a>
                @endif
            </div>
            <form action="{{ $userFormAction }}" class="space-y-4" method="POST">
                @csrf
                @if ($isEditingUser)
                    @method('PUT')
                @endif
                <input class="w-full rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="name" placeholder="Nama lengkap" required type="text" value="{{ old('name', $editingUser?->name) }}">
                <input class="w-full rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="username" placeholder="Username" required type="text" value="{{ old('username', $editingUser?->username) }}">
                <input class="w-full rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="email" placeholder="Email" type="email" value="{{ old('email', $editingUser?->email) }}">
                <input class="w-full rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" minlength="6" name="password" placeholder="{{ $isEditingUser ? 'Kosongkan jika tidak diubah' : 'Kata sandi minimal 6 karakter' }}" type="password" @if (! $isEditingUser) required @endif>
                <select class="w-full rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="role" required>
                    <option value="">Pilih peran</option>
                    @foreach ($roles as $role)
                        <option @selected(old('role', $editingUser?->getRoleNames()->first()) === $role->name) value="{{ $role->name }}">{{ $roleLabels[$role->name] ?? $role->name }}</option>
                    @endforeach
                </select>
                <label class="flex items-center gap-2 text-sm text-on-surface-variant">
                    <input @checked(old('is_active', $editingUser?->is_active ?? true)) name="is_active" type="checkbox" value="1"> Pengguna aktif
                </label>
                <button class="w-full rounded-xl bg-primary text-white font-semibold px-5 py-3" type="submit">{{ $isEditingUser ? 'Perbarui Pengguna' : 'Simpan Pengguna' }}</button>
            </form>
        </div>

        <div class="lg:col-span-8 bg-surface-container-lowest rounded-xl shadow-sm overflow-hidden">
            <div class="px-8 py-6 border-b border-surface-container">
                <h3 class="text-lg font-headline font-bold">Daftar Pengguna</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-surface-container-low">
                    <tr>
                        <th class="px-8 py-4 text-xs uppercase tracking-widest text-slate-500">Nama</th>
                        <th class="px-8 py-4 text-xs uppercase tracking-widest text-slate-500">Username</th>
                        <th class="px-8 py-4 text-xs uppercase tracking-widest text-slate-500">Email</th>
                        <th class="px-8 py-4 text-xs uppercase tracking-widest text-slate-500">Role</th>
                        <th class="px-8 py-4 text-xs uppercase tracking-widest text-slate-500">Status</th>
                        <th class="px-8 py-4 text-xs uppercase tracking-widest text-slate-500">Login Terakhir</th>
                        @if ($canManageUsers)
                            <th class="px-8 py-4 text-xs uppercase tracking-widest text-slate-500 text-right">Aksi</th>
                        @endif
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-container">
                    @foreach ($users as $user)
                        <tr>
                            <td class="px-8 py-4 text-sm font-semibold">{{ $user->name }}</td>
                            <td class="px-8 py-4 text-sm">{{ $user->username }}</td>
                            <td class="px-8 py-4 text-sm">{{ $user->email ?: '-' }}</td>
                            <td class="px-8 py-4 text-sm">{{ $user->getRoleNames()->map(fn ($role) => $roleLabels[$role] ?? $role)->implode(', ') ?: '-' }}</td>
                            <td class="px-8 py-4 text-sm">
                                @if ($canManageUsers)
                                    <form action="{{ route('users.status', $user) }}" method="POST" class="flex items-center gap-3">
                                        @csrf
                                        @method('PATCH')
                                        <input class="status-value" name="is_active" type="hidden" value="{{ $user->is_active ? 1 : 0 }}">
                                        <button
                                            class="relative inline-flex h-7 w-12 items-center rounded-full transition {{ $user->is_active ? 'bg-emerald-500' : 'bg-slate-300' }}"
                                            type="submit"
                                            onclick="event.preventDefault(); const hidden = this.closest('form').querySelector('.status-value'); hidden.value = hidden.value === '1' ? '0' : '1'; this.closest('form').submit();"
                                            title="Ubah status"
                                            aria-label="Ubah status"
                                        >
                                            <span class="inline-flex h-5 w-5 translate-x-1 rounded-full bg-white shadow transition {{ $user->is_active ? 'translate-x-6' : '' }}"></span>
                                        </button>
                                        <span class="text-xs font-semibold {{ $user->is_active ? 'text-emerald-700' : 'text-slate-500' }}">
                                            {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </form>
                                @else
                                    <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $user->is_active ? 'bg-emerald-100 text-emerald-900' : 'bg-red-100 text-red-900' }}">
                                        {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-8 py-4 text-sm">{{ $user->last_login_at?->format('d/m/Y H:i') ?? '-' }}</td>
                            @if ($canManageUsers)
                                <td class="px-8 py-4 text-sm">
                                    <div class="flex items-center justify-end gap-2">
                                        <a
                                            class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 text-slate-600 hover:bg-slate-50"
                                            href="{{ route('users.index', array_merge($baseQuery, ['edit_user' => $user->id])) }}"
                                            title="Edit pengguna"
                                            aria-label="Edit pengguna"
                                        >
                                            <svg viewBox="0 0 24 24" class="h-4 w-4" aria-hidden="true">
                                                <path d="M4 20h4l10.5-10.5-4-4L4 16v4zm13.7-12.3 1.6-1.6a1.4 1.4 0 0 0 0-2l-1.4-1.4a1.4 1.4 0 0 0-2 0l-1.6 1.6 3.4 3.4z" fill="currentColor"/>
                                            </svg>
                                        </a>
                                        @if (auth()->id() !== $user->id)
                                            <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('Hapus pengguna ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-rose-200 text-rose-600 hover:bg-rose-50" title="Hapus pengguna" aria-label="Hapus pengguna" type="submit">
                                                    <svg viewBox="0 0 24 24" class="h-4 w-4" aria-hidden="true">
                                                        <path d="M9 3h6l1 2h4v2H4V5h4l1-2zm1 7h2v8h-2v-8zm4 0h2v8h-2v-8zM7 8h10l-1 12H8L7 8z" fill="currentColor"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
