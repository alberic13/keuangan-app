<?php

namespace App\Services\Reports;

use App\Models\CashLedgerEntry;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CashBookReportService
{
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
        $filters = ['date_from' => $date, 'date_to' => $date];
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
                'income' => (int) CashLedgerEntry::query()->where('direction', 'in')->whereBetween('transaction_date', [$start, $end])->sum('amount'),
                'expense' => (int) CashLedgerEntry::query()->where('direction', 'out')->whereBetween('transaction_date', [$start, $end])->sum('amount'),
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
                'income' => (int) CashLedgerEntry::query()->where('direction', 'in')->whereYear('transaction_date', $year)->sum('amount'),
                'expense' => (int) CashLedgerEntry::query()->where('direction', 'out')->whereYear('transaction_date', $year)->sum('amount'),
            ];
        }
        return $rows;
    }

    public function buildRunningLedger(Collection $entries, array $filters = [], bool $includeOpeningBalance = false): array
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

    public function openingBalance(array $filters = []): int
    {
        if (empty($filters['date_from'])) {
            return 0;
        }

        $queryFilters = array_diff_key($filters, array_flip(['date_from', 'date_to']));

        return (int) $this->ledgerQuery($queryFilters)
            ->whereDate('transaction_date', '<', $filters['date_from'])
            ->get()
            ->reduce(fn (int $carry, CashLedgerEntry $entry) => $carry + ($entry->direction === 'in' ? (int) $entry->amount : -(int) $entry->amount), 0);
    }

    public function ledgerQuery(array $filters = []): Builder
    {
        return CashLedgerEntry::query()
            ->when(! empty($filters['account_id']), fn (Builder $query) => $query->where('account_id', $filters['account_id']))
            ->when(! empty($filters['direction']), fn (Builder $query) => $query->where('direction', $filters['direction']))
            ->when(! empty($filters['source_type']), fn (Builder $query) => $query->where('source_type', $filters['source_type']))
            ->when(! empty($filters['date_from']), fn (Builder $query) => $query->whereDate('transaction_date', '>=', $filters['date_from']))
            ->when(! empty($filters['date_to']), fn (Builder $query) => $query->whereDate('transaction_date', '<=', $filters['date_to']))
            ->when(! empty($filters['account_type']), fn (Builder $query) => $query->whereHas('account', fn (Builder $q) => $q->where('type', $filters['account_type'])));
    }
}
