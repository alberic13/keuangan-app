<div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
    <div class="lg:col-span-4 bg-surface-container-lowest rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-headline font-bold mb-4">Catat Kas Keluar</h3>
        <form action="{{ route('expenses.store') }}" class="space-y-4" method="POST">
            @csrf
            <input class="w-full rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="transaction_date" required type="date" value="{{ now()->toDateString() }}">
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
