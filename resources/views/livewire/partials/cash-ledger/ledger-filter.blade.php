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
