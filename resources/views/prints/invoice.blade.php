@php
    $invoices = isset($invoices) ? collect($invoices) : collect([$invoice]);
    $titleInvoice = $invoices->first();
@endphp

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Tagihan {{ $titleInvoice?->invoice_no ?? 'Invoice' }}</title>
    <style>
        @page { margin: 24px; }
        body { margin: 0; font-family: DejaVu Serif, serif; color: #1f2937; font-size: 10px; }
        .page { position: relative; background: #ffffff; padding: 30px 34px 20px; }
        .watermark {
            position: absolute;
            top: 0;
            right: 0;
            width: 170px;
            height: 170px;
            opacity: 0.04;
            background-image: radial-gradient(circle at 2px 2px, #00422f 1px, transparent 0);
            background-size: 20px 20px;
        }
        .header-shell {
            width: 100%;
            border-bottom: 4px double #14532d;
            padding-bottom: 16px;
            margin-bottom: 20px;
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
            color: #052e16;
        }
        .header-copy .address,
        .header-copy .meta-line {
            margin-top: 3px;
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #475569;
        }
        .title-block { text-align: center; margin-bottom: 18px; }
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
        .title-block h1 {
            margin: 0;
            font-size: 15px;
            text-transform: uppercase;
            text-decoration: underline;
            text-underline-offset: 4px;
        }
        .title-block p {
            margin: 5px 0 0;
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #475569;
            text-transform: uppercase;
        }
        .meta-grid { width: 100%; margin-bottom: 14px; }
        .meta-grid td { width: 50%; vertical-align: top; padding-right: 8px; }
        .meta-card {
            border: 1px solid #d7e0db;
            background: #fbfdfb;
            padding: 12px 14px;
            min-height: 94px;
        }
        .meta-label {
            font-family: DejaVu Sans, sans-serif;
            font-size: 8px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }
        .meta-value {
            margin-top: 4px;
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            font-weight: bold;
            color: #0f172a;
        }
        .pill {
            display: inline-block;
            padding: 4px 9px;
            border-radius: 999px;
            font-family: DejaVu Sans, sans-serif;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }
        .pill-unpaid { background: #fee2e2; color: #991b1b; }
        .pill-partial { background: #fef3c7; color: #92400e; }
        .pill-paid { background: #dcfce7; color: #166534; }
        .pill-void { background: #e2e8f0; color: #334155; }
        .section-title {
            margin: 14px 0 8px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #14532d;
        }
        .report-table {
            width: 100%;
            border-collapse: collapse;
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
        .report-table td {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
        }
        .text-right { text-align: right; }
        .summary-grid { width: 100%; margin-top: 12px; }
        .summary-grid td { width: 33.33%; padding-right: 8px; vertical-align: top; }
        .summary-card {
            border: 1px solid #d7e0db;
            background: #fbfdfb;
            padding: 12px 14px;
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
            font-size: 14px;
            font-weight: bold;
            color: #14532d;
        }
        .notes {
            margin-top: 12px;
            border: 1px solid #d7e0db;
            background: #fcfdfc;
            padding: 12px 14px;
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
        }
        .footer {
            margin-top: 18px;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
            font-family: DejaVu Sans, sans-serif;
            font-size: 8px;
            color: #94a3b8;
        }
        .footer table { width: 100%; }
        .footer .center { text-align: center; }
        .footer .right { text-align: right; }
        .page + .page { page-break-before: always; }
    </style>
</head>
<body>
    @foreach ($invoices as $invoice)
    @php
        $statusClass = match ($invoice->status) {
            'paid' => 'pill-paid',
            'partial' => 'pill-partial',
            'void' => 'pill-void',
            default => 'pill-unpaid',
        };
        $statusLabel = match ($invoice->status) {
            'paid' => 'Lunas',
            'partial' => 'Sebagian',
            'void' => 'Dibatalkan',
            default => 'Belum Lunas',
        };
        $documentId = sprintf('M2S-INV-%s-%s', $invoice->billingCycle?->due_date?->format('Ym') ?? now()->format('Ym'), $invoice->id);
    @endphp

    <div class="page">
        <div class="watermark"></div>

        <table class="header-shell">
            <tr>
                <td class="seal-wrap"><div class="seal">M2S</div></td>
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
            <span class="badge">Tagihan Resmi</span>
            <h1>Tagihan Siswa</h1>
            <p>No Invoice: {{ $invoice->invoice_no }} | Periode: {{ $invoice->billingCycle?->period_label ?? '-' }}</p>
        </div>

        <table class="meta-grid">
            <tr>
                <td>
                    <div class="meta-card">
                        <div class="meta-label">Nama Siswa</div>
                        <div class="meta-value">{{ $invoice->student->full_name }}</div>
                        <div class="meta-label" style="margin-top: 8px;">NIS / NISN</div>
                        <div class="meta-value">{{ $invoice->student->nis ?: '-' }} / {{ $invoice->student->nisn ?: '-' }}</div>
                    </div>
                </td>
                <td>
                    <div class="meta-card">
                        <div class="meta-label">Status Invoice</div>
                        <div class="meta-value"><span class="pill {{ $statusClass }}">{{ $statusLabel }}</span></div>
                        <div class="meta-label" style="margin-top: 8px;">Jatuh Tempo</div>
                        <div class="meta-value">{{ $invoice->billingCycle?->due_date?->translatedFormat('d F Y') ?? '-' }}</div>
                    </div>
                </td>
            </tr>
        </table>

        <p class="section-title">Identitas Siswa</p>
        <table class="report-table">
            <tbody>
                <tr>
                    <td style="width: 18%;"><strong>Kelas</strong></td>
                    <td style="width: 32%;">{{ $invoice->student->classRoom->name ?? '-' }}</td>
                    <td style="width: 18%;"><strong>Jurusan</strong></td>
                    <td style="width: 32%;">{{ $invoice->student->major->name ?? '-' }}</td>
                </tr>
                <tr>
                    <td><strong>Angkatan</strong></td>
                    <td>{{ $invoice->student->batch->academic_year ?? '-' }}</td>
                    <td><strong>Tipe Siswa</strong></td>
                    <td>{{ ucfirst($invoice->student->student_type) }}</td>
                </tr>
            </tbody>
        </table>

        <p class="section-title">Rincian Tagihan</p>
        <table class="report-table">
            <thead>
                <tr>
                    <th style="width: 28%;">Jenis Biaya</th>
                    <th style="width: 20%;">Periode</th>
                    <th style="width: 18%;">Jatuh Tempo</th>
                    <th style="width: 16%;">Referensi</th>
                    <th class="text-right" style="width: 18%;">Nominal</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $invoice->feeType->name }}</td>
                    <td>{{ $invoice->billingCycle?->period_label ?? '-' }}</td>
                    <td>{{ $invoice->billingCycle?->due_date?->format('d/m/Y') ?? '-' }}</td>
                    <td>{{ $invoice->reference_name ?: '-' }}</td>
                    <td class="text-right">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <table class="summary-grid">
            <tr>
                <td>
                    <div class="summary-card">
                        <div class="label">Total Tagihan</div>
                        <div class="value">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</div>
                    </div>
                </td>
                <td>
                    <div class="summary-card">
                        <div class="label">Sudah Dibayar</div>
                        <div class="value">Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }}</div>
                    </div>
                </td>
                <td>
                    <div class="summary-card">
                        <div class="label">Sisa Tagihan</div>
                        <div class="value">Rp {{ number_format($invoice->outstanding_amount, 0, ',', '.') }}</div>
                    </div>
                </td>
            </tr>
        </table>

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

        <div class="footer">
            <table>
                <tr>
                    <td>Generated by E-Keuangan MAN 2 Surakarta on {{ now()->format('Y-m-d H:i:s') }}</td>
                    <td class="center">Document ID: {{ $documentId }}</td>
                    <td class="right">Page {{ $loop->iteration }} of {{ $loop->count }}</td>
                </tr>
            </table>
        </div>
    </div>
    @endforeach
</body>
</html>
