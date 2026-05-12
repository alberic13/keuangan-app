@php
    $studentTypeLabels = [
        'regular' => 'Reguler',
        'boarding' => 'Asrama',
    ];
@endphp

<div class="space-y-8">
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <h3 class="text-lg font-headline font-bold">Form Pembayaran</h3>
            <p class="text-sm text-on-surface-variant">Pilih invoice yang mau dibayar dan isi nominal per invoice.</p>
        </div>
        <a class="inline-flex items-center justify-center rounded-xl bg-surface-container-low px-5 py-3 text-sm font-semibold text-primary" href="{{ route('payments.index', request()->only(['student_search', 'student_class_id', 'student_batch_id', 'payment_search', 'payment_student_id', 'payment_method', 'payment_status', 'payment_date_from', 'payment_date_to'])) }}">
            Kembali Pilih Siswa
        </a>
    </div>

    @if ($selectedStudent)
        <form action="{{ route('payments.store') }}" class="space-y-6" enctype="multipart/form-data" method="POST">
            @csrf

            <input name="student_id" type="hidden" value="{{ $selectedStudent->id }}">
            @foreach (request()->only(['student_search', 'student_class_id', 'student_batch_id', 'payment_search', 'payment_student_id', 'payment_method', 'payment_status', 'payment_date_from', 'payment_date_to']) as $filterName => $filterValue)
                @if (filled($filterValue))
                    <input name="{{ $filterName }}" type="hidden" value="{{ $filterValue }}">
                @endif
            @endforeach

            <div class="rounded-xl border border-surface-container bg-surface-container-lowest p-6 shadow-sm">
                <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-[0.24em] text-on-surface-variant">Siswa Pembayar</p>
                        <h4 class="mt-2 text-2xl font-headline font-bold text-on-surface">{{ $selectedStudent->full_name }}</h4>
                        <p class="mt-1 text-sm text-on-surface-variant">
                            {{ $selectedStudent->nis ?: '-' }} / {{ $selectedStudent->nisn ?: '-' }}
                        </p>
                    </div>
                    <span class="rounded-full bg-secondary-container px-3 py-1 text-xs font-semibold text-on-surface">
                        {{ $invoiceOptions->count() }} tagihan tersedia
                    </span>
                </div>

                <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-4 text-sm">
                    <div class="rounded-xl bg-surface-container-low px-4 py-3">
                        <p class="text-xs uppercase tracking-widest text-on-surface-variant">Kelas</p>
                        <p class="mt-1 font-semibold text-on-surface">{{ $selectedStudent->classRoom->name ?? '-' }}</p>
                    </div>
                    <div class="rounded-xl bg-surface-container-low px-4 py-3">
                        <p class="text-xs uppercase tracking-widest text-on-surface-variant">Jurusan</p>
                        <p class="mt-1 font-semibold text-on-surface">{{ $selectedStudent->major->name ?? '-' }}</p>
                    </div>
                    <div class="rounded-xl bg-surface-container-low px-4 py-3">
                        <p class="text-xs uppercase tracking-widest text-on-surface-variant">Angkatan</p>
                        <p class="mt-1 font-semibold text-on-surface">{{ $selectedStudent->batch->academic_year ?? '-' }}</p>
                    </div>
                    <div class="rounded-xl bg-surface-container-low px-4 py-3">
                        <p class="text-xs uppercase tracking-widest text-on-surface-variant">Tipe Siswa</p>
                        <p class="mt-1 font-semibold text-on-surface">{{ $studentTypeLabels[$selectedStudent->student_type] ?? ucfirst($selectedStudent->student_type) }}</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <input class="rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="payment_date" required type="date" value="{{ old('payment_date', now()->toDateString()) }}">

                <select class="rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="method" required>
                    <option @selected(old('method', 'cash') === 'cash') value="cash">Tunai</option>
                    <option @selected(old('method', 'cash') === 'bank_transfer') value="bank_transfer">Transfer Manual</option>
                </select>

                <select class="rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="cash_account_id" required>
                    <option value="">Pilih akun kas/bank</option>
                    @foreach ($accounts as $account)
                        <option @selected((string) old('cash_account_id') === (string) $account->id) value="{{ $account->id }}">
                            {{ $account->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="rounded-xl border border-surface-container bg-surface-container-low px-4 py-4">
                <label class="block text-sm font-semibold text-on-surface" for="payment_proof">Upload Bukti Pembayaran</label>
                <p class="mt-1 text-xs text-on-surface-variant">Format yang didukung: PDF, JPG, JPEG, PNG, atau WEBP. Wajib untuk metode transfer manual.</p>
                <input accept=".pdf,.jpg,.jpeg,.png,.webp" class="mt-3 block w-full rounded-xl border-none bg-white px-4 py-3 text-sm" id="payment_proof" name="payment_proof" type="file">
            </div>

            <textarea class="w-full rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="notes" placeholder="Catatan transaksi" rows="3">{{ old('notes') }}</textarea>

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
        </form>
    @else
        <div class="rounded-xl bg-surface-container-lowest p-6 shadow-sm">
            <p class="text-sm text-on-surface-variant">Pilih siswa terlebih dahulu dari halaman pembayaran untuk memuat tagihan.</p>
        </div>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const syncRowState = (checkbox) => {
                const key = checkbox.dataset.key;
                const amountField = document.querySelector(`.payment-amount[data-key="${key}"]`);
                const invoiceField = checkbox.closest('tr').querySelector('.invoice-id-field');

                if (!amountField || !invoiceField) {
                    return;
                }

                amountField.disabled = !checkbox.checked;
                amountField.required = checkbox.checked;
                invoiceField.disabled = !checkbox.checked;
            };

            document.querySelectorAll('.payment-toggle').forEach((checkbox) => {
                syncRowState(checkbox);
                checkbox.addEventListener('change', () => syncRowState(checkbox));
            });
        });
    </script>
</div>
