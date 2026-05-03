<?php

namespace App\Http\Controllers\Api;

use App\Exports\RowsExport;
use App\Http\Controllers\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Student;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    use ApiResponses;

    public function __construct(
        protected ReportService $reportService,
    ) {
    }

    public function dailyCash(Request $request)
    {
        $request->validate(['date' => ['required', 'date']]);

        return $this->success($this->reportService->dailyCash($request->string('date')->toString()));
    }

    public function monthlySummary(Request $request)
    {
        $year = $request->integer('year', now()->year);

        return $this->success($this->reportService->monthlySummary($year));
    }

    public function yearlySummary(Request $request)
    {
        $year = $request->integer('year', now()->year);

        return $this->success($this->reportService->yearlySummary($year - 2, $year));
    }

    public function studentLedger(Student $student)
    {
        return $this->success($this->reportService->studentLedger($student));
    }

    public function arrears(Request $request)
    {
        return $this->success($this->reportService->arrears($request->only(['batch_id', 'class_id', 'major_id'])));
    }

    public function bku(Request $request)
    {
        return $this->success($this->reportService->bku($request->only(['account_id', 'direction', 'date_from', 'date_to', 'source_type'])));
    }

    public function cashBook(Request $request)
    {
        return $this->success($this->reportService->cashBook($request->only(['account_id', 'date_from', 'date_to'])));
    }

    public function cashReceiptBook(Request $request)
    {
        return $this->success($this->reportService->cashReceiptBook($request->only(['account_id', 'date_from', 'date_to'])));
    }

    public function bankReceiptBook(Request $request)
    {
        return $this->success($this->reportService->bankReceiptBook($request->only(['account_id', 'date_from', 'date_to'])));
    }

    public function cashBankReceiptBook(Request $request)
    {
        return $this->success($this->reportService->cashBankReceiptBook($request->only(['account_id', 'date_from', 'date_to'])));
    }

    public function export(Request $request)
    {
        $request->validate([
            'type' => ['required', 'string'],
            'format' => ['required', 'in:pdf,xlsx'],
        ]);

        $type = $request->string('type')->toString();

        if ($type === 'cashflow') {
            [$dateFrom, $dateTo] = $this->resolvePeriodRange($request);
            $request->merge(['date_from' => $dateFrom, 'date_to' => $dateTo]);
        }

        $rows = match ($type) {
            'cashflow' => $this->normalizeCashflowExportRows($request->only(['date_from', 'date_to'])),
            'daily-cash' => $this->reportService->dailyCash($request->string('date', now()->toDateString())->toString()),
            'monthly-summary' => $this->reportService->monthlySummary($request->integer('year', now()->year)),
            'yearly-summary' => $this->reportService->yearlySummary(now()->year - 2, now()->year),
            'student-ledger' => $request->filled('student_id')
                ? $this->reportService->studentLedger(Student::query()->findOrFail($request->integer('student_id')))
                : null,
            'arrears' => $this->reportService->arrears($request->only(['batch_id', 'class_id', 'major_id'])),
            'bku' => $this->reportService->bku($request->only(['account_id', 'direction', 'date_from', 'date_to', 'source_type'])),
            'cash-book' => $this->reportService->cashBook($request->only(['account_id', 'date_from', 'date_to'])),
            'cash-receipt-book' => $this->reportService->cashReceiptBook($request->only(['account_id', 'date_from', 'date_to'])),
            'bank-receipt-book' => $this->reportService->bankReceiptBook($request->only(['account_id', 'date_from', 'date_to'])),
            'cash-bank-receipt-book' => $this->reportService->cashBankReceiptBook($request->only(['account_id', 'date_from', 'date_to'])),
            default => [],
        };

        if ($request->string('format')->toString() === 'xlsx') {
            return Excel::download(
                new RowsExport($this->normalizeRowsForSpreadsheet($type, $rows) ?: [['data' => 'Tidak ada data']]),
                Str::slug($type ?: 'report').'.xlsx',
            );
        }

        return Pdf::loadView('prints.report-export', [
            'type' => $type,
            'title' => $this->resolveTitle($type),
            'rows' => $rows,
            'filters' => $request->except(['format']),
            'generatedAt' => now(),
        ])->download(Str::slug($type ?: 'report').'.pdf');
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
        $mode = $request->string('mode', 'daily')->toString();

        if ($mode === 'monthly') {
            $year = (int) $request->integer('year', now()->year);
            $month = (int) $request->integer('month', now()->month);
            $month = max(1, min(12, $month));

            $start = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $end = $start->copy()->endOfMonth();

            return [$start->toDateString(), $end->toDateString()];
        }

        $fromYear = (int) $request->integer('from_year', now()->year);
        $fromMonth = (int) $request->integer('from_month', now()->month);
        $fromDay = (int) $request->integer('from_day', now()->day);

        $toYear = (int) $request->integer('to_year', $fromYear);
        $toMonth = (int) $request->integer('to_month', $fromMonth);
        $toDay = (int) $request->integer('to_day', $fromDay);

        try {
            $from = Carbon::createFromDate($fromYear, $fromMonth, $fromDay);
        } catch (\Throwable) {
            $from = now();
        }

        try {
            $to = Carbon::createFromDate($toYear, $toMonth, $toDay);
        } catch (\Throwable) {
            $to = $from->copy();
        }

        if ($to->lessThan($from)) {
            [$from, $to] = [$to, $from];
        }

        return [$from->toDateString(), $to->toDateString()];
    }

    protected function normalizeCashflowExportRows(array $filters): array
    {
        $payments = $this->reportService->incomePayments($filters + ['limit' => 500]);
        $expenses = $this->reportService->expenseDetails($filters + ['limit' => 500]);

        $rows = [];

        foreach ($payments as $payment) {
            $rows[] = [
                'tanggal' => $payment->payment_date?->format('Y-m-d') ?? '-',
                'jenis' => 'uang_masuk',
                'no_bukti' => $payment->payment_no,
                'sumber' => $payment->method === 'bank_transfer' ? 'transfer' : 'tunai',
                'keterangan' => $payment->student?->full_name ?? '-',
                'uang_masuk' => (int) $payment->total_amount,
                'uang_keluar' => 0,
            ];
        }

        foreach ($expenses as $expense) {
            $rows[] = [
                'tanggal' => $expense->transaction_date?->format('Y-m-d') ?? '-',
                'jenis' => 'uang_keluar',
                'no_bukti' => $expense->expense_no,
                'sumber' => $expense->category?->name ?? '-',
                'keterangan' => $expense->description,
                'uang_masuk' => 0,
                'uang_keluar' => (int) $expense->amount,
            ];
        }

        return collect($rows)
            ->sortBy('tanggal')
            ->values()
            ->all();
    }

    protected function normalizeRowsForSpreadsheet(string $type, mixed $rows): array
    {
        if ($type === 'student-ledger') {
            return $this->normalizeStudentLedgerRows($rows);
        }

        if ($type === 'arrears') {
            return collect($rows instanceof Collection ? $rows->all() : $rows)
                ->map(function (Invoice $invoice) {
                    return [
                        'invoice_no' => $invoice->invoice_no,
                        'nama_siswa' => $invoice->student->full_name,
                        'kelas' => $invoice->student->classRoom->name ?? '-',
                        'jurusan' => $invoice->student->major->name ?? '-',
                        'jenis_biaya' => $invoice->feeType->name,
                        'periode' => $invoice->billingCycle?->period_label ?? '-',
                        'status' => strtoupper($invoice->status),
                        'outstanding' => $invoice->outstanding_amount,
                    ];
                })
                ->all();
        }

        return collect($rows instanceof Collection ? $rows->all() : $rows)
            ->map(function ($row) {
                if (is_array($row)) {
                    return collect($row)->map(fn ($value) => $this->flattenValue($value))->all();
                }

                if (is_object($row) && method_exists($row, 'toArray')) {
                    return collect($row->toArray())->map(fn ($value) => $this->flattenValue($value))->all();
                }

                return ['data' => $this->flattenValue($row)];
            })
            ->values()
            ->all();
    }

    protected function normalizeStudentLedgerRows(mixed $rows): array
    {
        if (! is_array($rows) || ! isset($rows['student'])) {
            return [];
        }

        $student = $rows['student'];
        $normalized = [[
            'bagian' => 'Profil',
            'dokumen' => $student->full_name,
            'tanggal' => $student->enrollment_date?->format('Y-m-d') ?? '-',
            'keterangan' => trim(($student->classRoom->name ?? '-').' / '.($student->major->name ?? '-').' / '.($student->batch->academic_year ?? '-')),
            'status' => ucfirst($student->student_type),
            'nominal' => '',
            'saldo' => '',
        ]];

        foreach ($rows['invoices'] as $invoice) {
            $normalized[] = [
                'bagian' => 'Invoice',
                'dokumen' => $invoice->invoice_no,
                'tanggal' => $invoice->billingCycle?->due_date?->format('Y-m-d') ?? '-',
                'keterangan' => trim($invoice->feeType->name.' '.($invoice->reference_name ? '- '.$invoice->reference_name : '')),
                'status' => strtoupper($invoice->status),
                'nominal' => $invoice->total_amount,
                'saldo' => $invoice->outstanding_amount,
            ];
        }

        foreach ($rows['payments'] as $payment) {
            $normalized[] = [
                'bagian' => 'Pembayaran',
                'dokumen' => $payment->payment_no,
                'tanggal' => $payment->payment_date?->format('Y-m-d') ?? '-',
                'keterangan' => $payment->items->map(fn ($item) => $item->invoice?->invoice_no)->filter()->join(', '),
                'status' => strtoupper($payment->status),
                'nominal' => $payment->total_amount,
                'saldo' => 0,
            ];
        }

        return $normalized;
    }

    protected function flattenValue(mixed $value): string|int|float
    {
        if (is_array($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        if (is_object($value) && method_exists($value, 'toArray')) {
            return json_encode($value->toArray(), JSON_UNESCAPED_UNICODE);
        }

        return $value ?? '';
    }
}
