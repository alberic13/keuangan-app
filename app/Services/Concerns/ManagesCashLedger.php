<?php

namespace App\Services\Concerns;

use App\Models\CashAccount;
use App\Models\CashLedgerEntry;
use App\Support\DocumentNumber;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

trait ManagesCashLedger
{
    protected function createLedgerEntryWithRetry(array $attributes, string $date, int $attempts = 5): void
    {
        $lastException = null;

        for ($attempt = 1; $attempt <= $attempts; $attempt++) {
            try {
                CashLedgerEntry::query()->create($attributes + [
                    'entry_no' => DocumentNumber::next('LED', CashLedgerEntry::class, 'entry_no', $date),
                ]);

                return;
            } catch (QueryException $exception) {
                if (! $this->isDuplicateEntryNumberException($exception)) {
                    throw $exception;
                }

                $lastException = $exception;
            }
        }

        throw $lastException;
    }

    protected function isDuplicateEntryNumberException(QueryException $exception): bool
    {
        return (string) $exception->getCode() === '23000' && str_contains($exception->getMessage(), 'cash_ledger_entries_entry_no_unique');
    }

    protected function activeAccount(int $accountId, string $field = 'cash_account_id'): CashAccount
    {
        $account = CashAccount::query()->findOrFail($accountId);

        if (! $account->is_active) {
            throw ValidationException::withMessages([
                $field => 'Akun kas/bank tidak aktif.',
            ]);
        }

        return $account;
    }
}
