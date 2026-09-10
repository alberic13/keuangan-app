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
