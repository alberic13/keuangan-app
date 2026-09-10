<p class="section-title">Profil Siswa</p>
<table class="report-table">
    <tbody>
        <tr>
            <td style="width: 18%;"><strong>Nama</strong></td>
            <td style="width: 32%;">{{ $rows['student']->full_name }}</td>
            <td style="width: 18%;"><strong>NIS / NISN</strong></td>
            <td style="width: 32%;">{{ $rows['student']->nis ?: '-' }} / {{ $rows['student']->nisn ?: '-' }}</td>
        </tr>
        <tr>
            <td><strong>Kelas</strong></td>
            <td colspan="3">{{ $rows['student']->classRoom->name ?? '-' }}</td>
        </tr>
        <tr>
            <td><strong>Angkatan</strong></td>
            <td>{{ $rows['student']->batch->academic_year ?? '-' }}</td>
            <td><strong>Tipe</strong></td>
            <td>{{ ucfirst($rows['student']->student_type) }}</td>
        </tr>
    </tbody>
</table>

<p class="section-title">Invoice Siswa</p>
@if ($rows['invoices']->isEmpty())
    <div class="empty">Belum ada invoice untuk siswa ini.</div>
@else
    <table class="report-table">
        <thead>
            <tr>
                <th>Invoice</th>
                <th>Jenis Biaya</th>
                <th>Periode</th>
                <th>Status</th>
                <th class="text-right">Nominal</th>
                <th class="text-right">Sisa Tagihan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows['invoices'] as $invoice)
                <tr>
                    <td>{{ $invoice->invoice_no }}</td>
                    <td>{{ $invoice->feeType->name }}</td>
                    <td>{{ $invoice->billingCycle?->period_label ?? '-' }}</td>
                    <td>{{ strtoupper($invoice->status) }}</td>
                    <td class="text-right">{{ $formatCurrency($invoice->total_amount) }}</td>
                    <td class="text-right">{{ $formatCurrency($invoice->outstanding_amount) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

<p class="section-title">Riwayat Pembayaran</p>
@if ($rows['payments']->isEmpty())
    <div class="empty">Belum ada pembayaran untuk siswa ini.</div>
@else
    <table class="report-table">
        <thead>
            <tr>
                <th>No Bukti</th>
                <th>Tanggal</th>
                <th>Metode</th>
                <th>Akun</th>
                <th>Alokasi Invoice</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows['payments'] as $payment)
                <tr>
                    <td>{{ $payment->payment_no }}</td>
                    <td>{{ $payment->payment_date?->format('d/m/Y') }}</td>
                    <td>{{ $payment->method === 'bank_transfer' ? 'Transfer Manual' : 'Tunai' }}</td>
                    <td>{{ $payment->cashAccount->name }}</td>
                    <td>{{ $payment->items->map(fn ($item) => $item->invoice?->invoice_no)->filter()->join(', ') ?: '-' }}</td>
                    <td class="text-right">{{ $formatCurrency($payment->total_amount) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
