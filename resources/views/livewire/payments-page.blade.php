@php
    $paymentStatusLabels = [
        'posted' => 'Tersimpan',
        'edited' => 'Diedit',
    ];

    $studentFilters = array_filter([
        'student_search' => request('student_search'),
        'student_class_id' => request('student_class_id'),
        'student_batch_id' => request('student_batch_id'),
    ], fn ($value) => filled($value));

    $paymentFilters = array_filter([
        'payment_search' => request('payment_search'),
        'payment_student_id' => request('payment_student_id'),
        'payment_method' => request('payment_method'),
        'payment_status' => request('payment_status'),
        'payment_date_from' => request('payment_date_from'),
        'payment_date_to' => request('payment_date_to'),
    ], fn ($value) => filled($value));

    $currentFilters = array_merge($studentFilters, $paymentFilters);
@endphp

<div class="space-y-8">
    <div class="bg-surface-container-lowest rounded-xl shadow-sm p-6">
        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
            <h3 class="text-lg font-headline font-bold mb-4">Pilih Siswa</h3>
            <span class="rounded-full bg-secondary-container px-3 py-1 text-xs font-semibold text-on-surface">
                {{ $students->count() }} siswa tampil
            </span>
        </div>

            <form class="mt-5 grid grid-cols-1 gap-3 lg:grid-cols-12" method="GET">
                @foreach ($paymentFilters as $filterName => $filterValue)
                    <input name="{{ $filterName }}" type="hidden" value="{{ $filterValue }}">
                @endforeach

                <input class="rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm lg:col-span-4" name="student_search" placeholder="Cari nama siswa, NIS, atau angkatan" type="text" value="{{ request('student_search', request('search')) }}">

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:col-span-4">
                    <select class="rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="student_class_id">
                        <option value="">Semua kelas</option>
                        @foreach ($classOptions as $class)
                            <option @selected((string) request('student_class_id') === (string) $class->id) value="{{ $class->id }}">
                                {{ $class->name }}
                            </option>
                        @endforeach
                    </select>

                    <select class="rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="student_batch_id">
                        <option value="">Semua angkatan</option>
                        @foreach ($batchOptions as $batch)
                            <option @selected((string) request('student_batch_id') === (string) $batch->id) value="{{ $batch->id }}">
                                {{ $batch->academic_year }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex gap-3 lg:col-span-4">
                    <button class="flex-1 rounded-xl bg-surface-container-low px-5 py-3 text-sm font-semibold text-primary" type="submit">Filter</button>
                    <a class="rounded-xl bg-white px-5 py-3 text-sm font-semibold text-on-surface-variant shadow-sm" href="{{ route('payments.index', $paymentFilters) }}">Reset</a>
                </div>
            </form>

            <form action="{{ route('payments.create') }}" class="mt-4 grid grid-cols-1 gap-3 lg:grid-cols-12" method="GET">
                @foreach ($currentFilters as $filterName => $filterValue)
                    <input name="{{ $filterName }}" type="hidden" value="{{ $filterValue }}">
                @endforeach

                <select class="rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm lg:col-span-9" name="student_id" required>
                    <option value="">Pilih siswa aktif</option>
                    @foreach ($students as $student)
                        <option value="{{ $student->id }}">
                            {{ $student->full_name }} &bull; {{ $student->nis ?: $student->nisn ?: '-' }} &bull; {{ $student->classRoom?->name ?? '-' }} &bull; {{ $student->batch?->academic_year ?? '-' }}
                        </option>
                    @endforeach
                </select>

                <button class="rounded-xl bg-primary px-5 py-3.5 text-base font-bold text-white shadow-sm transition-colors hover:bg-primary-container focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 lg:col-span-3" type="submit">
                    Muat Tagihan
                </button>
            </form>
    </div>

    <div class="bg-surface-container-lowest rounded-xl shadow-sm overflow-hidden">
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

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-surface-container-low">
                <tr>
                    <th class="px-8 py-4 text-xs uppercase tracking-widest text-slate-500">No Bukti</th>
                    <th class="px-8 py-4 text-xs uppercase tracking-widest text-slate-500">Tanggal</th>
                    <th class="px-8 py-4 text-xs uppercase tracking-widest text-slate-500">Siswa</th>
                    <th class="px-8 py-4 text-xs uppercase tracking-widest text-slate-500">Metode</th>
                    <th class="px-8 py-4 text-xs uppercase tracking-widest text-slate-500">Akun</th>
                    <th class="px-8 py-4 text-xs uppercase tracking-widest text-slate-500">Status</th>
                    <th class="px-8 py-4 text-xs uppercase tracking-widest text-slate-500">Total</th>
                    <th class="px-8 py-4 text-xs uppercase tracking-widest text-slate-500">Aksi</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-surface-container">
                @forelse ($payments as $payment)
                    <tr>
                        <td class="px-8 py-4 text-sm font-semibold">{{ $payment->payment_no }}</td>
                        <td class="px-8 py-4 text-sm">{{ $payment->payment_date?->format('d/m/Y') }}</td>
                        <td class="px-8 py-4 text-sm">{{ $payment->student->full_name }}</td>
                        <td class="px-8 py-4 text-sm">{{ $payment->method === 'bank_transfer' ? 'Transfer Manual' : 'Tunai' }}</td>
                        <td class="px-8 py-4 text-sm">{{ $payment->cashAccount->name }}</td>
                        <td class="px-8 py-4 text-sm">
                            <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $payment->status === 'edited' ? 'bg-amber-100 text-amber-900' : 'bg-emerald-100 text-emerald-900' }}">
                                {{ $paymentStatusLabels[$payment->status] ?? strtoupper($payment->status) }}
                            </span>
                        </td>
                        <td class="px-8 py-4 text-sm font-semibold">Rp {{ number_format($payment->total_amount, 0, ',', '.') }}</td>
                        <td class="px-8 py-4 text-sm">
                            <div class="flex flex-wrap gap-3">
                                @if ($payment->payment_proof_url)
                                    <a class="inline-flex items-center rounded-lg bg-emerald-50 px-3 py-2 font-semibold text-emerald-800 transition-colors hover:bg-emerald-100" href="{{ $payment->payment_proof_url }}" rel="noreferrer" target="_blank">
                                        Bukti Transfer
                                    </a>
                                @endif
                                <a class="inline-flex items-center rounded-lg bg-slate-100 px-3 py-2 font-semibold text-slate-700 transition-colors hover:bg-slate-200" href="{{ route('payments.receipt', $payment) }}" target="_blank">
                                    Cetak Kwitansi
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-8 py-6 text-sm text-on-surface-variant" colspan="8">Tidak ada pembayaran yang cocok dengan filter.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
