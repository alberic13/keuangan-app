<div class="lg:col-span-4 bg-surface-container-lowest rounded-xl shadow-sm p-6">
    <h3 class="text-lg font-headline font-bold mb-4">Buat Siklus Tagihan</h3>
    <form action="{{ route('billing-cycles.store') }}" class="space-y-4" method="POST">
        @csrf
        <div class="grid grid-cols-2 gap-4">
            <input class="rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" max="12" min="1" name="month" placeholder="Bulan" required type="number">
            <input class="rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" min="2020" name="year" placeholder="Tahun" required type="number" value="{{ now()->year }}">
        </div>
        <input class="w-full rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="period_label" placeholder="Contoh: April 2026" required type="text">
        <div class="space-y-1">
            <label class="block text-xs font-bold text-on-surface-variant px-1" for="due_date">JATUH TEMPO PADA :</label>
            <input class="w-full rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" id="due_date" name="due_date" type="date" value="{{ now()->day(10)->toDateString() }}">
        </div>
        <button class="w-full rounded-xl bg-primary text-white font-semibold px-5 py-3" type="submit">Simpan Siklus Tagihan</button>
    </form>
</div>
