<div class="rounded-xl border border-surface-container bg-surface-container-lowest overflow-hidden">
    <div class="border-b border-surface-container bg-surface-container-low px-4 py-3">
        <p class="text-sm font-semibold">Alokasi Invoice</p>
        <p class="text-xs text-on-surface-variant">Semua invoice aktif dimuat otomatis dan bisa Anda sesuaikan sebelum disimpan.</p>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left">
            <thead class="bg-surface-container-low">
            <tr>
                <th class="px-4 py-3 text-xs uppercase tracking-widest text-slate-500">Pilih</th>
                <th class="px-4 py-3 text-xs uppercase tracking-widest text-slate-500">Invoice</th>
                <th class="px-4 py-3 text-xs uppercase tracking-widest text-slate-500">Jenis</th>
                <th class="px-4 py-3 text-xs uppercase tracking-widest text-slate-500">Sisa Tagihan</th>
                <th class="px-4 py-3 text-xs uppercase tracking-widest text-slate-500">Nominal Bayar</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-surface-container">
            @forelse ($invoiceOptions as $invoice)
                @php
                    $itemKey = (string) $invoice->id;
                    $oldInvoiceId = old("items.$itemKey.invoice_id");
                    $checked = $oldInvoiceId !== null ? true : true;
                    $amountValue = old("items.$itemKey.amount", (int) $invoice->outstanding_amount);
                @endphp
                <tr>
                    <td class="px-4 py-3 text-sm align-top">
                        <input @checked($checked) class="payment-toggle h-4 w-4 rounded border-slate-300 text-primary focus:ring-primary" data-key="{{ $itemKey }}" type="checkbox">
                        <input class="invoice-id-field" name="items[{{ $itemKey }}][invoice_id]" type="hidden" value="{{ $invoice->id }}">
                    </td>
                    <td class="px-4 py-3 text-sm">
                        <p class="font-semibold">{{ $invoice->invoice_no }}</p>
                        <p class="text-xs text-on-surface-variant">
                            {{ $invoice->billingCycle?->period_label ?? '-' }}
                            @if ($invoice->billingCycle?->due_date)
                                &bull; Jatuh tempo {{ $invoice->billingCycle->due_date->format('d/m/Y') }}
                            @endif
                        </p>
                    </td>
                    <td class="px-4 py-3 text-sm">
                        <p>{{ $invoice->feeType->name }}</p>
                        <p class="text-xs text-on-surface-variant">{{ $invoice->reference_name ?: 'Tanpa referensi tambahan' }}</p>
                        <p class="mt-1 text-xs font-semibold {{ $invoice->feeType->installment_allowed ? 'text-emerald-700' : 'text-amber-700' }}">
                            {{ $invoice->feeType->installment_allowed ? 'Boleh parsial' : 'Harus dibayar penuh' }}
                        </p>
                    </td>
                    <td class="px-4 py-3 text-sm">
                        <p class="font-semibold">Rp {{ number_format($invoice->outstanding_amount, 0, ',', '.') }}</p>
                    </td>
                    <td class="px-4 py-3 text-sm">
                        <input class="payment-amount w-full rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" data-key="{{ $itemKey }}" max="{{ (int) $invoice->outstanding_amount }}" min="1" name="items[{{ $itemKey }}][amount]" required type="number" value="{{ $amountValue }}">
                    </td>
                </tr>
            @empty
                <tr>
                    <td class="px-4 py-5 text-sm text-on-surface-variant" colspan="5">Tidak ada invoice yang bisa diproses untuk siswa ini.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@if ($invoiceOptions->isNotEmpty())
    <button class="w-full rounded-xl bg-primary text-white font-semibold px-5 py-3" type="submit">
        Simpan Pembayaran
    </button>
@endif
