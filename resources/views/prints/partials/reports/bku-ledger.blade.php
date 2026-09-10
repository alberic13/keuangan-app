<table class="summary-grid">
    <tr>
        <td>
            <div class="summary-card">
                <div class="label">Total Debet</div>
                <div class="value">{{ $formatCurrency($ledgerDebit) }}</div>
            </div>
        </td>
        <td>
            <div class="summary-card">
                <div class="label">Total Kredit</div>
                <div class="value">{{ $formatCurrency($ledgerCredit) }}</div>
            </div>
        </td>
        <td>
            <div class="summary-card">
                <div class="label">Saldo Akhir</div>
                <div class="value">{{ $formatCurrency($ledgerBalance) }}</div>
            </div>
        </td>
    </tr>
</table>

@if ($ledgerRows->isEmpty())
    <div class="empty">Belum ada transaksi untuk filter laporan ini.</div>
@else
    <table class="report-table">
        <thead>
            <tr>
                <th style="width: 11%;">Tgl</th>
                <th style="width: 19%;">Kode</th>
                <th style="width: 34%;">Uraian Transaksi</th>
                <th class="text-right" style="width: 12%;">Debet (Rp)</th>
                <th class="text-right" style="width: 12%;">Kredit (Rp)</th>
                <th class="text-right" style="width: 12%;">Saldo (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($ledgerRows as $row)
                @if ($row['is_opening_balance'] ?? false)
                    <tr class="opening-row">
                        <td colspan="5">Saldo Awal Periode</td>
                        <td class="text-right" style="font-style: normal; font-weight: bold; color: #0f172a;">
                            {{ number_format((int) ($row['balance'] ?? 0), 0, ',', '.') }}
                        </td>
                    </tr>
                @else
                    <tr>
                        <td>{{ ! empty($row['date']) ? \Carbon\Carbon::parse($row['date'])->format('d/m') : '-' }}</td>
                        <td>{{ $sourceCode($row) }}</td>
                        <td>{{ $row['description'] ?? '-' }}</td>
                        <td class="text-right">{{ number_format((int) ($row['debit'] ?? 0), 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format((int) ($row['credit'] ?? 0), 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format((int) ($row['balance'] ?? 0), 0, ',', '.') }}</td>
                    </tr>
                @endif
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="3" class="text-center">Jumlah Total Transaksi</td>
                <td class="text-right">{{ number_format($ledgerDebit, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($ledgerCredit, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($ledgerBalance, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
@endif

@if (($type ?? null) === 'bku')
    <table class="signature-grid">
        <tr>
            <td>
                <p>Mengetahui,<br>Kepala Madrasah</p>
                <div class="signature-space"></div>
                <div class="signature-line">&nbsp;</div>
            </td>
            <td>
                <p>Surakarta, {{ ($periodEnd ?? $generatedAt)->translatedFormat('d F Y') }}<br>Bendahara Madrasah</p>
                <div class="signature-space"></div>
                <div class="signature-line">&nbsp;</div>
            </td>
        </tr>
    </table>
@endif
