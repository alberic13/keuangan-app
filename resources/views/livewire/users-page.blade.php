@php
    $roleLabels = [
        'admin_keuangan' => 'Admin Keuangan',
        'bendahara' => 'Bendahara',
        'kepala_madrasah' => 'Kepala Madrasah',
        'waka' => 'Waka',
        'admin_tu' => 'Admin TU',
    ];
@endphp

<div class="space-y-8">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <div class="lg:col-span-4 bg-surface-container-lowest rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-headline font-bold mb-4">Tambah Pengguna</h3>
            <form action="{{ route('users.store') }}" class="space-y-4" method="POST">
                @csrf
                <input class="w-full rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="name" placeholder="Nama lengkap" required type="text">
                <input class="w-full rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="username" placeholder="Username" required type="text">
                <input class="w-full rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="email" placeholder="Email" type="email">
                <input class="w-full rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" minlength="6" name="password" placeholder="Kata sandi minimal 6 karakter" required type="password">
                <select class="w-full rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="role" required>
                    <option value="">Pilih peran</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->name }}">{{ $roleLabels[$role->name] ?? $role->name }}</option>
                    @endforeach
                </select>
                <label class="flex items-center gap-2 text-sm text-on-surface-variant">
                    <input checked name="is_active" type="checkbox" value="1"> Pengguna aktif
                </label>
                <button class="w-full rounded-xl bg-primary text-white font-semibold px-5 py-3" type="submit">Simpan Pengguna</button>
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
                                <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $user->is_active ? 'bg-emerald-100 text-emerald-900' : 'bg-red-100 text-red-900' }}">
                                    {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-8 py-4 text-sm">{{ $user->last_login_at?->format('d/m/Y H:i') ?? '-' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
