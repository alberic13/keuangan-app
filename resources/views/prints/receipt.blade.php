<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Kwitansi {{ $payment->payment_no }}</title>
    <style>
        @page {
            size: 21cm 9cm;
            margin: 0.35cm 0.45cm;
        }

        body {
            margin: 0;
            color: #111827;
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            line-height: 1.2;
        }

        .receipt {
            border: 1px solid #111827;
            box-sizing: border-box;
            padding: 0.28cm 0.35cm;
            page-break-inside: avoid;
        }

        .header {
            width: 100%;
            border-bottom: 2px solid #064e3b;
            margin-bottom: 6px;
            padding-bottom: 5px;
        }

        .brand {
            font-size: 10px;
            font-weight: bold;
            color: #064e3b;
            text-transform: uppercase;
        }

        .school {
            margin-top: 1px;
            font-size: 9px;
            color: #374151;
        }

        .title {
            text-align: right;
        }

        .title h1 {
            margin: 0;
            font-size: 14px;
            letter-spacing: 1px;
        }

        .title p {
            margin: 2px 0 0;
            font-size: 8px;
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

        .meta,
        .items {
            width: 100%;
            border-collapse: collapse;
        }

        .meta td,
        .items td {
            padding: 1px 0;
            vertical-align: top;
        }

        .label {
            width: 78px;
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
            border: 1px solid #064e3b;
            padding: 7px 8px;
            text-align: center;
            background: #f7fbf8;
        }

        .amount-box .amount-label {
            font-size: 7px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #4b5563;
        }

        .amount-box .amount-value {
            margin-top: 4px;
            font-size: 15px;
            font-weight: bold;
            color: #064e3b;
        }

        .section-title {
            margin: 6px 0 3px;
            font-size: 8px;
            font-weight: bold;
            color: #064e3b;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }

        .footer {
            margin-top: 8px;
            text-align: right;
            font-size: 8px;
        }

        .footer .spacer {
            height: 14px;
        }
    </style>
</head>
<body>
    @php
        $paymentFor = $payment->items
            ->map(function ($item) {
                $name = $item->invoice->feeType->name ?? 'Pembayaran';
                $period = $item->invoice->billingCycle?->period_label;

                return $period ? $name.' '.$period : $name;
            })
            ->take(3)
            ->values();
    @endphp

    <div class="receipt">
        <table class="header">
            <tr>
                <td>
                    <div class="brand">E-Keuangan MAN 2 Surakarta</div>
                    <div class="school">Jl. Slamet Riyadi No. 441, Surakarta</div>
                </td>
                <td class="title">
                    <h1>KWITANSI</h1>
                    <p>{{ $payment->payment_no }}</p>
                </td>
            </tr>
        </table>

        <table class="content">
            <tr>
                <td class="left">
                    <table class="meta">
                        <tr>
                            <td class="label">Tanggal</td>
                            <td class="separator">:</td>
                            <td class="value">{{ $payment->payment_date?->translatedFormat('d F Y') }}</td>
                        </tr>
                        <tr>
                            <td class="label">Diterima dari</td>
                            <td class="separator">:</td>
                            <td class="value">{{ $payment->student->full_name }}</td>
                        </tr>
                        <tr>
                            <td class="label">NIS / NISN</td>
                            <td class="separator">:</td>
                            <td>{{ $payment->student->nis ?: '-' }} / {{ $payment->student->nisn ?: '-' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Kelas</td>
                            <td class="separator">:</td>
                            <td>{{ $payment->student->classRoom->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Metode</td>
                            <td class="separator">:</td>
                            <td>{{ $payment->method === 'bank_transfer' ? 'Transfer Manual' : 'Tunai' }}</td>
                        </tr>
                    </table>

                    <div class="section-title">Untuk Pembayaran</div>
                    <table class="items">
                        @foreach ($paymentFor as $itemLabel)
                            <tr>
                                <td style="width: 10px;">-</td>
                                <td>{{ $itemLabel }}</td>
                            </tr>
                        @endforeach
                        @if ($payment->items->count() > $paymentFor->count())
                            <tr>
                                <td style="width: 10px;">-</td>
                                <td>dan {{ $payment->items->count() - $paymentFor->count() }} item lainnya</td>
                            </tr>
                        @endif
                    </table>
                </td>
                <td class="right">
                    <div class="amount-box">
                        <div class="amount-label">Jumlah Diterima</div>
                        <div class="amount-value">Rp {{ number_format($payment->total_amount, 0, ',', '.') }}</div>
                    </div>

                    <div class="footer">
                        <div>Surakarta, {{ $payment->payment_date?->format('d/m/Y') }}</div>
                        <div class="spacer"></div>
                        <div>Petugas</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
