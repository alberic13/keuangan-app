<?php

namespace App\Services;

use App\Models\CashLedgerEntry;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ReportService
{
    public function cashflow(array $filters = []): array
    {
        $cashIn = (int) $this->ledgerQuery(array_merge($filters, ['direction' => 'in']))->sum('amount');
        $cashOut = (int) $this->ledgerQuery(array_merge($filters, ['direction' => 'out']))->sum('amount');

        return [[
            'date_from' => $filters['date_from'] ?? null,
            'date_to' => $filters['date_to'] ?? null,
            'income' => $cashIn,
            'expense' => $cashOut,
            'net' => $cashIn - $cashOut,
        ]];
    }

    public function incomePayments(array $filters = []): Collection
    {
        return Payment::query()
            ->with(['student', 'cashAccount'])
            ->when(! empty($filters['date_from']), fn (Builder $query) => $query->whereDate('payment_date', '>=', $filters['date_from']))
            ->when(! empty($filters['date_to']), fn (Builder $query) => $query->whereDate('payment_date', '<=', $filters['date_to']))
            ->latest('payment_date')
            ->when(array_key_exists('limit', $filters), fn (Builder $query) => $query->limit((int) $filters['limit']))
            ->get();
    }

    public function expenseDetails(array $filters = []): Collection
    {
        return Expense::query()
            ->with(['category', 'paymentAccount'])
            ->when(! empty($filters['date_from']), fn (Builder $query) => $query->whereDate('transaction_date', '>=', $filters['date_from']))
            ->when(! empty($filters['date_to']), fn (Builder $query) => $query->whereDate('transaction_date', '<=', $filters['date_to']))
            ->latest('transaction_date')
            ->when(array_key_exists('limit', $filters), fn (Builder $query) => $query->limit((int) $filters['limit']))
            ->get();
    }

    public function dashboardSummary(?string $month = null): array
    {
        [$monthStart, $monthEnd, $monthLabel] = $this->monthRange($month);

        $cashInQuery = CashLedgerEntry::query()
            ->where('direction', 'in');
        $cashOutQuery = CashLedgerEntry::query()
            ->where('direction', 'out');
        $paymentsQuery = Payment::query();
        $outstandingQuery = Invoice::query()
            ->whereIn('status', ['unpaid', 'partial']);
        $invoicesQuery = Invoice::query();

        if ($monthStart && $monthEnd) {
            $cashInQuery->whereBetween('transaction_date', [$monthStart, $monthEnd]);
            $cashOutQuery->whereBetween('transaction_date', [$monthStart, $monthEnd]);
            $paymentsQuery->whereBetween('payment_date', [$monthStart, $monthEnd]);
            $outstandingQuery->whereHas('billingCycle', function (Builder $query) use ($monthStart, $monthEnd) {
                $query->whereBetween('due_date', [$monthStart, $monthEnd]);
            });
            $invoicesQuery->whereHas('billingCycle', function (Builder $query) use ($monthStart, $monthEnd) {
                $query->whereBetween('due_date', [$monthStart, $monthEnd]);
            });
        }

        $cashIn = (int) $cashInQuery->sum('amount');
        $cashOut = (int) $cashOutQuery->sum('amount');

        return [
            'total_invoices' => (int) $invoicesQuery->sum('total_amount'),
            'total_payments' => (int) $paymentsQuery->sum('total_amount'),
            'outstanding' => (int) $outstandingQuery->sum('outstanding_amount'),
            'income' => $cashIn,
            'expense' => $cashOut,
            'net_cash_balance' => $cashIn - $cashOut,
            'is_month_filtered' => $monthStart !== null,
            'month' => $month,
            'month_label' => $monthLabel,
            'recent_payments' => $this->recentPayments()->toArray(),
        ];
    }

    public function paymentTrend(int $months = 6, ?string $selectedMonth = null): array
    {
        $series = [];

        for ($offset = $months - 1; $offset >= 0; $offset--) {
            $month = now()->copy()->subMonths($offset);
            $series[] = [
                'label' => $month->translatedFormat('M Y'),
                'month_key' => $month->format('Y-m'),
                'amount' => (int) Payment::query()
                    ->whereBetween('payment_date', [$month->copy()->startOfMonth(), $month->copy()->endOfMonth()])
                    ->sum('total_amount'),
                'is_selected' => $selectedMonth === $month->format('Y-m'),
            ];
        }

        return $series;
    }

    public function recentPayments(int $limit = 10): Collection
    {
        return Payment::query()
            ->with(['student', 'cashAccount'])
            ->latest('payment_date')
            ->limit($limit)
            ->get();
    }

    public function ledger(array $filters = []): Collection
    {
        return $this->ledgerQuery($filters)
            ->with('account')
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();
    }

    public function bku(array $filters = []): array
    {
        return $this->buildRunningLedger($this->ledger($filters), $filters, true);
    }

    public function cashBook(array $filters = []): array
    {
        $filters['account_type'] = 'cash';

        return $this->buildRunningLedger($this->ledger($filters), $filters, true);
    }

    public function cashReceiptBook(array $filters = []): array
    {
        $filters['account_type'] = 'cash';
        $filters['direction'] = 'in';

        return $this->buildRunningLedger($this->ledger($filters), $filters);
    }

    public function bankReceiptBook(array $filters = []): array
    {
        $filters['account_type'] = 'bank';
        $filters['direction'] = 'in';

        return $this->buildRunningLedger($this->ledger($filters), $filters);
    }

    public function cashBankReceiptBook(array $filters = []): array
    {
        $filters['direction'] = 'in';

        return $this->buildRunningLedger($this->ledger($filters), $filters);
    }

    public function dailyCash(string $date): array
    {
        $filters = [
            'date_from' => $date,
            'date_to' => $date,
        ];

        return $this->buildRunningLedger($this->ledger($filters), $filters, true);
    }

    public function monthlySummary(int $year): array
    {
        $rows = [];

        for ($month = 1; $month <= 12; $month++) {
            $start = Carbon::create($year, $month)->startOfMonth();
            $end = $start->copy()->endOfMonth();

            $rows[] = [
                'month' => $start->translatedFormat('F Y'),
                'income' => (int) CashLedgerEntry::query()
                    ->where('direction', 'in')
                    ->whereBetween('transaction_date', [$start, $end])
                    ->sum('amount'),
                'expense' => (int) CashLedgerEntry::query()
                    ->where('direction', 'out')
                    ->whereBetween('transaction_date', [$start, $end])
                    ->sum('amount'),
            ];
        }

        return $rows;
    }

    public function yearlySummary(int $fromYear, int $toYear): array
    {
        $rows = [];

        for ($year = $fromYear; $year <= $toYear; $year++) {
            $rows[] = [
                'year' => $year,
                'income' => (int) CashLedgerEntry::query()
                    ->where('direction', 'in')
                    ->whereYear('transaction_date', $year)
                    ->sum('amount'),
                'expense' => (int) CashLedgerEntry::query()
                    ->where('direction', 'out')
                    ->whereYear('transaction_date', $year)
                    ->sum('amount'),
            ];
        }

        return $rows;
    }

    public function studentLedger(Student $student): array
    {
        return [
            'student' => $student->load(['batch', 'classRoom', 'major']),
            'invoices' => $student->invoices()->with(['feeType', 'billingCycle'])->latest()->get(),
            'payments' => $student->payments()->with(['items.invoice', 'cashAccount'])->latest('payment_date')->get(),
        ];
    }

    public function arrears(array $filters = []): Collection
    {
        return Invoice::query()
            ->with(['student.batch', 'student.classRoom', 'student.major', 'feeType', 'billingCycle'])
            ->whereIn('status', ['unpaid', 'partial'])
            ->when(! empty($filters['billing_month']), function (Builder $query) use ($filters) {
                [$monthStart, $monthEnd] = $this->monthRange($filters['billing_month']);

                if ($monthStart && $monthEnd) {
                    $query->whereHas('billingCycle', fn (Builder $cycleQuery) => $cycleQuery->whereBetween('due_date', [$monthStart, $monthEnd]));
                }
            })
            ->when(! empty($filters['batch_id']), fn (Builder $query) => $query->whereHas('student', fn (Builder $studentQuery) => $studentQuery->where('batch_id', $filters['batch_id'])))
            ->when(! empty($filters['class_id']), fn (Builder $query) => $query->whereHas('student', fn (Builder $studentQuery) => $studentQuery->where('class_id', $filters['class_id'])))
            ->when(! empty($filters['major_id']), fn (Builder $query) => $query->whereHas('student', fn (Builder $studentQuery) => $studentQuery->where('major_id', $filters['major_id'])))
            ->orderByDesc('outstanding_amount')
            ->get();
    }

    public function monthRange(?string $month): array
    {
        if (! is_string($month) || ! preg_match('/^\d{4}-\d{2}$/', $month)) {
            return [null, null, 'Semua Bulan'];
        }

        try {
            $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        } catch (\Throwable) {
            return [null, null, 'Semua Bulan'];
        }

        return [$start, $start->copy()->endOfMonth(), $start->translatedFormat('F Y')];
    }

    protected function buildRunningLedger(Collection $entries, array $filters = [], bool $includeOpeningBalance = false): array
    {
        $openingBalance = $includeOpeningBalance ? $this->openingBalance($filters) : 0;
        $runningBalance = $openingBalance;

        $rows = [];

        if ($includeOpeningBalance && ($openingBalance !== 0 || $entries->isNotEmpty() || ! empty($filters['date_from']))) {
            $rows[] = [
                'date' => $filters['date_from'] ?? null,
                'entry_no' => null,
                'account' => null,
                'direction' => null,
                'source_type' => 'opening_balance',
                'description' => 'Saldo Awal Periode',
                'debit' => 0,
                'credit' => 0,
                'balance' => $openingBalance,
                'is_opening_balance' => true,
            ];
        }

        return [
            ...$rows,
            ...$entries->map(function (CashLedgerEntry $entry) use (&$runningBalance) {
            $debit = $entry->direction === 'in' ? (int) $entry->amount : 0;
            $credit = $entry->direction === 'out' ? (int) $entry->amount : 0;
            $runningBalance += $debit - $credit;

            return [
                'date' => $entry->transaction_date?->format('Y-m-d'),
                'entry_no' => $entry->entry_no,
                'account' => $entry->account?->name,
                'direction' => $entry->direction,
                'source_type' => $entry->source_type,
                'description' => $entry->description,
                'debit' => $debit,
                'credit' => $credit,
                'balance' => $runningBalance,
                'is_opening_balance' => false,
            ];
            })->all(),
        ];
    }

    protected function openingBalance(array $filters = []): int
    {
        if (empty($filters['date_from'])) {
            return 0;
        }

        $queryFilters = array_diff_key($filters, array_flip(['date_from', 'date_to']));

        return (int) $this->ledgerQuery($queryFilters)
            ->whereDate('transaction_date', '<', $filters['date_from'])
            ->get()
            ->reduce(function (int $carry, CashLedgerEntry $entry) {
                return $carry + ($entry->direction === 'in' ? (int) $entry->amount : -(int) $entry->amount);
            }, 0);
    }

    protected function ledgerQuery(array $filters = []): Builder
    {
        return CashLedgerEntry::query()
            ->when(! empty($filters['account_id']), fn (Builder $query) => $query->where('account_id', $filters['account_id']))
            ->when(! empty($filters['direction']), fn (Builder $query) => $query->where('direction', $filters['direction']))
            ->when(! empty($filters['source_type']), fn (Builder $query) => $query->where('source_type', $filters['source_type']))
            ->when(! empty($filters['date_from']), fn (Builder $query) => $query->whereDate('transaction_date', '>=', $filters['date_from']))
            ->when(! empty($filters['date_to']), fn (Builder $query) => $query->whereDate('transaction_date', '<=', $filters['date_to']))
            ->when(! empty($filters['account_type']), fn (Builder $query) => $query->whereHas('account', fn (Builder $accountQuery) => $accountQuery->where('type', $filters['account_type'])));
    }
}
