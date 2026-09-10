@if ($cashflowRows->isEmpty())
    <div class="empty">Tidak ada data untuk laporan ini.</div>
@else
    <table class="report-table">
        <thead>
            <tr>
                <th style="width: 14%;">Tanggal</th>
                <th style="width: 12%;">Jenis</th>
                <th style="width: 16%;">No Bukti</th>
                <th style="width: 16%;">Sumber</th>
                <th>Keterangan</th>
                <th class="text-right" style="width: 11%;">Uang Masuk</th>
                <th class="text-right" style="width: 11%;">Uang Keluar</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($cashflowRows as $row)
                <tr>
                    <td>{{ $row['tanggal'] ?? '-' }}</td>
                    <td>{{ $row['jenis'] ?? '-' }}</td>
                    <td>{{ $row['no_bukti'] ?? '-' }}</td>
                    <td>{{ $row['sumber'] ?? '-' }}</td>
                    <td>{{ $row['keterangan'] ?? '-' }}</td>
                    <td class="text-right">{{ number_format((int) ($row['uang_masuk'] ?? 0), 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format((int) ($row['uang_keluar'] ?? 0), 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="5" class="text-center">Total Periode</td>
                <td class="text-right">{{ number_format($cashflowIncome, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($cashflowExpense, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>
@endif
