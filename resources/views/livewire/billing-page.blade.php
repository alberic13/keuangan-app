@php
    $cycleStatusLabels = [
        'open' => 'Terbuka',
        'closed' => 'Tertutup',
    ];
    $invoiceStatusLabels = [
        'unpaid' => 'Belum Lunas',
        'partial' => 'Sebagian',
        'paid' => 'Lunas',
        'void' => 'Dibatalkan',
    ];
@endphp

<div class="space-y-8">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
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

        <div class="lg:col-span-8 bg-surface-container-lowest rounded-xl shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-headline font-bold">Generate Tagihan</h3>
                    <p class="text-sm text-on-surface-variant">Gunakan jenis biaya dan periode aktif untuk membuat invoice massal.</p>
                </div>
                <a class="text-sm font-semibold text-primary" href="{{ route('fees.index') }}">Kelola tarif</a>
            </div>
            <form action="{{ route('billing.generate') }}" class="grid grid-cols-1 md:grid-cols-2 gap-4" method="POST">
                @csrf
                <select class="rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="fee_type_id" required>
                    <option value="">Pilih jenis biaya</option>
                    @foreach ($feeTypes as $feeType)
                        <option value="{{ $feeType->id }}">{{ $feeType->name }}</option>
                    @endforeach
                </select>
                <select class="rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="billing_cycle_id" required>
                    <option value="">Pilih siklus tagihan</option>
                    @foreach ($cycles as $cycle)
                        <option value="{{ $cycle->id }}">{{ $cycle->period_label }} • {{ $cycleStatusLabels[$cycle->status] ?? strtoupper($cycle->status) }}</option>
                    @endforeach
                </select>
                <select class="rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="filters[batch_id]">
                    <option value="">Semua angkatan</option>
                    @foreach ($batches as $batch)
                        <option value="{{ $batch->id }}">{{ $batch->academic_year }}</option>
                    @endforeach
                </select>
                <select class="rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="filters[class_id]">
                    <option value="">Semua kelas</option>
                    @foreach ($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->name }}</option>
                    @endforeach
                </select>

                <select class="rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="filters[student_type]">
                    <option value="all">Semua tipe siswa</option>
                    @foreach ($studentTypes as $studentType)
                        <option value="{{ $studentType->slug }}">{{ $studentType->label }}</option>
                    @endforeach
                </select>
                <input class="md:col-span-2 rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="reference_name" placeholder="Referensi kegiatan, opsional untuk uang kegiatan" type="text">
                <button class="md:col-span-2 rounded-xl bg-primary text-white font-semibold px-5 py-3" type="submit">Buat Invoice</button>
            </form>
        </div>
    </div>

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
                                    <button class="font-semibold text-tertiary" type="submit">Tutup Siklus</button>
                                </form>
                            @else
                                <span class="text-on-surface-variant">Tertutup</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-surface-container-lowest rounded-xl shadow-sm overflow-hidden">
        <div class="px-8 py-6 border-b border-surface-container">
            <h3 class="text-lg font-headline font-bold">Daftar Invoice</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-surface-container-low">
                <tr>
                    <th class="px-8 py-4 text-xs uppercase tracking-widest text-slate-500">Invoice</th>
                    <th class="px-8 py-4 text-xs uppercase tracking-widest text-slate-500">Siswa</th>
                    <th class="px-8 py-4 text-xs uppercase tracking-widest text-slate-500">Jenis</th>
                    <th class="px-8 py-4 text-xs uppercase tracking-widest text-slate-500">Periode</th>
                    <th class="px-8 py-4 text-xs uppercase tracking-widest text-slate-500">Sisa Tagihan</th>
                    <th class="px-8 py-4 text-xs uppercase tracking-widest text-slate-500">Status</th>
                    <th class="px-8 py-4 text-xs uppercase tracking-widest text-slate-500">Aksi</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-surface-container">
                @forelse ($invoices as $invoice)
                    <tr>
                        <td class="px-8 py-4 text-sm font-semibold">{{ $invoice->invoice_no }}</td>
                        <td class="px-8 py-4 text-sm">
                            <p class="font-semibold">{{ $invoice->student->full_name }}</p>
                            <p class="text-xs text-on-surface-variant">{{ $invoice->student->batch->academic_year }}</p>
                        </td>
                        <td class="px-8 py-4 text-sm">
                            <p>{{ $invoice->feeType->name }}</p>
                            <p class="text-xs text-on-surface-variant">{{ $invoice->reference_name ?: '-' }}</p>
                        </td>
                        <td class="px-8 py-4 text-sm">{{ $invoice->billingCycle?->period_label ?? '-' }}</td>
                        <td class="px-8 py-4 text-sm font-semibold">Rp {{ number_format($invoice->outstanding_amount, 0, ',', '.') }}</td>
                        <td class="px-8 py-4 text-sm">
                            <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $invoice->status === 'paid' ? 'bg-emerald-100 text-emerald-900' : ($invoice->status === 'partial' ? 'bg-amber-100 text-amber-900' : ($invoice->status === 'void' ? 'bg-slate-200 text-slate-700' : 'bg-red-100 text-red-900')) }}">
                                {{ $invoiceStatusLabels[$invoice->status] ?? strtoupper($invoice->status) }}
                            </span>
                        </td>
                        <td class="px-8 py-4 text-sm">
                            <div class="flex flex-wrap gap-3">
                                <a class="inline-flex items-center rounded-lg bg-surface-container-low px-3 py-2 font-semibold text-primary transition-colors hover:bg-surface-container-high" href="{{ route('invoices.print', $invoice) }}" target="_blank">
                                    Cetak
                                </a>
                                @if ($invoice->status === 'unpaid')
                                    <form action="{{ route('invoices.void', $invoice) }}" method="POST">
                                        @csrf
                                        <button class="inline-flex items-center rounded-lg bg-red-100 px-3 py-2 font-semibold text-red-800 transition-colors hover:bg-red-200" type="submit">
                                            Void
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-8 py-6 text-sm text-on-surface-variant" colspan="7">Belum ada invoice.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
