<?php

use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BillingController;
use App\Http\Controllers\Api\CashController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\FeeController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ReferenceController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\StudentImportController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/logout', [AuthController::class, 'logout'])->middleware(['auth', 'active']);
        Route::get('/me', [AuthController::class, 'me'])->middleware(['auth', 'active']);
    });

    Route::middleware(['auth', 'active'])->group(function () {
        Route::get('/batches', [ReferenceController::class, 'batches']);
        Route::post('/batches', [ReferenceController::class, 'storeBatch']);
        Route::get('/classes', [ReferenceController::class, 'classes']);
        Route::post('/classes', [ReferenceController::class, 'storeClass']);

        Route::get('/students', [StudentController::class, 'index']);
        Route::post('/students', [StudentController::class, 'store']);
        Route::get('/students/{student}', [StudentController::class, 'show']);
        Route::put('/students/{student}', [StudentController::class, 'update']);
        Route::patch('/students/{student}/deactivate', [StudentController::class, 'deactivate']);

        Route::get('/imports/students/template', [StudentImportController::class, 'template']);
        Route::post('/imports/students/preview', [StudentImportController::class, 'preview']);
        Route::post('/imports/students/commit', [StudentImportController::class, 'commit']);
        Route::get('/imports/students/logs', [StudentImportController::class, 'logs']);
        Route::get('/imports/students/logs/{importLog}', [StudentImportController::class, 'show']);

        Route::get('/fee-types', [FeeController::class, 'feeTypesIndex']);
        Route::post('/fee-types', [FeeController::class, 'storeFeeType']);
        Route::put('/fee-types/{feeType}', [FeeController::class, 'updateFeeType']);
        Route::get('/fee-schemes', [FeeController::class, 'feeSchemesIndex']);
        Route::post('/fee-schemes', [FeeController::class, 'storeFeeScheme']);
        Route::put('/fee-schemes/{feeScheme}', [FeeController::class, 'updateFeeScheme']);

        Route::get('/billing-cycles', [BillingController::class, 'cyclesIndex']);
        Route::post('/billing-cycles', [BillingController::class, 'storeCycle']);
        Route::put('/billing-cycles/{billingCycle}', [BillingController::class, 'updateCycle']);
        Route::post('/billing-cycles/{billingCycle}/close', [BillingController::class, 'closeCycle']);
        Route::post('/billing/generate', [BillingController::class, 'generate']);
        Route::get('/invoices', [BillingController::class, 'invoicesIndex']);
        Route::get('/invoices/{invoice}', [BillingController::class, 'showInvoice']);
        Route::get('/students/{student}/invoices/open', [BillingController::class, 'openByStudent']);
        Route::post('/invoices/{invoice}/void', [BillingController::class, 'voidInvoice']);

        Route::post('/payments', [PaymentController::class, 'store']);
        Route::get('/payments', [PaymentController::class, 'index']);
        Route::get('/payments/{payment}', [PaymentController::class, 'show']);
        Route::put('/payments/{payment}', [PaymentController::class, 'update']);
        Route::post('/payments/{payment}/print-receipt', [PaymentController::class, 'printReceipt']);

        Route::get('/cash-accounts', [CashController::class, 'cashAccountsIndex']);
        Route::post('/cash-accounts', [CashController::class, 'storeCashAccount']);
        Route::put('/cash-accounts/{cashAccount}', [CashController::class, 'updateCashAccount']);
        Route::get('/expense-categories', [CashController::class, 'expenseCategoriesIndex']);
        Route::post('/expense-categories', [CashController::class, 'storeExpenseCategory']);
        Route::get('/expenses', [CashController::class, 'expensesIndex']);
        Route::post('/expenses', [CashController::class, 'storeExpense']);
        Route::put('/expenses/{expense}', [CashController::class, 'updateExpense']);
        Route::get('/cash-ledger', [CashController::class, 'ledgerIndex']);

        Route::get('/reports/daily-cash', [ReportController::class, 'dailyCash']);
        Route::get('/reports/monthly-summary', [ReportController::class, 'monthlySummary']);
        Route::get('/reports/yearly-summary', [ReportController::class, 'yearlySummary']);
        Route::get('/reports/student-ledger/{student}', [ReportController::class, 'studentLedger']);
        Route::get('/reports/arrears', [ReportController::class, 'arrears']);
        Route::get('/reports/bku', [ReportController::class, 'bku']);
        Route::get('/reports/cash-book', [ReportController::class, 'cashBook']);
        Route::get('/reports/cash-receipt-book', [ReportController::class, 'cashReceiptBook']);
        Route::get('/reports/bank-receipt-book', [ReportController::class, 'bankReceiptBook']);
        Route::get('/reports/cash-bank-receipt-book', [ReportController::class, 'cashBankReceiptBook']);
        Route::get('/reports/export', [ReportController::class, 'export']);

        Route::get('/dashboard/summary', [DashboardController::class, 'summary']);
        Route::get('/dashboard/payment-trend', [DashboardController::class, 'paymentTrend']);
        Route::get('/dashboard/recent-payments', [DashboardController::class, 'recentPayments']);

        Route::get('/audit-logs', [AuditLogController::class, 'index']);
        Route::get('/audit-logs/{auditLog}', [AuditLogController::class, 'show']);

        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']);
        Route::put('/users/{user}', [UserController::class, 'update']);
    });
});
