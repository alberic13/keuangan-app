<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Keuangan Excel</title>
</head>
<body>
    <table border="1">
        <tr>
            <td colspan="7"><strong>Laporan Keuangan MAN 2</strong></td>
        </tr>
        <tr>
            <td colspan="7">{{ $filterDescription }}</td>
        </tr>
        <tr>
            <td colspan="7">Diekspor pada {{ now()->format('d-m-Y H:i') }}</td>
        </tr>
    </table>

    <table border="1" style="margin-top: 12px;">
        <tr>
            <th>Total Pemasukan</th>
            <th>Total Pengeluaran</th>
            <th>Saldo</th>
            <th>Jumlah Transaksi</th>
        </tr>
        <tr>
            <td>Rp {{ number_format($summary['income'], 0, ',', '.') }}</td>
            <td>Rp {{ number_format($summary['expense'], 0, ',', '.') }}</td>
            <td>Rp {{ number_format($summary['balance'], 0, ',', '.') }}</td>
            <td>{{ $summary['count'] }}</td>
        </tr>
    </table>

    <table border="1" style="margin-top: 12px;">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>No Referensi</th>
                <th>Keterangan</th>
                <th>Kategori</th>
                <th>Tipe</th>
                <th>Nominal</th>
                <th>Submitter</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $transaksi)
                <tr>
                    <td>{{ $transaksi->tanggal?->format('d-m-Y') }}</td>
                    <td>{{ $transaksi->no_referensi }}</td>
                    <td>{{ $transaksi->deskripsi_kegiatan }}</td>
                    <td>{{ $transaksi->kategori?->nama_kategori ?? '-' }}</td>
                    <td>{{ ucfirst($transaksi->kategori?->tipe ?? '-') }}</td>
                    <td>{{ number_format($transaksi->nominal, 0, ',', '.') }}</td>
                    <td>{{ $transaksi->submitter?->name ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">Belum ada data transaksi untuk filter yang dipilih.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
