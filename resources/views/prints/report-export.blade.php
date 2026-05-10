<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        @page { margin: 24px; }
        body { margin: 0; font-family: DejaVu Serif, serif; color: #1f2937; font-size: 10px; }
        .page { position: relative; min-height: 1020px; background: #ffffff; padding: 30px 34px 20px; }
        .watermark {
            position: absolute;
            top: 0;
            right: 0;
            width: 180px;
            height: 180px;
            opacity: 0.04;
            background-image: radial-gradient(circle at 2px 2px, #00422f 1px, transparent 0);
            background-size: 20px 20px;
        }
        .header-shell {
            width: 100%;
            border-bottom: 4px double #14532d;
            padding-bottom: 16px;
            margin-bottom: 22px;
        }
        .header-shell td { vertical-align: top; }
        .seal-wrap { width: 72px; }
        .seal {
            width: 58px;
            height: 58px;
            border: 3px solid #14532d;
            border-radius: 999px;
            text-align: center;
            line-height: 58px;
            font-family: DejaVu Sans, sans-serif;
            font-weight: bold;
            color: #14532d;
            font-size: 16px;
            margin-top: 2px;
        }
        .header-copy { text-align: center; }
        .header-copy .kemenag {
            font-family: DejaVu Sans, sans-serif;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.7px;
            color: #14532d;
            font-weight: bold;
        }
        .header-copy .school {
            margin-top: 2px;
            font-size: 20px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: #052e16;
        }
        .header-copy .address,
        .header-copy .meta-line {
            margin-top: 3px;
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #475569;
        }
        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 999px;
            background: #e7f3ec;
            color: #14532d;
            font-family: DejaVu Sans, sans-serif;
            font-size: 8px;
            font-weight: bold;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 6px;
        }
        .title-block { text-align: center; margin-bottom: 20px; }
        .title-block h1 {
            margin: 0;
            font-size: 15px;
            text-transform: uppercase;
            text-decoration: underline;
            text-underline-offset: 4px;
            color: #0f172a;
        }
        .title-block p {
            margin: 5px 0 0;
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            text-transform: uppercase;
            color: #475569;
            letter-spacing: 0.4px;
        }
        .chip-row { margin-bottom: 12px; }
        .chip {
            display: inline-block;
            margin: 0 6px 6px 0;
            padding: 5px 8px;
            border-radius: 999px;
            background: #f1f5f9;
            color: #334155;
            font-family: DejaVu Sans, sans-serif;
            font-size: 8px;
        }
        .summary-grid { width: 100%; margin-bottom: 14px; }
        .summary-grid td { width: 33.33%; padding-right: 8px; vertical-align: top; }
        .summary-card {
            border: 1px solid #d7e0db;
            background: #fbfdfb;
            padding: 10px 12px;
        }
        .summary-card .label {
            font-family: DejaVu Sans, sans-serif;
            font-size: 8px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }
        .summary-card .value {
            margin-top: 4px;
            font-family: DejaVu Sans, sans-serif;
            font-size: 13px;
            font-weight: bold;
            color: #052e16;
        }
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }
        .report-table th,
        .report-table td {
            border: 1px solid #cbd5cf;
            padding: 8px 9px;
            vertical-align: top;
        }
        .report-table th {
            background: #052e16;
            color: #ffffff;
            font-family: DejaVu Sans, sans-serif;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            text-align: left;
        }
        .report-table tbody td {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #1f2937;
        }
        .report-table .text-right { text-align: right; }
        .report-table .text-center { text-align: center; }
        .report-table .opening-row td {
            background: #f8faf8;
            font-style: italic;
            color: #64748b;
        }
        .report-table .total-row td {
            background: #eef8f1;
            font-weight: bold;
            color: #14532d;
        }
        .section-title {
            margin: 16px 0 8px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #14532d;
        }
        .empty {
            border: 1px dashed #cbd5e1;
            padding: 16px;
            text-align: center;
            font-family: DejaVu Sans, sans-serif;
            color: #64748b;
        }
        .signature-grid {
            width: 100%;
            margin-top: 30px;
        }
        .signature-grid td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
        }
        .signature-space { height: 62px; }
        .signature-line {
            width: 220px;
            margin: 0 auto;
            border-top: 1px solid #334155;
            padding-top: 5px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .footer {
            margin-top: 22px;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
            font-family: DejaVu Sans, sans-serif;
            font-size: 8px;
            color: #94a3b8;
        }
        .footer table { width: 100%; }
        .footer .center { text-align: center; }
        .footer .right { text-align: right; }
        .muted { color: #64748b; }
    </style>
</head>
<body>
    @php
        $filters = collect($filters ?? [])->filter(fn ($value, $key) => filled($value) && $key !== 'format');
        $generatedAt = $generatedAt ?? now();
        $ledgerTypes = ['bku', 'cash-book', 'cash-receipt-book', 'bank-receipt-book', 'cash-bank-receipt-book', 'daily-cash'];
        $isLedgerType = in_array($type ?? null, $ledgerTypes, true);
        $isStudentLedger = ($type ?? null) === 'student-ledger' && is_array($rows ?? null) && isset($rows['student']);
        $isArrears = ($type ?? null) === 'arrears';
        $currencyColumns = ['income', 'expense', 'debit', 'credit', 'balance', 'amount', 'total_amount', 'paid_amount', 'outstanding_amount', 'outstanding', 'nominal', 'uang_masuk', 'uang_keluar'];
        $formatCurrency = fn ($value) => 'Rp '.number_format((int) $value, 0, ',', '.');
        $formatHeading = fn ($value) => str((string) $value)->replace('_', ' ')->title();
        $formatValue = function ($key, $value) use ($currencyColumns, $formatCurrency) {
            if (is_array($value)) {
                return json_encode($value, JSON_UNESCAPED_UNICODE);
            }

            if (is_object($value) && method_exists($value, 'toArray')) {
                return json_encode($value->toArray(), JSON_UNESCAPED_UNICODE);
            }

            if ($value === null || $value === '') {
                return '-';
            }

            if (in_array((string) $key, $currencyColumns, true) && is_numeric($value)) {
                return $formatCurrency($value);
            }

            return (string) $value;
        };
        $flatRows = collect(is_iterable($rows ?? null) ? $rows : [])
            ->map(function ($row) {
                if (is_array($row)) {
                    return $row;
                }

                if (is_object($row) && method_exists($row, 'toArray')) {
                    return $row->toArray();
                }

                return ['data' => $row];
            })
            ->values();
        $ledgerRows = $isLedgerType ? collect($rows ?? []) : collect();
        $transactionRows = $ledgerRows->reject(fn ($row) => $row['is_opening_balance'] ?? false)->values();
        $ledgerDebit = (int) $transactionRows->sum('debit');
        $ledgerCredit = (int) $transactionRows->sum('credit');
        $ledgerBalance = (int) ($ledgerRows->last()['balance'] ?? 0);
        $isCashflow = ($type ?? null) === 'cashflow';
        $cashflowRows = $isCashflow ? $flatRows : collect();
        $cashflowIncome = (int) $cashflowRows->sum('uang_masuk');
        $cashflowExpense = (int) $cashflowRows->sum('uang_keluar');
        $ledgerDates = $transactionRows->pluck('date')->filter()->values();
        $periodStart = $filters->get('date_from')
            ? \Carbon\Carbon::parse($filters->get('date_from'))
            : ($ledgerDates->isNotEmpty() ? \Carbon\Carbon::parse($ledgerDates->first()) : null);
        $periodEnd = $filters->get('date_to')
            ? \Carbon\Carbon::parse($filters->get('date_to'))
            : ($ledgerDates->isNotEmpty() ? \Carbon\Carbon::parse($ledgerDates->last()) : null);
        $periodLabel = $periodStart && $periodEnd
            ? sprintf('Periode: %s - %s', $periodStart->translatedFormat('d F Y'), $periodEnd->translatedFormat('d F Y'))
            : 'Periode: Seluruh Data Tersedia';
        $typeCode = strtoupper(preg_replace('/[^A-Z0-9]+/', '-', strtoupper((string) ($type ?? 'DOC'))));
        $documentId = sprintf('M2S-%s-%s-%s', $typeCode, ($periodStart ?? $generatedAt)->format('Ym'), $generatedAt->format('His'));
        $sourceCode = function ($row) {
            if (! empty($row['entry_no'])) {
                return $row['entry_no'];
            }

            return strtoupper(str_replace('_', '-', (string) ($row['source_type'] ?? '-')));
        };
    @endphp

    <div class="page">
        <div class="watermark"></div>

        <table class="header-shell">
            <tr>
                <td class="seal-wrap">
                    <div class="seal">M2S</div>
                </td>
                <td class="header-copy">
                    <div class="kemenag">Kementerian Agama Republik Indonesia</div>
                    <div class="school">MAN 2 Surakarta</div>
                    <div class="address">Jl. Slamet Riyadi No. 441, Kota Surakarta, Jawa Tengah 57147</div>
                    <div class="meta-line">Telepon: (0271) 711xxx | Website: www.man2surakarta.sch.id</div>
                </td>
                <td class="seal-wrap"></td>
            </tr>
        </table>

        <div class="title-block">
            <span class="badge">Official Document</span>
            <h1>{{ strtoupper($title) }}</h1>
            <p>{{ $periodLabel }}</p>
        </div>

        @if ($filters->isNotEmpty())
            <div class="chip-row">
                @foreach ($filters as $key => $value)
                    <span class="chip">{{ $formatHeading($key) }}: {{ is_scalar($value) ? $value : json_encode($value, JSON_UNESCAPED_UNICODE) }}</span>
                @endforeach
            </div>
        @endif

        @if ($isStudentLedger)
            <p class="section-title">Profil Siswa</p>
            <table class="report-table">
                <tbody>
                    <tr>
                        <td style="width: 18%;"><strong>Nama</strong></td>
                        <td style="width: 32%;">{{ $rows['student']->full_name }}</td>
                        <td style="width: 18%;"><strong>NIS / NISN</strong></td>
                        <td style="width: 32%;">{{ $rows['student']->nis ?: '-' }} / {{ $rows['student']->nisn ?: '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Kelas</strong></td>
                        <td>{{ $rows['student']->classRoom->name ?? '-' }}</td>
                        <td><strong>Jurusan</strong></td>
                        <td>{{ $rows['student']->major->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Angkatan</strong></td>
                        <td>{{ $rows['student']->batch->academic_year ?? '-' }}</td>
                        <td><strong>Tipe</strong></td>
                        <td>{{ ucfirst($rows['student']->student_type) }}</td>
                    </tr>
                </tbody>
            </table>

            <p class="section-title">Invoice Siswa</p>
            @if ($rows['invoices']->isEmpty())
                <div class="empty">Belum ada invoice untuk siswa ini.</div>
            @else
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Jenis Biaya</th>
                            <th>Periode</th>
                            <th>Status</th>
                            <th class="text-right">Nominal</th>
                            <th class="text-right">Sisa Tagihan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows['invoices'] as $invoice)
                            <tr>
                                <td>{{ $invoice->invoice_no }}</td>
                                <td>{{ $invoice->feeType->name }}</td>
                                <td>{{ $invoice->billingCycle?->period_label ?? '-' }}</td>
                                <td>{{ strtoupper($invoice->status) }}</td>
                                <td class="text-right">{{ $formatCurrency($invoice->total_amount) }}</td>
                                <td class="text-right">{{ $formatCurrency($invoice->outstanding_amount) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            <p class="section-title">Riwayat Pembayaran</p>
            @if ($rows['payments']->isEmpty())
                <div class="empty">Belum ada pembayaran untuk siswa ini.</div>
            @else
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>No Bukti</th>
                            <th>Tanggal</th>
                            <th>Metode</th>
                            <th>Akun</th>
                            <th>Alokasi Invoice</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows['payments'] as $payment)
                            <tr>
                                <td>{{ $payment->payment_no }}</td>
                                <td>{{ $payment->payment_date?->format('d/m/Y') }}</td>
                                <td>{{ $payment->method === 'bank_transfer' ? 'Transfer Manual' : 'Tunai' }}</td>
                                <td>{{ $payment->cashAccount->name }}</td>
                                <td>{{ $payment->items->map(fn ($item) => $item->invoice?->invoice_no)->filter()->join(', ') ?: '-' }}</td>
                                <td class="text-right">{{ $formatCurrency($payment->total_amount) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        @elseif ($isLedgerType)
            <table class="summary-grid">
                <tr>
                    <td>
                        <div class="summary-card">
                            <div class="label">Total Debet</div>
                            <div class="value">{{ $formatCurrency($ledgerDebit) }}</div>
                        </div>
                    </td>
                    <td>
                        <div class="summary-card">
                            <div class="label">Total Kredit</div>
                            <div class="value">{{ $formatCurrency($ledgerCredit) }}</div>
                        </div>
                    </td>
                    <td>
                        <div class="summary-card">
                            <div class="label">Saldo Akhir</div>
                            <div class="value">{{ $formatCurrency($ledgerBalance) }}</div>
                        </div>
                    </td>
                </tr>
            </table>

            @if ($ledgerRows->isEmpty())
                <div class="empty">Belum ada transaksi untuk filter laporan ini.</div>
            @else
                <table class="report-table">
                    <thead>
                        <tr>
                            <th style="width: 11%;">Tgl</th>
                            <th style="width: 19%;">Kode</th>
                            <th style="width: 34%;">Uraian Transaksi</th>
                            <th class="text-right" style="width: 12%;">Debet (Rp)</th>
                            <th class="text-right" style="width: 12%;">Kredit (Rp)</th>
                            <th class="text-right" style="width: 12%;">Saldo (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($ledgerRows as $row)
                            @if ($row['is_opening_balance'] ?? false)
                                <tr class="opening-row">
                                    <td colspan="5">Saldo Awal Periode</td>
                                    <td class="text-right" style="font-style: normal; font-weight: bold; color: #0f172a;">
                                        {{ number_format((int) ($row['balance'] ?? 0), 0, ',', '.') }}
                                    </td>
                                </tr>
                            @else
                                <tr>
                                    <td>{{ ! empty($row['date']) ? \Carbon\Carbon::parse($row['date'])->format('d/m') : '-' }}</td>
                                    <td>{{ $sourceCode($row) }}</td>
                                    <td>{{ $row['description'] ?? '-' }}</td>
                                    <td class="text-right">{{ number_format((int) ($row['debit'] ?? 0), 0, ',', '.') }}</td>
                                    <td class="text-right">{{ number_format((int) ($row['credit'] ?? 0), 0, ',', '.') }}</td>
                                    <td class="text-right">{{ number_format((int) ($row['balance'] ?? 0), 0, ',', '.') }}</td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="total-row">
                            <td colspan="3" class="text-center">Jumlah Total Transaksi</td>
                            <td class="text-right">{{ number_format($ledgerDebit, 0, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($ledgerCredit, 0, ',', '.') }}</td>
                            <td class="text-right">{{ number_format($ledgerBalance, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            @endif

            @if (($type ?? null) === 'bku')
                <table class="signature-grid">
                    <tr>
                        <td>
                            <p>Mengetahui,<br>Kepala Madrasah</p>
                            <div class="signature-space"></div>
                            <div class="signature-line">&nbsp;</div>
                        </td>
                        <td>
                            <p>Surakarta, {{ ($periodEnd ?? $generatedAt)->translatedFormat('d F Y') }}<br>Bendahara Madrasah</p>
                            <div class="signature-space"></div>
                            <div class="signature-line">&nbsp;</div>
                        </td>
                    </tr>
                </table>
            @endif
        @elseif ($isArrears)
            @if (collect($rows ?? [])->isEmpty())
                <div class="empty">Tidak ada data tunggakan untuk filter ini.</div>
            @else
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Jenis Biaya</th>
                            <th>Periode</th>
                            <th>Status</th>
                            <th class="text-right">Sisa Tagihan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $invoice)
                            <tr>
                                <td>{{ $invoice->invoice_no }}</td>
                                <td>{{ $invoice->student->full_name }}</td>
                                <td>{{ $invoice->student->classRoom->name ?? '-' }}</td>
                                <td>{{ $invoice->feeType->name }}</td>
                                <td>{{ $invoice->billingCycle?->period_label ?? '-' }}</td>
                                <td>{{ strtoupper($invoice->status) }}</td>
                                <td class="text-right">{{ $formatCurrency($invoice->outstanding_amount) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        @elseif ($isCashflow)
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
        @elseif ($flatRows->isEmpty())
            <div class="empty">Tidak ada data untuk laporan ini.</div>
        @else
            @php $headers = array_keys($flatRows->first()); @endphp
            <table class="report-table">
                <thead>
                    <tr>
                        @foreach ($headers as $header)
                            <th>{{ $formatHeading($header) }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($flatRows as $row)
                        <tr>
                            @foreach ($headers as $header)
                                <td class="{{ in_array($header, $currencyColumns, true) ? 'text-right' : '' }}">
                                    {{ $formatValue($header, $row[$header] ?? null) }}
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <div class="footer">
            <table>
                <tr>
                    <td>Generated by E-Keuangan MAN 2 Surakarta on {{ $generatedAt->format('Y-m-d H:i:s') }}</td>
                    <td class="center">Document ID: {{ $documentId }}</td>
                    <td class="right">Page 1 of 1</td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>
