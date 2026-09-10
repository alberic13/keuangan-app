<?php

namespace App\Services;

use App\Models\CashLedgerEntry;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Student;
use App\Services\Reports\CashBookReportService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ReportService
{
    public function __construct(
        protected CashBookReportService $cashBookReports = new CashBookReportService(),
    ) {
    }

    public function cashflow(array $filters = []): array
    {
        $cashIn = (int) $this->cashBookReports->ledgerQuery(array_merge($filters, ['direction' => 'in']))->sum('amount');
        $cashOut = (int) $this->cashBookReports->ledgerQuery(array_merge($filters, ['direction' => 'out']))->sum('amount');

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
        return Payment::query()->with(['student', 'cashAccount'])
            ->when(! empty($filters['date_from']), fn (Builder $q) => $q->whereDate('payment_date', '>=', $filters['date_from']))
            ->when(! empty($filters['date_to']), fn (Builder $q) => $q->whereDate('payment_date', '<=', $filters['date_to']))
            ->latest('payment_date')
            ->when(array_key_exists('limit', $filters), fn (Builder $q) => $q->limit((int) $filters['limit']))
            ->get();
    }

    public function expenseDetails(array $filters = []): Collection
    {
        return Expense::query()->with(['category', 'paymentAccount'])
            ->when(! empty($filters['date_from']), fn (Builder $q) => $q->whereDate('transaction_date', '>=', $filters['date_from']))
            ->when(! empty($filters['date_to']), fn (Builder $q) => $q->whereDate('transaction_date', '<=', $filters['date_to']))
            ->latest('transaction_date')
            ->when(array_key_exists('limit', $filters), fn (Builder $q) => $q->limit((int) $filters['limit']))
            ->get();
    }

    public function dashboardSummary(?string $month = null): array
    {
        [$monthStart, $monthEnd, $monthLabel] = $this->monthRange($month);

        $cashInQuery = CashLedgerEntry::query()->where('direction', 'in');
        $cashOutQuery = CashLedgerEntry::query()->where('direction', 'out');
        $paymentsQuery = Payment::query();
        $outstandingQuery = Invoice::query()->whereIn('status', ['unpaid', 'partial']);
        $invoicesQuery = Invoice::query();

        if ($monthStart && $monthEnd) {
            $cashInQuery->whereBetween('transaction_date', [$monthStart, $monthEnd]);
            $cashOutQuery->whereBetween('transaction_date', [$monthStart, $monthEnd]);
            $paymentsQuery->whereBetween('payment_date', [$monthStart, $monthEnd]);
            $outstandingQuery->whereHas('billingCycle', fn (Builder $q) => $q->whereBetween('due_date', [$monthStart, $monthEnd]));
            $invoicesQuery->whereHas('billingCycle', fn (Builder $q) => $q->whereBetween('due_date', [$monthStart, $monthEnd]));
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
        return Payment::query()->with(['student', 'cashAccount'])->latest('payment_date')->limit($limit)->get();
    }

    public function ledger(array $filters = []): Collection { return $this->cashBookReports->ledger($filters); }
    public function bku(array $filters = []): array { return $this->cashBookReports->bku($filters); }
    public function cashBook(array $filters = []): array { return $this->cashBookReports->cashBook($filters); }
    public function cashReceiptBook(array $filters = []): array { return $this->cashBookReports->cashReceiptBook($filters); }
    public function bankReceiptBook(array $filters = []): array { return $this->cashBookReports->bankReceiptBook($filters); }
    public function cashBankReceiptBook(array $filters = []): array { return $this->cashBookReports->cashBankReceiptBook($filters); }
    public function dailyCash(string $date): array { return $this->cashBookReports->dailyCash($date); }
    public function monthlySummary(int $year): array { return $this->cashBookReports->monthlySummary($year); }
    public function yearlySummary(int $fromYear, int $toYear): array { return $this->cashBookReports->yearlySummary($fromYear, $toYear); }

    public function studentLedger(Student $student): array
    {
        return [
            'student' => $student->load(['batch', 'classRoom']),
            'invoices' => $student->invoices()->with(['feeType', 'billingCycle'])->latest()->get(),
            'payments' => $student->payments()->with(['items.invoice', 'cashAccount'])->latest('payment_date')->get(),
        ];
    }

    public function arrears(array $filters = []): Collection
    {
        return Invoice::query()
            ->with(['student.batch', 'student.classRoom', 'feeType', 'billingCycle'])
            ->whereIn('status', ['unpaid', 'partial'])
            ->when(! empty($filters['billing_month']), function (Builder $query) use ($filters) {
                [$monthStart, $monthEnd] = $this->monthRange($filters['billing_month']);
                if ($monthStart && $monthEnd) {
                    $query->whereHas('billingCycle', fn (Builder $cq) => $cq->whereBetween('due_date', [$monthStart, $monthEnd]));
                }
            })
            ->when(! empty($filters['batch_id']), fn (Builder $q) => $q->whereHas('student', fn (Builder $sq) => $sq->where('batch_id', $filters['batch_id'])))
            ->when(! empty($filters['class_id']), fn (Builder $q) => $q->whereHas('student', fn (Builder $sq) => $sq->where('class_id', $filters['class_id'])))
            ->orderByDesc('outstanding_amount')->get();
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
}
