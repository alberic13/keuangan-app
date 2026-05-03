<?php

use App\Http\Controllers\Api\ReportController as ApiReportController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Web\BillingManagementController;
use App\Http\Controllers\Web\CashManagementController;
use App\Http\Controllers\Web\FeeManagementController;
use App\Http\Controllers\Web\PaymentManagementController;
use App\Http\Controllers\Web\StudentImportController;
use App\Http\Controllers\Web\StudentManagementController;
use App\Http\Controllers\Web\UserManagementController;
use App\Livewire\AuditLogsPage;
use App\Livewire\BillingPage;
use App\Livewire\CashLedgerPage;
use App\Livewire\DashboardPage;
use App\Livewire\ExpensesPage;
use App\Livewire\FeesPage;
use App\Livewire\ImportsPage;
use App\Livewire\PaymentFormPage;
use App\Livewire\PaymentsPage;
use App\Livewire\ReportsPage;
use App\Livewire\StudentCreatePage;
use App\Livewire\StudentsPage;
use App\Livewire\UsersPage;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', DashboardPage::class)->name('dashboard');
    Route::get('/students/create', StudentCreatePage::class)->name('students.create');
    Route::get('/students', StudentsPage::class)->name('students.index');
    Route::post('/students', [StudentManagementController::class, 'store'])->name('students.store');
    Route::put('/students/{student}', [StudentManagementController::class, 'update'])->name('students.update');
    Route::patch('/students/{student}/activate', [StudentManagementController::class, 'activate'])->name('students.activate');
    Route::patch('/students/{student}/deactivate', [StudentManagementController::class, 'deactivate'])->name('students.deactivate');

    Route::get('/imports/students', ImportsPage::class)->name('imports.students');
    Route::get('/imports/students/template', [StudentImportController::class, 'template'])->name('imports.students.template');
    Route::post('/imports/students/preview', [StudentImportController::class, 'preview'])->name('imports.students.preview');
    Route::post('/imports/students/commit', [StudentImportController::class, 'commit'])->name('imports.students.commit');

    Route::get('/fees', FeesPage::class)->name('fees.index');
    Route::post('/fee-types', [FeeManagementController::class, 'storeFeeType'])->name('fee-types.store');
    Route::put('/fee-types/{feeType}', [FeeManagementController::class, 'updateFeeType'])->name('fee-types.update');
    Route::post('/fee-schemes', [FeeManagementController::class, 'storeFeeScheme'])->name('fee-schemes.store');
    Route::put('/fee-schemes/{feeScheme}', [FeeManagementController::class, 'updateFeeScheme'])->name('fee-schemes.update');

    Route::get('/billing', BillingPage::class)->name('billing.index');
    Route::post('/billing-cycles', [BillingManagementController::class, 'storeCycle'])->name('billing-cycles.store');
    Route::put('/billing-cycles/{billingCycle}', [BillingManagementController::class, 'updateCycle'])->name('billing-cycles.update');
    Route::post('/billing-cycles/{billingCycle}/close', [BillingManagementController::class, 'closeCycle'])->name('billing-cycles.close');
    Route::post('/billing/generate', [BillingManagementController::class, 'generate'])->name('billing.generate');
    Route::post('/invoices/{invoice}/void', [BillingManagementController::class, 'voidInvoice'])->name('invoices.void');
    Route::get('/invoices/{invoice}/print', [BillingManagementController::class, 'printInvoice'])->name('invoices.print');

    Route::get('/payments', PaymentsPage::class)->name('payments.index');
    Route::get('/payments/create', PaymentFormPage::class)->name('payments.create');
    Route::post('/payments', [PaymentManagementController::class, 'store'])->name('payments.store');
    Route::put('/payments/{payment}', [PaymentManagementController::class, 'update'])->name('payments.update');
    Route::get('/payments/{payment}/receipt', [PaymentManagementController::class, 'printReceipt'])->name('payments.receipt');

    Route::get('/expenses', ExpensesPage::class)->name('expenses.index');
    Route::get('/cash-ledger', fn () => redirect()->route('expenses.index'))->name('cash-ledger.index');
    Route::post('/cash-accounts', [CashManagementController::class, 'storeCashAccount'])->name('cash-accounts.store');
    Route::put('/cash-accounts/{cashAccount}', [CashManagementController::class, 'updateCashAccount'])->name('cash-accounts.update');
    Route::post('/expense-categories', [CashManagementController::class, 'storeExpenseCategory'])->name('expense-categories.store');
    Route::post('/expenses', [CashManagementController::class, 'storeExpense'])->name('expenses.store');
    Route::put('/expenses/{expense}', [CashManagementController::class, 'updateExpense'])->name('expenses.update');
    Route::delete('/expenses/{expense}', [CashManagementController::class, 'destroyExpense'])->name('expenses.destroy');

    Route::get('/users', UsersPage::class)->name('users.index');
    Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
    Route::put('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');

    Route::get('/reports', ReportsPage::class)->name('reports.index');
    Route::get('/reports/export', [ApiReportController::class, 'export'])->name('reports.export');

    Route::get('/audit-logs', AuditLogsPage::class)->name('audit-logs.index');
});
