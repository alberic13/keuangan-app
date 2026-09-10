<div class="bg-surface-container-lowest rounded-xl shadow-sm overflow-hidden">
    <div class="px-8 py-6 border-b border-surface-container">
        <h3 class="text-lg font-headline font-bold">Buku Kas</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-surface-container-low">
            <tr>
                <th class="px-8 py-4 text-xs uppercase tracking-widest text-slate-500">Tanggal</th>
                <th class="px-8 py-4 text-xs uppercase tracking-widest text-slate-500">Entry No</th>
                <th class="px-8 py-4 text-xs uppercase tracking-widest text-slate-500">Akun</th>
                <th class="px-8 py-4 text-xs uppercase tracking-widest text-slate-500">Deskripsi</th>
                <th class="px-8 py-4 text-xs uppercase tracking-widest text-slate-500">Debit</th>
                <th class="px-8 py-4 text-xs uppercase tracking-widest text-slate-500">Kredit</th>
                <th class="px-8 py-4 text-xs uppercase tracking-widest text-slate-500">Saldo</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-surface-container">
            @forelse ($ledgerRows ?? [] as $row)
                <tr>
                    <td class="px-8 py-4 text-sm whitespace-nowrap">{{ $row['date'] }}</td>
                    <td class="px-8 py-4 text-sm font-semibold">{{ $row['entry_no'] }}</td>
                    <td class="px-8 py-4 text-sm">{{ $row['account'] }}</td>
                    <td class="px-8 py-4 text-sm">{{ $row['description'] }}</td>
                    <td class="px-8 py-4 text-sm">Rp {{ number_format($row['debit'], 0, ',', '.') }}</td>
                    <td class="px-8 py-4 text-sm">Rp {{ number_format($row['credit'], 0, ',', '.') }}</td>
                    <td class="px-8 py-4 text-sm font-semibold">Rp {{ number_format($row['balance'], 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td class="px-8 py-6 text-sm text-on-surface-variant" colspan="7">Belum ada data buku kas.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
