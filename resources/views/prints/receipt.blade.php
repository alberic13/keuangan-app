<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Kwitansi {{ $payment->payment_no }}</title>
    <style>
        @page {
            margin: 12px 14px;
        }

        body {
            margin: 0;
            color: #111827;
            font-family: DejaVu Sans, sans-serif;
            font-size: 8.5px;
            line-height: 1.25;
        }

        .receipt {
            border: 1px solid #111827;
            min-height: 252px;
            padding: 10px 12px;
        }

        .title {
            text-align: center;
            margin-bottom: 7px;
        }

        .title h1 {
            margin: 0;
            font-size: 13px;
            letter-spacing: 1px;
        }

        .title p {
            margin: 2px 0 0;
            font-size: 8.5px;
        }

        .meta {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 7px;
        }

        .meta td {
            padding: 1px 0;
            vertical-align: top;
        }

        .label {
            width: 78px;
            white-space: nowrap;
        }

        .separator {
            width: 10px;
            text-align: center;
        }

        .value {
            font-weight: 600;
        }

        .amount-box {
            border: 1px solid #111827;
            padding: 6px 10px;
            margin: 7px 0;
            text-align: center;
        }

        .amount-box .amount-label {
            font-size: 7.5px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .amount-box .amount-value {
            margin-top: 2px;
            font-size: 13px;
            font-weight: bold;
        }

        .detail-box {
            border-top: 1px dashed #6b7280;
            border-bottom: 1px dashed #6b7280;
            padding: 5px 0;
            margin: 6px 0 8px;
        }

        .detail-box p {
            margin: 0 0 3px;
        }

        .detail-box p:last-child {
            margin-bottom: 0;
        }

        .items {
            margin: 3px 0 0 12px;
            padding: 0;
        }

        .items li {
            margin-bottom: 1px;
        }

        .footer {
            margin-top: 6px;
            text-align: right;
            font-size: 8px;
        }

        .footer .spacer {
            height: 20px;
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
            ->take(5)
            ->values();
    @endphp

    <div class="receipt">
        <div class="title">
            <h1>KWITANSI</h1>
            <p>MAN 2 Surakarta</p>
        </div>

        <table class="meta">
            <tr>
                <td class="label">No. Bukti</td>
                <td class="separator">:</td>
                <td class="value">{{ $payment->payment_no }}</td>
            </tr>
            <tr>
                <td class="label">Tanggal</td>
                <td class="separator">:</td>
                <td class="value">{{ $payment->payment_date?->translatedFormat('d F Y') }}</td>
            </tr>
            <tr>
                <td class="label">Sudah terima dari</td>
                <td class="separator">:</td>
                <td class="value">{{ $payment->student->full_name }}</td>
            </tr>
            <tr>
                <td class="label">Kelas / Jurusan</td>
                <td class="separator">:</td>
                <td>{{ ($payment->student->classRoom->name ?? '-') . ' / ' . ($payment->student->major->name ?? '-') }}</td>
            </tr>
            <tr>
                <td class="label">Metode</td>
                <td class="separator">:</td>
                <td>{{ $payment->method === 'bank_transfer' ? 'Transfer Manual' : 'Tunai' }}</td>
            </tr>
        </table>

        <div class="amount-box">
            <div class="amount-label">Jumlah Diterima</div>
            <div class="amount-value">Rp {{ number_format($payment->total_amount, 0, ',', '.') }}</div>
        </div>

        <div class="detail-box">
            <p><strong>Untuk pembayaran:</strong></p>
            <ul class="items">
                @foreach ($paymentFor as $itemLabel)
                    <li>{{ $itemLabel }}</li>
                @endforeach
                @if ($payment->items->count() > $paymentFor->count())
                    <li>dan {{ $payment->items->count() - $paymentFor->count() }} item lainnya</li>
                @endif
            </ul>

            @if ($payment->notes)
                <p><strong>Catatan:</strong> {{ $payment->notes }}</p>
            @endif
        </div>

        <div class="footer">
            <div>Surakarta, {{ $payment->payment_date?->translatedFormat('d F Y') }}</div>
            <div class="spacer"></div>
            <div>Petugas</div>
        </div>
    </div>
</body>
</html>
