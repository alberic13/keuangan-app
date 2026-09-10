<?php

namespace App\Services\Reports;

use Carbon\Carbon;
use Illuminate\Support\Collection;

class ReportPeriodResolver
{
    public function resolve(): array
    {
        $now = now();
        $hasPeriodQuery = request()->hasAny([
            'mode', 'from_day', 'from_month', 'from_year',
            'to_day', 'to_month', 'to_year', 'month', 'year',
        ]);
        $mode = $hasPeriodQuery ? request('mode', 'daily') : 'daily';

        if ($mode === 'monthly') {
            return $this->resolveMonthlyPeriod($now);
        }

        if (! $hasPeriodQuery) {
            return $this->resolveDefaultDailyPeriod($now);
        }

        return $this->resolveCustomDailyPeriod($now);
    }

    public function buildHistory(Collection $payments, Collection $expenses): Collection
    {
        $paymentHistory = $payments->map(fn ($p) => [
            'date' => $p->payment_date,
            'kind' => 'uang_masuk',
            'reference' => $p->payment_no,
            'description' => $p->student?->full_name ?? '-',
            'account' => $p->cashAccount?->name ?? '-',
            'method' => $p->method === 'bank_transfer' ? 'Transfer' : 'Tunai',
            'income' => (int) $p->total_amount,
            'expense' => 0,
        ]);

        $expenseHistory = $expenses->map(fn ($e) => [
            'date' => $e->transaction_date,
            'kind' => 'uang_keluar',
            'reference' => $e->expense_no,
            'description' => $e->description,
            'account' => $e->paymentAccount?->name ?? '-',
            'method' => '-',
            'income' => 0,
            'expense' => (int) $e->amount,
        ]);

        return $paymentHistory
            ->merge($expenseHistory)
            ->sortByDesc(fn (array $item) => $item['date']?->timestamp ?? 0)
            ->values();
    }

    protected function resolveMonthlyPeriod(Carbon $now): array
    {
        $year = (int) request('year', $now->year);
        $month = max(1, min(12, (int) request('month', $now->month)));
        $start = Carbon::createFromDate($year, $month, 1)->startOfMonth();

        return [
            $start->toDateString(),
            $start->copy()->endOfMonth()->toDateString(),
            'Periode ' . $start->translatedFormat('F Y'),
            ['mode' => 'monthly', 'month' => $month, 'year' => $year],
            null,
        ];
    }

    protected function resolveDefaultDailyPeriod(Carbon $now): array
    {
        $dateStr = $now->toDateString();
        $dateArr = [
            'mode' => 'daily',
            'from_day' => (int) $now->format('d'),
            'from_month' => (int) $now->format('m'),
            'from_year' => (int) $now->format('Y'),
            'to_day' => (int) $now->format('d'),
            'to_month' => (int) $now->format('m'),
            'to_year' => (int) $now->format('Y'),
        ];

        return [$dateStr, $dateStr, 'Tanggal ' . $now->translatedFormat('d F Y'), $dateArr, null];
    }

    protected function resolveCustomDailyPeriod(Carbon $now): array
    {
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

        $periodAlert = $to->lessThan($from) ? 'Tanggal awal tidak boleh lebih besar dari tanggal sampai.' : null;
        $label = $from->isSameDay($to)
            ? 'Tanggal ' . $from->translatedFormat('d F Y')
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
