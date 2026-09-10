<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Bukti Pengeluaran {{ $expense->expense_no }}</title>
    @include('prints.partials.voucher-styles')
</head>
<body>
    <div class="receipt">
        <table style="width: 100%; margin-bottom: -4px;">
            <tr>
                <td style="text-align: right; font-size: 7.5px; font-weight: bold; font-family: DejaVu Sans, sans-serif;">
                    No : {{ $expense->expense_no }}
                </td>
            </tr>
        </table>

        @include('prints.partials.voucher-header')

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
