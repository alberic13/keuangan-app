<div class="px-8 py-6 border-b border-surface-container">
    <h3 class="text-lg font-headline font-bold">Riwayat Pembayaran</h3>
    <form class="mt-5 grid grid-cols-1 gap-3 lg:grid-cols-12" method="GET">
        @foreach ($studentFilters as $filterName => $filterValue)
            <input name="{{ $filterName }}" type="hidden" value="{{ $filterValue }}">
        @endforeach

        <input class="rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm lg:col-span-3" name="payment_search" placeholder="Cari no bukti, nama, NIS, akun, catatan" type="text" value="{{ request('payment_search', request('search')) }}">

        <select class="rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm lg:col-span-3" name="payment_student_id">
            <option value="">Semua siswa</option>
            @foreach ($studentFilterOptions as $student)
                <option @selected((string) request('payment_student_id') === (string) $student->id) value="{{ $student->id }}">
                    {{ $student->full_name }} - {{ $student->nis ?: $student->nisn ?: '-' }}
                </option>
            @endforeach
        </select>

        <select class="rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm lg:col-span-2" name="payment_method">
            <option value="">Semua metode</option>
            <option @selected(request('payment_method') === 'cash') value="cash">Tunai</option>
            <option @selected(request('payment_method') === 'bank_transfer') value="bank_transfer">Transfer Manual</option>
        </select>

        <select class="rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm lg:col-span-2" name="payment_status">
            <option value="">Semua status</option>
            <option @selected(request('payment_status') === 'posted') value="posted">Tersimpan</option>
            <option @selected(request('payment_status') === 'edited') value="edited">Diedit</option>
        </select>

        <div class="grid grid-cols-2 gap-3 lg:col-span-4">
            <input class="rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="payment_date_from" title="Tanggal mulai" type="date" value="{{ request('payment_date_from') }}">
            <input class="rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="payment_date_to" title="Tanggal akhir" type="date" value="{{ request('payment_date_to') }}">
        </div>

        <div class="flex gap-3 lg:col-span-3">
            <button class="flex-1 rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-white" type="submit">Terapkan</button>
            <a class="rounded-xl bg-surface-container-low px-5 py-3 text-sm font-semibold text-primary" href="{{ route('payments.index', $studentFilters) }}">Reset</a>
        </div>
    </form>
</div>
