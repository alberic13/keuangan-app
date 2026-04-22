<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Keuangan Excel</title>
    <style>
        body {
            font-family: Helvetica, Arial, sans-serif;
            font-size: 12px;
            color: #173126;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            border: 1px solid #b9c3bd;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #e7efe9;
            font-weight: bold;
        }

        .title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 6px;
        }

        .meta {
            margin-bottom: 14px;
        }

        .summary {
            margin: 14px 0 18px;
        }

        .summary td {
            width: 25%;
        }
    </style>
</head>
<body>
    <div class="title">Laporan Keuangan MAN 2</div>
    <div class="meta">
        Periode: {{ $filters['start_date'] }} s.d. {{ $filters['end_date'] }}
    </div>

    <table class="summary">
        <tr>
            <td><strong>Total Pemasukan</strong><br>Rp {{ number_format($summary['income'], 0, ',', '.') }}</td>
            <td><strong>Total Pengeluaran</strong><br>Rp {{ number_format($summary['expense'], 0, ',', '.') }}</td>
            <td><strong>Saldo Periode</strong><br>Rp {{ number_format($summary['balance'], 0, ',', '.') }}</td>
            <td><strong>Jumlah Baris</strong><br>{{ $summary['count'] }}</td>
        </tr>
    </table>

    <table>
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
                    <td>{{ $transaksi->tanggal?->format('d M Y') }}</td>
                    <td>{{ $transaksi->no_referensi }}</td>
                    <td>{{ $transaksi->deskripsi_kegiatan }}</td>
                    <td>{{ $transaksi->kategori?->nama_kategori ?? '-' }}</td>
                    <td>{{ ucfirst($transaksi->kategori?->tipe ?? '-') }}</td>
                    <td>Rp {{ number_format($transaksi->nominal, 0, ',', '.') }}</td>
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
