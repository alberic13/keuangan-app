@php
    $accountTypeLabels = [
        'cash' => 'Tunai',
        'bank' => 'Bank',
    ];
    $sectionLinks = [
        'ledger' => route('cash-ledger.index', ['section' => 'ledger']),
        'expenses' => route('cash-ledger.index', ['section' => 'expenses']),
    ];
@endphp

<div class="space-y-8">
    <div class="bg-surface-container-lowest rounded-xl shadow-sm p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h3 class="text-lg font-headline font-bold text-on-surface">Kelola Arus Kas Sekolah</h3>
                <p class="text-sm text-on-surface-variant">Gunakan tombol mode di bawah untuk berpindah antara pemantauan buku kas dan pencatatan kas keluar.</p>
            </div>
            <div class="inline-flex w-full flex-col gap-3 rounded-2xl bg-surface-container-low p-2 sm:w-auto sm:flex-row">
                <a class="inline-flex items-center justify-center rounded-xl px-5 py-3 text-sm font-semibold transition-all {{ $activeSection === 'ledger' ? 'bg-primary text-white shadow-sm' : 'text-on-surface hover:bg-surface-container-high' }}" href="{{ $sectionLinks['ledger'] }}">
                    Buku Kas
                </a>
                <a class="inline-flex items-center justify-center rounded-xl px-5 py-3 text-sm font-semibold transition-all {{ $activeSection === 'expenses' ? 'bg-primary text-white shadow-sm' : 'text-on-surface hover:bg-surface-container-high' }}" href="{{ $sectionLinks['expenses'] }}">
                    Kas Keluar
                </a>
            </div>
        </div>
    </div>

    @if ($activeSection === 'ledger')
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <div class="lg:col-span-4 bg-surface-container-lowest rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-headline font-bold mb-4">Filter Buku Kas</h3>
                <form class="space-y-4" method="GET">
                    <input name="section" type="hidden" value="ledger">
                    <input class="w-full rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="search" placeholder="Cari nomor entry, akun, atau deskripsi" type="text" value="{{ request('search') }}">
                    <select class="w-full rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="account_id">
                        <option value="">Semua akun</option>
                        @foreach ($accounts as $account)
                            <option @selected((string) request('account_id') === (string) $account->id) value="{{ $account->id }}">{{ $account->name }}</option>
                        @endforeach
                    </select>
                    <select class="w-full rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="direction">
                        <option value="">Semua arah</option>
                        <option @selected(request('direction') === 'in') value="in">Masuk</option>
                        <option @selected(request('direction') === 'out') value="out">Keluar</option>
                    </select>
                    <select class="w-full rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="source_type">
                        <option value="">Semua sumber</option>
                        <option @selected(request('source_type') === 'payment') value="payment">Pembayaran</option>
                        <option @selected(request('source_type') === 'expense') value="expense">Pengeluaran</option>
                    </select>
                    <div class="grid grid-cols-2 gap-4">
                        <input class="rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="date_from" type="date" value="{{ request('date_from') }}">
                        <input class="rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="date_to" type="date" value="{{ request('date_to') }}">
                    </div>
                    <button class="w-full rounded-xl bg-primary text-white font-semibold px-5 py-3" type="submit">Terapkan Filter</button>
                </form>
            </div>

            <div class="lg:col-span-8 bg-surface-container-lowest rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-headline font-bold mb-4">Ringkasan Akun</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach ($accounts as $account)
                        <div class="rounded-xl bg-surface-container-low p-4">
                            <p class="text-xs uppercase tracking-widest text-on-surface-variant">{{ $accountTypeLabels[$account->type] ?? strtoupper($account->type) }}</p>
                            <p class="mt-1 font-semibold text-on-surface">{{ $account->name }}</p>
                            <p class="text-sm text-on-surface-variant">{{ $account->account_number ?: 'Tanpa nomor akun' }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

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
                    @forelse ($ledgerRows as $row)
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
    @else
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <div class="lg:col-span-4 bg-surface-container-lowest rounded-xl shadow-sm p-6">
                <h3 class="text-lg font-headline font-bold mb-4">Catat Kas Keluar</h3>
                <form action="{{ route('expenses.store') }}" class="space-y-4" method="POST">
                    @csrf
                    <input class="w-full rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" min="{{ now()->toDateString() }}" name="transaction_date" required type="date" value="{{ now()->toDateString() }}">
                    <select class="w-full rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="category_id" required>
                        <option value="">Pilih kategori</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <select class="w-full rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="payment_account_id" required>
                        <option value="">Pilih akun bayar</option>
                        @foreach ($accounts as $account)
                            <option value="{{ $account->id }}">{{ $account->name }}</option>
                        @endforeach
                    </select>
                    <input class="w-full rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" min="1" name="amount" placeholder="Nominal" required type="number">
                    <textarea class="w-full rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="description" placeholder="Deskripsi pengeluaran" required rows="4"></textarea>
                    <button class="w-full rounded-xl bg-primary text-white font-semibold px-5 py-3" type="submit">Simpan Pengeluaran</button>
                </form>
            </div>

            <div class="lg:col-span-8 space-y-8">
                <div class="bg-surface-container-lowest rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-headline font-bold mb-4">Tambah Kategori Pengeluaran</h3>
                    <form action="{{ route('expense-categories.store') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4" method="POST">
                        @csrf
                        <input class="rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="code" placeholder="Kode" required type="text">
                        <input class="rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="name" placeholder="Nama kategori" required type="text">
                        <button class="rounded-xl bg-primary text-white font-semibold px-5 py-3" type="submit">Tambah Kategori</button>
                    </form>
                </div>

                <div class="bg-surface-container-lowest rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-headline font-bold mb-4">Akun Kas / Bank Aktif</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach ($accounts as $account)
                            <div class="rounded-xl bg-surface-container-low p-4">
                                <p class="text-xs uppercase tracking-widest text-on-surface-variant">{{ $accountTypeLabels[$account->type] ?? strtoupper($account->type) }}</p>
                                <p class="mt-1 font-semibold">{{ $account->name }}</p>
                                <p class="text-sm text-on-surface-variant">{{ $account->account_holder ?: 'Tanpa nama pemegang akun' }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

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
    @endif
</div>
