<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Kwitansi {{ $payment->payment_no }}</title>
    @include('prints.partials.voucher-styles')
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
        <table style="width: 100%; margin-bottom: -4px;">
            <tr>
                <td style="text-align: right; font-size: 7.5px; font-weight: bold; font-family: DejaVu Sans, sans-serif;">
                    No : {{ $payment->payment_no }}
                </td>
            </tr>
        </table>

        @include('prints.partials.voucher-header')

        <div class="title-container">
            <h1 class="title-text">KWITANSI</h1>
        </div>

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
                                <td style="width: 10px; color: #4b5563;">-</td>
                                <td>{{ $itemLabel }}</td>
                            </tr>
                        @endforeach
                        @if ($payment->items->count() > $paymentFor->count())
                            <tr>
                                <td style="width: 10px; color: #4b5563;">-</td>
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
                        <div>Surakarta, {{ $payment->payment_date?->translatedFormat('d F Y') }}</div>
                        <div class="spacer"></div>
                        <div style="font-weight: bold;">( {{ auth()->user()->name }} )</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
