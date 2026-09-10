<p class="section-title">Rincian Tagihan</p>
<table class="report-table">
    <thead>
        <tr>
            <th style="width: 28%;">Jenis Biaya</th>
            <th style="width: 20%;">Periode</th>
            <th style="width: 18%;">Jatuh Tempo</th>
            <th style="width: 16%;">Referensi</th>
            <th class="text-right" style="width: 18%;">Nominal</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>{{ $invoice->feeType->name }}</td>
            <td>{{ $invoice->billingCycle?->period_label ?? '-' }}</td>
            <td>{{ $invoice->billingCycle?->due_date?->format('d/m/Y') ?? '-' }}</td>
            <td>{{ $invoice->reference_name ?: '-' }}</td>
            <td class="text-right">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
        </tr>
    </tbody>
</table>

<table class="summary-grid">
    <tr>
        <td>
            <div class="summary-card">
                <div class="label">Total Tagihan</div>
                <div class="value">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</div>
            </div>
        </td>
        <td>
            <div class="summary-card">
                <div class="label">Sudah Dibayar</div>
                <div class="value">Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }}</div>
            </div>
        </td>
        <td>
            <div class="summary-card">
                <div class="label">Sisa Tagihan</div>
                <div class="value">Rp {{ number_format($invoice->outstanding_amount, 0, ',', '.') }}</div>
            </div>
        </td>
    </tr>
</table>
