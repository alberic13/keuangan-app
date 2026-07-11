<div class="space-y-8">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <div class="lg:col-span-4 bg-surface-container-lowest rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between gap-3 mb-4">
                <h3 class="text-lg font-headline font-bold">{{ $editingExpense ? 'Edit Pengeluaran' : 'Catat Pengeluaran' }}</h3>
                @if ($editingExpense)
                    <a href="{{ route('expenses.index') }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-800">Batal</a>
                @endif
            </div>
            <form action="{{ $editingExpense ? route('expenses.update', $editingExpense) : route('expenses.store') }}" class="space-y-4" method="POST">
                @csrf
                @if ($editingExpense)
                    @method('PUT')
                @endif
                <input class="w-full rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="transaction_date" required type="date" value="{{ old('transaction_date', $editingExpense?->transaction_date?->format('Y-m-d') ?? now()->toDateString()) }}">
                <select class="w-full rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="category_id" required>
                    <option value="">Pilih kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('category_id', $editingExpense?->category_id) == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                <select class="w-full rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="payment_account_id" required>
                    <option value="">Pilih akun bayar</option>
                    @foreach ($accounts as $account)
                        <option value="{{ $account->id }}" @selected(old('payment_account_id', $editingExpense?->payment_account_id) == $account->id)>{{ $account->name }}</option>
                    @endforeach
                </select>
                <input class="w-full rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" min="1" name="amount" placeholder="Nominal" required type="number" value="{{ old('amount', $editingExpense?->amount) }}">
                <textarea class="w-full rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="description" placeholder="Deskripsi pengeluaran" required rows="4">{{ old('description', $editingExpense?->description) }}</textarea>
                <button class="w-full rounded-xl bg-primary text-white font-semibold px-5 py-3" type="submit">{{ $editingExpense ? 'Simpan Perubahan' : 'Simpan Pengeluaran' }}</button>
            </form>
        </div>

        <div class="lg:col-span-8 space-y-8">
            <div class="bg-surface-container-lowest rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-headline font-bold mb-4">Tambah Kategori Pengeluaran</h3>
                <form action="{{ route('expense-categories.store') }}" class="grid grid-cols-1 md:grid-cols-2 gap-4" method="POST">
                    @csrf
                    <input class="rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="name" placeholder="Nama kategori" required type="text">
                    <button class="rounded-xl bg-primary text-white font-semibold px-5 py-3 md:col-span-2" type="submit">Tambah Kategori</button>
                </form>
            </div>

            <div class="bg-surface-container-lowest rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-headline font-bold mb-4">Akun Kas / Bank Aktif</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach ($accounts as $account)
                        <div class="rounded-xl bg-surface-container-low p-4">
                            <p class="text-xs uppercase tracking-widest text-on-surface-variant">{{ strtoupper($account->type) }}</p>
                            <p class="mt-1 font-semibold">{{ $account->name }}</p>
                            <p class="text-sm text-on-surface-variant">{{ $account->account_holder ?: 'Tanpa nama holder' }}</p>
                            @php
                                $balance = (int) ($account->incoming_total ?? 0) - (int) ($account->outgoing_total ?? 0);
                            @endphp
                            <div class="mt-3 rounded-lg bg-white/70 px-3 py-2 text-sm font-semibold text-slate-700">
                                Saldo: <span class="text-emerald-700">Rp {{ number_format($balance, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="bg-surface-container-lowest rounded-xl shadow-sm overflow-hidden">
        <div class="px-8 py-6 border-b border-surface-container flex items-center justify-between">
            <h3 class="text-lg font-headline font-bold">Riwayat Pengeluaran</h3>
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
                    <th class="px-8 py-4 text-xs uppercase tracking-widest text-slate-500">Action</th>
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
                        <td class="px-8 py-4 text-sm">
                            <div class="flex items-center gap-2">
                                <a
                                    href="{{ route('expenses.print', $expense) }}"
                                    target="_blank"
                                    class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-emerald-200 bg-white text-emerald-600 transition hover:border-emerald-300 hover:text-emerald-700"
                                    title="Cetak bukti pengeluaran"
                                    aria-label="Cetak bukti pengeluaran"
                                >
                                    <svg viewBox="0 0 24 24" aria-hidden="true" class="h-4 w-4">
                                        <path d="M19 8H5c-1.66 0-3 1.34-3 3v6h4v4h12v-4h4v-6c0-1.66-1.34-3-3-3zm-3 11H8v-5h8v5zm3-7c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm-1-9H6v4h12V3z" fill="currentColor"/>
                                    </svg>
                                </a>
                                <a
                                    href="{{ route('expenses.index', array_merge(request()->except('edit', 'page'), ['edit' => $expense->id])) }}"
                                    class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-blue-200 bg-white text-blue-600 transition hover:border-blue-300 hover:text-blue-700"
                                    title="Edit pengeluaran"
                                    aria-label="Edit pengeluaran"
                                >
                                    <svg viewBox="0 0 24 24" aria-hidden="true" class="h-4 w-4">
                                        <path d="M4 20h4l10.5-10.5-4-4L4 16v4zm13.7-12.3 1.6-1.6a1.4 1.4 0 0 0 0-2l-1.4-1.4a1.4 1.4 0 0 0-2 0l-1.6 1.6 3.4 3.4z" fill="currentColor"/>
                                    </svg>
                                </a>
                                <form action="{{ route('expenses.destroy', $expense) }}" method="POST" onsubmit="return confirm('Hapus pengeluaran ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        type="submit"
                                        class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-red-200 bg-white text-red-600 transition hover:border-red-300 hover:text-red-700"
                                        title="Hapus pengeluaran"
                                        aria-label="Hapus pengeluaran"
                                    >
                                        <svg viewBox="0 0 24 24" aria-hidden="true" class="h-4 w-4">
                                            <path d="M9 3h6l1 2h4v2H4V5h4l1-2zm1 7h2v8h-2v-8zm4 0h2v8h-2v-8zM7 8h10l-1 12H8L7 8z" fill="currentColor"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-8 py-6 text-sm text-on-surface-variant" colspan="7">Belum ada pengeluaran.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
