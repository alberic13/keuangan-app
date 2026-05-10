<?php

namespace App\Services;

use App\Models\CashAccount;
use App\Models\CashLedgerEntry;
use App\Models\Expense;
use App\Models\User;
use App\Support\DocumentNumber;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ExpenseService
{
    public function __construct(
        protected AuditLogService $auditLogs,
    ) {
    }

    public function create(array $attributes, User $actor): Expense
    {
        return DB::transaction(function () use ($attributes, $actor) {
            $account = $this->activeAccount($attributes['payment_account_id']);

            $expense = Expense::query()->create([
                'expense_no' => DocumentNumber::next('EXP', Expense::class, 'expense_no', $attributes['transaction_date']),
                'transaction_date' => $attributes['transaction_date'],
                'category_id' => $attributes['category_id'],
                'payment_account_id' => $account->id,
                'amount' => $attributes['amount'],
                'description' => $attributes['description'],
                'attachment_path' => $attributes['attachment_path'] ?? null,
                'status' => 'posted',
                'created_by' => $actor->id,
            ]);

            $this->syncLedger($expense, $actor);
            $this->auditLogs->log('expense.created', $expense, null, $expense->toArray(), null, $actor);

            return $expense->fresh(['category', 'paymentAccount']);
        });
    }

    public function update(Expense $expense, array $attributes, User $actor): Expense
    {
        return DB::transaction(function () use ($expense, $attributes, $actor) {
            $before = $expense->toArray();
            $account = $this->activeAccount($attributes['payment_account_id'] ?? $expense->payment_account_id);

            $expense->update([
                'transaction_date' => $attributes['transaction_date'] ?? $expense->transaction_date,
                'category_id' => $attributes['category_id'] ?? $expense->category_id,
                'payment_account_id' => $account->id,
                'amount' => $attributes['amount'] ?? $expense->amount,
                'description' => $attributes['description'] ?? $expense->description,
                'attachment_path' => $attributes['attachment_path'] ?? $expense->attachment_path,
                'status' => 'edited',
                'updated_by' => $actor->id,
            ]);

            $this->syncLedger($expense, $actor);
            $this->auditLogs->log('expense.updated', $expense, $before, $expense->fresh()->toArray(), 'Edit pengeluaran', $actor);

            return $expense->fresh(['category', 'paymentAccount']);
        });
    }

    public function delete(Expense $expense, User $actor): void
    {
        DB::transaction(function () use ($expense, $actor) {
            $before = $expense->toArray();

            CashLedgerEntry::query()
                ->where('source_type', 'expense')
                ->where('source_id', $expense->id)
                ->delete();

            if ($expense->attachment_path) {
                Storage::disk('public')->delete($expense->attachment_path);
            }

            $expense->delete();

            $this->auditLogs->log('expense.deleted', $expense, $before, null, 'Hapus pengeluaran', $actor);
        });
    }

    protected function syncLedger(Expense $expense, User $actor): void
    {
        CashLedgerEntry::query()
            ->where('source_type', 'expense')
            ->where('source_id', $expense->id)
            ->delete();

        CashLedgerEntry::query()->create([
            'entry_no' => DocumentNumber::next('LED', CashLedgerEntry::class, 'entry_no', $expense->transaction_date),
            'transaction_date' => $expense->transaction_date,
            'account_id' => $expense->payment_account_id,
            'direction' => 'out',
            'source_type' => 'expense',
            'source_id' => $expense->id,
            'amount' => $expense->amount,
            'description' => $expense->description,
            'status' => 'posted',
            'created_by' => $actor->id,
        ]);
    }

    protected function activeAccount(int $accountId): CashAccount
    {
        $account = CashAccount::query()->findOrFail($accountId);

        if (! $account->is_active) {
            throw ValidationException::withMessages([
                'payment_account_id' => 'Akun kas/bank tidak aktif.',
            ]);
        }

        return $account;
    }
}
