<div class="bg-surface-container-lowest rounded-xl shadow-sm overflow-hidden">
    <div class="px-8 py-6 border-b border-surface-container flex items-center justify-between gap-4">
        <div>
            <h3 class="text-lg font-headline font-bold">Riwayat Pengeluaran</h3>
            <p class="text-sm text-on-surface-variant">Pantau seluruh transaksi kas keluar dalam satu daftar.</p>
        </div>
        <form action="{{ route('cash-ledger.index') }}" class="w-full max-w-sm" method="GET">
            <input name="section" type="hidden" value="expenses">
            <input class="w-full rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="search" placeholder="Cari nomor bukti, kategori, akun, atau deskripsi" type="text" value="{{ request('search') }}">
        </form>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-surface-container-low">
            <tr>
                <th class="px-8 py-4 text-xs uppercase tracking-widest text-slate-500">No Bukti</th>
                <th class="px-8 py-4 text-xs uppercase tracking-widest text-slate-500">Tanggal</th>
                <th class="px-8 py-4 text-xs uppercase tracking-widest text-slate-500">Kategori</th>
                <th class="px-8 py-4 text-xs uppercase tracking-widest text-slate-500">Akun</th>
                <th class="px-8 py-4 text-xs uppercase tracking-widest text-slate-500">Deskripsi</th>
                <th class="px-8 py-4 text-xs uppercase tracking-widest text-slate-500">Nominal</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-surface-container">
            @forelse ($expenses as $expense)
                <tr>
                    <td class="px-8 py-4 text-sm font-semibold">{{ $expense->expense_no }}</td>
                    <td class="px-8 py-4 text-sm">{{ $expense->transaction_date?->format('d/m/Y') }}</td>
                    <td class="px-8 py-4 text-sm">{{ $expense->category->name }}</td>
                    <td class="px-8 py-4 text-sm">{{ $expense->paymentAccount->name }}</td>
                    <td class="px-8 py-4 text-sm">{{ $expense->description }}</td>
                    <td class="px-8 py-4 text-sm font-semibold">Rp {{ number_format($expense->amount, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td class="px-8 py-6 text-sm text-on-surface-variant" colspan="6">Belum ada pengeluaran.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
