<?php

namespace App\Services\Reports;

use App\Exports\RowsExport;
use App\Models\Student;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\Response;

class ReportExportService
{
    public function __construct(
        protected ReportService $reportService,
        protected ReportRowNormalizer $rowNormalizer,
    ) {
    }

    public function export(Request $request): Response
    {
        $type = $request->string('type')->toString();

        if ($type === 'cashflow') {
            [$dateFrom, $dateTo] = $this->resolvePeriodRange($request);
            $request->merge(['date_from' => $dateFrom, 'date_to' => $dateTo]);
        }

        $rows = $this->resolveRows($type, $request);

        $summary = $type === 'cashflow' && is_array($rows) ? [
            'income' => (int) collect($rows)->sum('uang_masuk'),
            'expense' => (int) collect($rows)->sum('uang_keluar'),
            'balance' => (int) collect($rows)->sum('uang_masuk') - (int) collect($rows)->sum('uang_keluar'),
            'count' => collect($rows)->count(),
        ] : null;

        if ($request->string('format')->toString() === 'xlsx') {
            $exportRows = $this->rowNormalizer->normalizeForSpreadsheet($type, $rows) ?: [['data' => 'Tidak ada data']];

            return Excel::download(new RowsExport($exportRows), Str::slug($type ?: 'report').'.xlsx');
        }

        return Pdf::loadView('prints.report-export', [
            'type' => $type,
            'title' => $this->resolveTitle($type),
            'rows' => $rows,
            'summary' => $summary,
            'filters' => $request->except(['format']),
            'generatedAt' => now(),
        ])->download(Str::slug($type ?: 'report').'.pdf');
    }

    protected function resolveRows(string $type, Request $request): mixed
    {
        return match ($type) {
            'cashflow' => $this->rowNormalizer->normalizeCashflow($request->only(['date_from', 'date_to'])),
            'daily-cash' => $this->reportService->dailyCash($request->string('date', now()->toDateString())->toString()),
            'monthly-summary' => $this->reportService->monthlySummary($request->integer('year', now()->year)),
            'yearly-summary' => $this->reportService->yearlySummary(now()->year - 2, now()->year),
            'student-ledger' => $request->filled('student_id')
                ? $this->reportService->studentLedger(Student::query()->findOrFail($request->integer('student_id')))
                : null,
            'arrears' => $this->reportService->arrears($request->only(['batch_id', 'class_id'])),
            'bku' => $this->reportService->bku($request->only(['account_id', 'direction', 'date_from', 'date_to', 'source_type'])),
            'cash-book' => $this->reportService->cashBook($request->only(['account_id', 'date_from', 'date_to'])),
            'cash-receipt-book' => $this->reportService->cashReceiptBook($request->only(['account_id', 'date_from', 'date_to'])),
            'bank-receipt-book' => $this->reportService->bankReceiptBook($request->only(['account_id', 'date_from', 'date_to'])),
            'cash-bank-receipt-book' => $this->reportService->cashBankReceiptBook($request->only(['account_id', 'date_from', 'date_to'])),
            default => [],
        };
    }

    protected function resolveTitle(string $type): string
    {
        return match ($type) {
            'cashflow' => 'Laporan Uang Masuk & Keluar',
            'bku' => 'Laporan Buku Kas Umum (BKU)',
            'cash-book' => 'Laporan Buku Kas Tunai',
            'cash-receipt-book' => 'Laporan Buku Pembantu Penerimaan Cash',
            'bank-receipt-book' => 'Laporan Buku Pembantu Penerimaan Bank',
            'cash-bank-receipt-book' => 'Laporan Buku Pembantu Penerimaan Cash + Bank',
            'daily-cash' => 'Laporan Kas Harian',
            'monthly-summary' => 'Laporan Ringkasan Bulanan',
            'yearly-summary' => 'Laporan Ringkasan Tahunan',
            'arrears' => 'Laporan Tunggakan',
            'student-ledger' => 'Ledger Siswa',
            default => strtoupper($type),
        };
    }

    protected function resolvePeriodRange(Request $request): array
    {
        if ($request->string('mode', 'daily')->toString() === 'monthly') {
            $year = (int) $request->integer('year', now()->year);
            $month = max(1, min(12, (int) $request->integer('month', now()->month)));
            $start = Carbon::createFromDate($year, $month, 1)->startOfMonth();

            return [$start->toDateString(), $start->copy()->endOfMonth()->toDateString()];
        }

        $fromYear = (int) $request->integer('from_year', now()->year);
        $fromMonth = (int) $request->integer('from_month', now()->month);
        $fromDay = (int) $request->integer('from_day', now()->day);
        $toYear = (int) $request->integer('to_year', $fromYear);
        $toMonth = (int) $request->integer('to_month', $fromMonth);
        $toDay = (int) $request->integer('to_day', $fromDay);

        try { $from = Carbon::createFromDate($fromYear, $fromMonth, $fromDay); } catch (\Throwable) { $from = now(); }
        try { $to = Carbon::createFromDate($toYear, $toMonth, $toDay); } catch (\Throwable) { $to = $from->copy(); }

        return $to->lessThan($from) ? [$to->toDateString(), $from->toDateString()] : [$from->toDateString(), $to->toDateString()];
    }
}
