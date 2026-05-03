<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Services\ReportService;

class DashboardController extends Controller
{
    use ApiResponses;

    public function __construct(
        protected ReportService $reportService,
    ) {
    }

    public function summary()
    {
        return $this->success($this->reportService->dashboardSummary());
    }

    public function paymentTrend()
    {
        return $this->success($this->reportService->paymentTrend());
    }

    public function recentPayments()
    {
        return $this->success($this->reportService->recentPayments()->toArray());
    }
}
