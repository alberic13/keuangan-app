<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Keuangan PDF</title>
    <style>
        body {
            font-family: Helvetica, Arial, sans-serif;
            color: #173126;
            font-size: 12px;
        }

        h1, h2, h3, p {
            margin: 0;
        }

        .header {
            margin-bottom: 18px;
        }

        .header h1 {
            font-size: 22px;
            margin-bottom: 6px;
        }

        .meta {
            margin-top: 10px;
            color: #4f635b;
        }

        .summary {
            width: 100%;
            margin: 18px 0;
            border-collapse: collapse;
        }

        .summary td {
            width: 25%;
            border: 1px solid #d8d1c5;
            padding: 10px;
            vertical-align: top;
        }

        .summary-label {
            font-size: 10px;
            text-transform: uppercase;
            color: #677a71;
        }

        .summary-value {
            margin-top: 6px;
            font-size: 16px;
            font-weight: bold;
        }

        table.report {
            width: 100%;
            border-collapse: collapse;
        }

        table.report th,
        table.report td {
            border: 1px solid #d8d1c5;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }

        table.report th {
            background: #f2ece2;
            font-size: 11px;
            text-transform: uppercase;
        }

        .amount-income {
            color: #2f8f5d;
            font-weight: bold;
        }

        .amount-expense {
            color: #b9473a;
            font-weight: bold;
        }

        .footer-note {
            margin-top: 14px;
            color: #677a71;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Keuangan MAN 2</h1>
        <p>Rekap transaksi sesuai filter yang sedang aktif pada sistem.</p>
        <div class="meta">
            {{ $filterDescription }} |
            Dicetak: {{ now()->format('d-m-Y H:i') }}
        </div>
    </div>

    <table class="summary">
        <tr>
            <td>
                <div class="summary-label">Total Pemasukan</div>
                <div class="summary-value">Rp {{ number_format($summary['income'], 0, ',', '.') }}</div>
            </td>
            <td>
                <div class="summary-label">Total Pengeluaran</div>
                <div class="summary-value">Rp {{ number_format($summary['expense'], 0, ',', '.') }}</div>
            </td>
            <td>
                <div class="summary-label">Saldo Periode</div>
                <div class="summary-value">Rp {{ number_format($summary['balance'], 0, ',', '.') }}</div>
            </td>
            <td>
                <div class="summary-label">Jumlah Baris</div>
                <div class="summary-value">{{ $summary['count'] }}</div>
            </td>
        </tr>
    </table>

    <table class="report">
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
                    <td class="{{ optional($transaksi->kategori)->tipe === 'pemasukan' ? 'amount-income' : 'amount-expense' }}">
                        Rp {{ number_format($transaksi->nominal, 0, ',', '.') }}
                    </td>
                    <td>{{ $transaksi->submitter?->name ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">Belum ada data transaksi untuk filter yang dipilih.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer-note">
        Dokumen ini dihasilkan otomatis dari sistem keuangan.
    </div>
</body>
</html>
