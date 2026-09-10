@if (collect($rows ?? [])->isEmpty())
    <div class="empty">Tidak ada data tunggakan untuk filter ini.</div>
@else
    <table class="report-table">
        <thead>
            <tr>
                <th>Invoice</th>
                <th>Nama Siswa</th>
                <th>Kelas</th>
                <th>Jenis Biaya</th>
                <th>Periode</th>
                <th>Status</th>
                <th class="text-right">Sisa Tagihan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $invoice)
                <tr>
                    <td>{{ $invoice->invoice_no }}</td>
                    <td>{{ $invoice->student->full_name }}</td>
                    <td>{{ $invoice->student->classRoom->name ?? '-' }}</td>
                    <td>{{ $invoice->feeType->name }}</td>
                    <td>{{ $invoice->billingCycle?->period_label ?? '-' }}</td>
                    <td>{{ strtoupper($invoice->status) }}</td>
                    <td class="text-right">{{ $formatCurrency($invoice->outstanding_amount) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif
