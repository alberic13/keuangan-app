<?php

namespace App\Livewire;

use App\Services\ReportService;
use Carbon\Carbon;
use Livewire\Component;

class ReportsPage extends Component
{
    public function render(ReportService $reportService)
    {
        [$dateFrom, $dateTo, $periodLabel, $filterState, $periodAlert] = $this->resolvePeriod();

        $filters = [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ];

        $type = 'cashflow';
        $payments = $reportService->incomePayments($filters + ['limit' => 50]);
        $expenses = $reportService->expenseDetails($filters + ['limit' => 50]);
        $paymentHistory = collect($payments->map(function ($payment) {
            return [
                'date' => $payment->payment_date,
                'kind' => 'uang_masuk',
                'reference' => $payment->payment_no,
                'description' => $payment->student?->full_name ?? '-',
                'account' => $payment->cashAccount?->name ?? '-',
                'income' => (int) $payment->total_amount,
                'expense' => 0,
            ];
        })->all());

        $expenseHistory = collect($expenses->map(function ($expense) {
            return [
                'date' => $expense->transaction_date,
                'kind' => 'uang_keluar',
                'reference' => $expense->expense_no,
                'description' => $expense->description,
                'account' => $expense->paymentAccount?->name ?? '-',
                'income' => 0,
                'expense' => (int) $expense->amount,
            ];
        })->all());

        $history = $paymentHistory
            ->map(function ($payment) {
                return $payment;
            })
            ->merge($expenseHistory)
            ->sortByDesc(fn (array $item) => $item['date']?->timestamp ?? 0)
            ->values();

        $rows = $reportService->cashflow($filters);
        $summaryRow = $rows[0] ?? ['income' => 0, 'expense' => 0, 'net' => 0];

        return view('livewire.reports-page', [
            'type' => $type,
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

    protected function resolvePeriod(): array
    {
        $now = now();
        $hasPeriodQuery = request()->hasAny([
            'mode',
            'from_day',
            'from_month',
            'from_year',
            'to_day',
            'to_month',
            'to_year',
            'month',
            'year',
        ]);
        $mode = $hasPeriodQuery ? request('mode', 'daily') : 'daily'; // daily|monthly

        if ($mode === 'monthly') {
            $year = (int) request('year', $now->year);
            $month = (int) request('month', $now->month);
            $month = max(1, min(12, $month));

            $start = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $end = $start->copy()->endOfMonth();

            return [
                $start->toDateString(),
                $end->toDateString(),
                'Periode '. $start->translatedFormat('F Y'),
                [
                    'mode' => 'monthly',
                    'month' => $month,
                    'year' => $year,
                ],
                null,
            ];
        }

        if (! $hasPeriodQuery) {
            return [
                $now->toDateString(),
                $now->toDateString(),
                'Tanggal '.$now->translatedFormat('d F Y'),
                [
                    'mode' => 'daily',
                    'from_day' => (int) $now->format('d'),
                    'from_month' => (int) $now->format('m'),
                    'from_year' => (int) $now->format('Y'),
                    'to_day' => (int) $now->format('d'),
                    'to_month' => (int) $now->format('m'),
                    'to_year' => (int) $now->format('Y'),
                ],
                null,
            ];
        }

        $fromYear = (int) request('from_year', $now->year);
        $fromMonth = (int) request('from_month', $now->month);
        $fromDay = (int) request('from_day', $now->day);

        $toYear = (int) request('to_year', $fromYear);
        $toMonth = (int) request('to_month', $fromMonth);
        $toDay = (int) request('to_day', $fromDay);

        try {
            $from = Carbon::createFromDate($fromYear, $fromMonth, $fromDay)->startOfDay();
        } catch (\Throwable) {
            $from = $now->copy()->startOfDay();
        }

        try {
            $to = Carbon::createFromDate($toYear, $toMonth, $toDay)->startOfDay();
        } catch (\Throwable) {
            $to = $from->copy();
        }

        $periodAlert = null;

        if ($to->lessThan($from)) {
            $periodAlert = 'Tanggal awal tidak boleh lebih besar dari tanggal sampai.';
        }

        $label = $from->isSameDay($to)
            ? 'Tanggal '.$from->translatedFormat('d F Y')
            : sprintf('Periode %s - %s', $from->translatedFormat('d F Y'), $to->translatedFormat('d F Y'));

        return [
            $from->toDateString(),
            $to->toDateString(),
            $label,
            [
                'mode' => 'daily',
                'from_day' => (int) $from->format('d'),
                'from_month' => (int) $from->format('m'),
                'from_year' => (int) $from->format('Y'),
                'to_day' => (int) $to->format('d'),
                'to_month' => (int) $to->format('m'),
                'to_year' => (int) $to->format('Y'),
            ],
            $periodAlert,
        ];
    }
}
