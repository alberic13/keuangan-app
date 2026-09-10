<div class="bg-surface-container-lowest rounded-xl shadow-sm overflow-hidden">
    <div class="px-8 py-6 border-b border-surface-container">
        <h3 class="text-lg font-headline font-bold">Siklus Tagihan Aktif</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-surface-container-low">
            <tr>
                <th class="px-8 py-4 text-xs uppercase tracking-widest text-slate-500">Periode</th>
                <th class="px-8 py-4 text-xs uppercase tracking-widest text-slate-500">Jatuh Tempo</th>
                <th class="px-8 py-4 text-xs uppercase tracking-widest text-slate-500">Status</th>
                <th class="px-8 py-4 text-xs uppercase tracking-widest text-slate-500">Aksi</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-surface-container">
            @foreach ($cycles as $cycle)
                <tr>
                    <td class="px-8 py-4 text-sm font-semibold">{{ $cycle->period_label }}</td>
                    <td class="px-8 py-4 text-sm">{{ $cycle->due_date?->format('d/m/Y') }}</td>
                    <td class="px-8 py-4 text-sm">
                        <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $cycle->status === 'open' ? 'bg-emerald-100 text-emerald-900' : 'bg-slate-200 text-slate-700' }}">
                            {{ $cycleStatusLabels[$cycle->status] ?? strtoupper($cycle->status) }}
                        </span>
                    </td>
                    <td class="px-8 py-4 text-sm">
                        @if ($cycle->status === 'open')
                            <form action="{{ route('billing-cycles.close', $cycle) }}" method="POST">
                                @csrf
                                <button class="font-semibold text-red-800 transition-colors hover:text-red-900" type="submit">Tutup Siklus</button>
                            </form>
                        @else
                            <form action="{{ route('billing-cycles.open', $cycle) }}" method="POST">
                                @csrf
                                <button class="font-semibold text-emerald-800 transition-colors hover:text-emerald-900" type="submit">Buka Siklus</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
