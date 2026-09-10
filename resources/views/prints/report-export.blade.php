<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    @include('prints.partials.report-styles')
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
            if (is_array($value)) return json_encode($value, JSON_UNESCAPED_UNICODE);
            if (is_object($value) && method_exists($value, 'toArray')) return json_encode($value->toArray(), JSON_UNESCAPED_UNICODE);
            if ($value === null || $value === '') return '-';
            if (in_array((string) $key, $currencyColumns, true) && is_numeric($value)) return $formatCurrency($value);
            return (string) $value;
        };
        $flatRows = collect(is_iterable($rows ?? null) ? $rows : [])
            ->map(fn ($row) => is_array($row) ? $row : (is_object($row) && method_exists($row, 'toArray') ? $row->toArray() : ['data' => $row]))
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
        $sourceCode = fn ($row) => ! empty($row['entry_no']) ? $row['entry_no'] : strtoupper(str_replace('_', '-', (string) ($row['source_type'] ?? '-')));
    @endphp

    <div class="page">
        <div class="watermark"></div>

        @include('prints.partials.letterhead')

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
            @include('prints.partials.reports.student-ledger')
        @elseif ($isLedgerType)
            @include('prints.partials.reports.bku-ledger')
        @elseif ($isArrears)
            @include('prints.partials.reports.arrears-table')
        @elseif ($isCashflow)
            @include('prints.partials.reports.cashflow-table')
        @else
            @include('prints.partials.reports.generic-table')
        @endif

        @include('prints.partials.document-footer')
    </div>
</body>
</html>
