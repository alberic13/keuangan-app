@if ($invoice->paymentItems->isNotEmpty())
    <p class="section-title">Riwayat Pembayaran</p>
    <table class="report-table">
        <thead>
            <tr>
                <th>No Bukti</th>
                <th>Tanggal</th>
                <th>Metode</th>
                <th class="text-right">Nominal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->paymentItems as $item)
                <tr>
                    <td>{{ $item->payment?->payment_no ?? '-' }}</td>
                    <td>{{ $item->payment?->payment_date?->format('d/m/Y') ?? '-' }}</td>
                    <td>{{ $item->payment?->method === 'bank_transfer' ? 'Transfer Manual' : 'Tunai' }}</td>
                    <td class="text-right">Rp {{ number_format($item->amount, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

<div class="notes">
    <strong>Petunjuk pembayaran:</strong> pembayaran dilakukan melalui petugas keuangan dengan menyebutkan nomor invoice ini.
    <br>
    <strong>Ketentuan:</strong> invoice yang tidak memperbolehkan cicilan wajib dibayar lunas per tagihan.
    <br>
    <strong>Keterangan tambahan:</strong> {{ $invoice->reference_name ?: 'Tidak ada keterangan tambahan.' }}
</div>
