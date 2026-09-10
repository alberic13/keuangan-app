<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Services\ReportService;
use App\Services\Reports\ReportExportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    use ApiResponses;

    public function __construct(
        protected ReportService $reportService,
        protected ReportExportService $exportService,
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
        return $this->success($this->reportService->arrears($request->only(['batch_id', 'class_id'])));
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

        return $this->exportService->export($request);
    }
}
