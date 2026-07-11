<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Bukti Pengeluaran {{ $expense->expense_no }}</title>
    <style>
        @page {
            size: 21cm 10cm;
            margin: 0.3cm 0.4cm;
        }

        body {
            margin: 0;
            color: #111827;
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            line-height: 1.3;
        }

        .receipt {
            border: 1px solid #111827;
            box-sizing: border-box;
            padding: 0.25cm 0.35cm;
            page-break-inside: avoid;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 3px double #111827;
            margin-bottom: 6px;
            padding-bottom: 4px;
        }

        .brand-title {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .school-info {
            font-size: 7.5px;
            color: #374151;
            margin-top: 1px;
        }

        .title-container {
            text-align: center;
            margin-top: 4px;
            margin-bottom: 6px;
        }

        .title-text {
            margin: 0;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 1px solid #111827;
            display: inline-block;
            padding-bottom: 1px;
        }

        .content {
            width: 100%;
            border-collapse: collapse;
        }

        .content td {
            vertical-align: top;
        }

        .left {
            width: 64%;
            padding-right: 12px;
        }

        .right {
            width: 36%;
        }

        .meta {
            width: 100%;
            border-collapse: collapse;
        }

        .meta td {
            padding: 3px 0;
            vertical-align: top;
        }

        .label {
            width: 100px;
            white-space: nowrap;
            color: #4b5563;
        }

        .separator {
            width: 8px;
            text-align: center;
            color: #4b5563;
        }

        .value {
            font-weight: 600;
        }

        .amount-box {
            border: 1px solid #111827;
            padding: 8px 10px;
            text-align: center;
            background: #f9fafb;
            margin-bottom: 10px;
        }

        .amount-box .amount-label {
            font-size: 7px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #4b5563;
        }

        .amount-box .amount-value {
            margin-top: 3px;
            font-size: 15px;
            font-weight: bold;
            color: #111827;
        }

        .signatures {
            margin-top: 20px;
            width: 100%;
            border-collapse: collapse;
        }

        .signatures td {
            text-align: center;
            font-size: 8px;
            width: 50%;
        }

        .signatures .spacer {
            height: 32px;
        }
    </style>
</head>
<body>
    <div class="receipt">
        <!-- Top Number -->
        <table style="width: 100%; margin-bottom: -4px;">
            <tr>
                <td style="text-align: right; font-size: 7.5px; font-weight: bold; font-family: DejaVu Sans, sans-serif;">
                    No : {{ $expense->expense_no }}
                </td>
            </tr>
        </table>

        <!-- School Letterhead -->
        <table class="header-table">
            <tr>
                <!-- Logo -->
                <td style="width: 12%; text-align: left; vertical-align: middle; padding-bottom: 2px;">
                    <img src="{{ public_path('images/man2-logo.png') }}" style="height: 52px; width: auto;">
                </td>
                <!-- Text -->
                <td style="width: 88%; text-align: center; vertical-align: middle; padding-bottom: 2px; padding-right: 12%;">
                    <div class="brand-title">KOMITE MADRASAH</div>
                    <div class="brand-title" style="margin-top: 1px;">MADRASAH ALIYAH NEGERI 2 KOTA SURAKARTA</div>
                    <div class="school-info" style="margin-top: 2px; font-weight: 500;">Jl. Slamet Riyadi nomor 308 Kota Surakarta</div>
                    <div class="school-info">Telepon : (0271) 716387 &nbsp; Faksimili : (0271) 716387</div>
                    <div class="school-info">Website : www.man2ska.com &nbsp; Email : man2surakarta@kemenag.go.id</div>
                </td>
            </tr>
        </table>

        <!-- Title -->
        <div class="title-container">
            <h1 class="title-text">BUKTI PENGELUARAN KAS</h1>
        </div>

        <table class="content">
            <tr>
                <td class="left">
                    <table class="meta">
                        <tr>
                            <td class="label">Tanggal Transaksi</td>
                            <td class="separator">:</td>
                            <td class="value">{{ $expense->transaction_date?->translatedFormat('d F Y') }}</td>
                        </tr>
                        <tr>
                            <td class="label">Kategori Pengeluaran</td>
                            <td class="separator">:</td>
                            <td class="value">{{ $expense->category->name }}</td>
                        </tr>
                        <tr>
                            <td class="label">Sumber Dana / Kas</td>
                            <td class="separator">:</td>
                            <td class="value">{{ $expense->paymentAccount->name }}</td>
                        </tr>
                        <tr>
                            <td class="label">Uraian / Deskripsi</td>
                            <td class="separator">:</td>
                            <td class="value" style="font-weight: normal; text-align: justify;">{{ $expense->description }}</td>
                        </tr>
                    </table>
                </td>
                <td class="right">
                    <div class="amount-box">
                        <div class="amount-label">Jumlah Pengeluaran</div>
                        <div class="amount-value">Rp {{ number_format($expense->amount, 0, ',', '.') }}</div>
                    </div>

                    <table class="signatures">
                        <tr>
                            <td>
                                Mengetahui,<br>
                                Kepala Madrasah
                                <div class="spacer"></div>
                                ( .................................... )
                            </td>
                            <td>
                                Surakarta, {{ $expense->transaction_date?->translatedFormat('d F Y') }}<br>
                                Bendahara
                                <div class="spacer"></div>
                                ( {{ auth()->user()->name }} )
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
