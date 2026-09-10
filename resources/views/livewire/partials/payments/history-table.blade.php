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
