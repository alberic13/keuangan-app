<div class="space-y-8">
    <div class="bg-surface-container-lowest rounded-xl shadow-sm p-6">
        <form class="grid grid-cols-1 md:grid-cols-5 gap-4" method="GET">
            <input class="rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="search" placeholder="Cari aktor, alasan, entitas, atau snapshot" type="text" value="{{ request('search') }}">
            <input class="rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="actor_id" placeholder="Filter ID aktor" type="number" value="{{ request('actor_id') }}">
            <input class="rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="entity_type" placeholder="Jenis entitas" type="text" value="{{ request('entity_type') }}">
            <input class="rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="action" placeholder="Aksi" type="text" value="{{ request('action') }}">
            <button class="rounded-xl bg-primary text-white font-semibold px-5 py-3 text-sm" type="submit">Filter Audit</button>
        </form>
    </div>

    <div class="bg-surface-container-lowest rounded-xl shadow-sm overflow-hidden">
        <div class="px-8 py-6 border-b border-surface-container">
            <h3 class="text-lg font-headline font-bold">Jejak Audit</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-surface-container-low">
                <tr>
                    <th class="px-8 py-4 text-xs uppercase tracking-widest text-slate-500">Waktu</th>
                    <th class="px-8 py-4 text-xs uppercase tracking-widest text-slate-500">Aktor</th>
                    <th class="px-8 py-4 text-xs uppercase tracking-widest text-slate-500">Entitas</th>
                    <th class="px-8 py-4 text-xs uppercase tracking-widest text-slate-500">Aksi</th>
                    <th class="px-8 py-4 text-xs uppercase tracking-widest text-slate-500">Alasan</th>
                    <th class="px-8 py-4 text-xs uppercase tracking-widest text-slate-500">Cuplikan</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-surface-container">
                @forelse ($logs as $log)
                    <tr class="align-top">
                        <td class="px-8 py-4 text-sm whitespace-nowrap">
                            <p class="font-semibold">{{ $log->created_at?->format('d/m/Y') }}</p>
                            <p class="text-xs text-on-surface-variant">{{ $log->created_at?->format('H:i:s') }}</p>
                        </td>
                        <td class="px-8 py-4 text-sm">
                            <p class="font-semibold">{{ $log->actor?->name ?? 'Sistem' }}</p>
                            <p class="text-xs text-on-surface-variant">{{ $log->actor?->username ?? '-' }}</p>
                        </td>
                        <td class="px-8 py-4 text-sm">
                            <p class="font-semibold">{{ $log->entity_type }}</p>
                            <p class="text-xs text-on-surface-variant">ID: {{ $log->entity_id ?? '-' }}</p>
                        </td>
                        <td class="px-8 py-4 text-sm">
                            <span class="rounded-full bg-secondary-container px-2 py-1 text-xs font-semibold text-on-surface">{{ $log->action }}</span>
                        </td>
                        <td class="px-8 py-4 text-sm text-on-surface-variant">{{ $log->reason ?? '-' }}</td>
                        <td class="px-8 py-4 text-xs text-on-surface-variant">
                            <div class="space-y-2">
                                <div>
                                    <p class="font-semibold text-on-surface">Sebelum</p>
                                    <pre class="mt-1 max-w-xs overflow-x-auto rounded-lg bg-surface-container-low p-3">{{ json_encode($log->before_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: '-' }}</pre>
                                </div>
                                <div>
                                    <p class="font-semibold text-on-surface">Sesudah</p>
                                    <pre class="mt-1 max-w-xs overflow-x-auto rounded-lg bg-surface-container-low p-3">{{ json_encode($log->after_json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?: '-' }}</pre>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-8 py-6 text-sm text-on-surface-variant" colspan="6">Belum ada audit log.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
