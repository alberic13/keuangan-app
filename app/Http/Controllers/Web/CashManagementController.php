<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CashAccount;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Services\AuditLogService;
use App\Services\ExpenseService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CashManagementController extends Controller
{
    public function __construct(
        protected ExpenseService $expenseService,
        protected AuditLogService $auditLogs,
    ) {
    }

    public function storeCashAccount(Request $request): RedirectResponse
    {
        $this->ensureAnyRole(['admin_keuangan']);
        $cashAccount = CashAccount::query()->create($this->validateAccount($request) + ['is_active' => true]);
        $this->auditLogs->log('cash_account.created', $cashAccount, null, $cashAccount->toArray(), null, $request->user());

        return $this->redirectBackWithMessage($request, 'Akun kas/bank berhasil ditambahkan.');
    }

    public function updateCashAccount(Request $request, CashAccount $cashAccount): RedirectResponse
    {
        $this->ensureAnyRole(['admin_keuangan']);
        $before = $cashAccount->toArray();
        $cashAccount->update($this->validateAccount($request) + ['is_active' => $request->boolean('is_active', $cashAccount->is_active)]);
        $this->auditLogs->log('cash_account.updated', $cashAccount, $before, $cashAccount->fresh()->toArray(), null, $request->user());

        return $this->redirectBackWithMessage($request, 'Akun kas/bank berhasil diperbarui.');
    }

    public function storeExpenseCategory(Request $request): RedirectResponse
    {
        $this->ensureAnyRole(['admin_keuangan']);
        $data = $request->validate(['name' => ['required', 'string', 'max:255']]);
        $category = ExpenseCategory::query()->create([
            'code' => ExpenseCategory::generateCode($data['name']),
            'name' => $data['name'],
            'is_active' => true,
        ]);
        $this->auditLogs->log('expense_category.created', $category, null, $category->toArray(), null, $request->user());

        return $this->redirectBackWithMessage($request, 'Kategori pengeluaran berhasil ditambahkan.');
    }

    public function storeExpense(Request $request): RedirectResponse
    {
        $this->ensureAnyRole(['admin_keuangan', 'bendahara']);
        $this->expenseService->create($this->validateExpense($request), $request->user());

        return $this->redirectBackWithMessage($request, 'Kas keluar berhasil dicatat.');
    }

    public function updateExpense(Request $request, Expense $expense): RedirectResponse
    {
        $this->ensureAnyRole(['admin_keuangan', 'bendahara']);
        $this->expenseService->update($expense, $this->validateExpense($request), $request->user());

        return $this->redirectBackWithMessage($request, 'Kas keluar berhasil diperbarui.');
    }

    public function destroyExpense(Request $request, Expense $expense): RedirectResponse
    {
        $this->ensureAnyRole(['admin_keuangan', 'bendahara']);
        $this->expenseService->delete($expense, $request->user());

        return $this->redirectBackWithMessage($request, 'Pengeluaran berhasil dihapus.');
    }

    public function printReceipt(Expense $expense)
    {
        $this->ensureAnyRole(['admin_keuangan', 'bendahara', 'kepala_madrasah', 'waka']);
        $expense->load(['category', 'paymentAccount']);

        return Pdf::loadView('prints.expense', ['expense' => $expense])
            ->setPaper([0, 0, 595.28, 283.46], 'portrait')
            ->stream($expense->expense_no.'.pdf');
    }

    protected function validateAccount(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['cash', 'bank'])],
            'account_number' => ['nullable', 'string', 'max:255'],
            'account_holder' => ['nullable', 'string', 'max:255'],
        ]);
    }

    protected function validateExpense(Request $request): array
    {
        return $request->validate([
            'transaction_date' => ['required', 'date'],
            'category_id' => ['required', 'exists:expense_categories,id'],
            'payment_account_id' => ['required', 'exists:cash_accounts,id'],
            'amount' => ['required', 'integer', 'min:1'],
            'description' => ['required', 'string'],
        ]);
    }
}
