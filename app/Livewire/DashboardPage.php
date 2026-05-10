<?php

namespace App\Livewire;

use App\Services\ReportService;
use Illuminate\Support\Collection;
use Livewire\Component;

class DashboardPage extends Component
{
    public function render(ReportService $reportService)
    {
        $selectedMonth = request()->string('month')->toString();
        $search = strtolower(trim((string) request('search')));
        [$monthStart, $monthEnd] = $reportService->monthRange($selectedMonth);
        $summary = $reportService->dashboardSummary($selectedMonth);
        $arrears = $reportService->arrears(array_filter([
            'billing_month' => $summary['is_month_filtered'] ? $selectedMonth : null,
        ]))
            ->when($search !== '', function (Collection $rows) use ($search) {
                return $rows->filter(function ($row) use ($search) {
                    return str_contains(strtolower(json_encode($row, JSON_UNESCAPED_UNICODE)), $search);
                })->values();
            })
            ->take(5);

        $ledgerFilters = $summary['is_month_filtered']
            ? [
                'date_from' => $monthStart?->toDateString(),
                'date_to' => $monthEnd?->toDateString(),
            ]
            : [
                'date_from' => now()->subDays(30)->toDateString(),
                'date_to' => now()->toDateString(),
            ];

        $ledger = collect($reportService->bku($ledgerFilters))
            ->when($search !== '', function (Collection $rows) use ($search) {
                return $rows->filter(function (array $row) use ($search) {
                    return str_contains(strtolower(json_encode($row, JSON_UNESCAPED_UNICODE)), $search);
                })->values();
            })
            ->take(6)
            ->all();

        return view('livewire.dashboard-page', [
            'summary' => $summary,
            'trend' => $reportService->paymentTrend(6, $summary['is_month_filtered'] ? $selectedMonth : null),
            'arrears' => $arrears,
            'ledger' => $ledger,
            'selectedMonth' => $summary['is_month_filtered'] ? $selectedMonth : null,
        ])->layout('layouts.app', [
            'pageTitle' => 'Dasbor',
            'pageHeading' => 'Ringkasan Keuangan',
            'activeNav' => 'dashboard',
            'searchPlaceholder' => 'Cari data...',
        ]);
    }
}
