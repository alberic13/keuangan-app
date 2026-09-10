<?php

namespace App\Livewire;

use App\Services\ReportService;
use App\Services\Reports\ReportPeriodResolver;
use Livewire\Component;

class ReportsPage extends Component
{
    public function render(ReportService $reportService, ReportPeriodResolver $periodResolver)
    {
        [$dateFrom, $dateTo, $periodLabel, $filterState, $periodAlert] = $periodResolver->resolve();

        $filters = [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ];

        $history = $periodResolver->buildHistory(
            $reportService->incomePayments($filters),
            $reportService->expenseDetails($filters)
        );

        $rows = $reportService->cashflow($filters);
        $summaryRow = $rows[0] ?? ['income' => 0, 'expense' => 0, 'net' => 0];

        return view('livewire.reports-page', [
            'type' => 'cashflow',
            'rows' => $rows,
            'summary' => [
                'income' => (int) ($summaryRow['income'] ?? 0),
                'expense' => (int) ($summaryRow['expense'] ?? 0),
                'balance' => (int) ($summaryRow['net'] ?? 0),
                'count' => $history->count(),
            ],
            'periodLabel' => $periodLabel,
            'filter' => $filterState,
            'periodAlert' => $periodAlert,
            'history' => $history,
        ])->layout('layouts.app', [
            'pageTitle' => 'Laporan',
            'pageHeading' => 'Laporan Uang Masuk & Keluar',
            'activeNav' => 'reports',
            'searchPlaceholder' => null,
        ]);
    }
}
