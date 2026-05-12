<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\CashAccount;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Services\AuditLogService;
use App\Services\ExpenseService;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CashController extends Controller
{
    use ApiResponses;

    public function __construct(
        protected ExpenseService $expenseService,
        protected ReportService $reportService,
        protected AuditLogService $auditLogs,
    ) {
    }

    public function cashAccountsIndex()
    {
        return $this->success(CashAccount::query()->orderBy('name')->get());
    }

    public function storeCashAccount(Request $request)
    {
        $this->ensureAnyRole(['admin_keuangan']);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['cash', 'bank'])],
            'account_number' => ['nullable', 'string', 'max:255'],
            'account_holder' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $cashAccount = CashAccount::query()->create($data + ['is_active' => $request->boolean('is_active', true)]);
        $this->auditLogs->log('cash_account.created', $cashAccount, null, $cashAccount->toArray(), null, $request->user());

        return $this->success($cashAccount, 'Success', 201);
    }

    public function updateCashAccount(Request $request, CashAccount $cashAccount)
    {
        $this->ensureAnyRole(['admin_keuangan']);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['cash', 'bank'])],
            'account_number' => ['nullable', 'string', 'max:255'],
            'account_holder' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $before = $cashAccount->toArray();
        $cashAccount->update($data + ['is_active' => $request->boolean('is_active', $cashAccount->is_active)]);
        $this->auditLogs->log('cash_account.updated', $cashAccount, $before, $cashAccount->fresh()->toArray(), null, $request->user());

        return $this->success($cashAccount);
    }

    public function expenseCategoriesIndex()
    {
        return $this->success(ExpenseCategory::query()->orderBy('name')->get());
    }

    public function storeExpenseCategory(Request $request)
    {
        $this->ensureAnyRole(['admin_keuangan']);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $category = ExpenseCategory::query()->create([
            'code' => ExpenseCategory::generateCode($data['name']),
            'name' => $data['name'],
            'is_active' => $request->boolean('is_active', true),
        ]);
        $this->auditLogs->log('expense_category.created', $category, null, $category->toArray(), null, $request->user());

        return $this->success($category, 'Success', 201);
    }

    public function expensesIndex(Request $request)
    {
        $expenses = Expense::query()
            ->with(['category', 'paymentAccount'])
            ->when($request->filled('category_id'), fn ($query) => $query->where('category_id', $request->integer('category_id')))
            ->when($request->filled('payment_account_id'), fn ($query) => $query->where('payment_account_id', $request->integer('payment_account_id')))
            ->latest('transaction_date')
            ->paginate($request->integer('per_page', 15));

        return $this->success($expenses);
    }

    public function storeExpense(Request $request)
    {
        $this->ensureAnyRole(['admin_keuangan', 'bendahara']);
        $data = $request->validate([
            'transaction_date' => ['required', 'date'],
            'category_id' => ['required', 'exists:expense_categories,id'],
            'payment_account_id' => ['required', 'exists:cash_accounts,id'],
            'amount' => ['required', 'integer', 'min:1'],
            'description' => ['required', 'string'],
        ]);

        return $this->success($this->expenseService->create($data, $request->user()), 'Success', 201);
    }

    public function updateExpense(Request $request, Expense $expense)
    {
        $this->ensureAnyRole(['admin_keuangan', 'bendahara']);
        $data = $request->validate([
            'transaction_date' => ['required', 'date'],
            'category_id' => ['required', 'exists:expense_categories,id'],
            'payment_account_id' => ['required', 'exists:cash_accounts,id'],
            'amount' => ['required', 'integer', 'min:1'],
            'description' => ['required', 'string'],
        ]);

        return $this->success($this->expenseService->update($expense, $data, $request->user()));
    }

    public function ledgerIndex(Request $request)
    {
        return $this->success($this->reportService->ledger($request->only([
            'account_id',
            'direction',
            'date_from',
            'date_to',
            'source_type',
        ])));
    }
}
