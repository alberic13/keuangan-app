<?php

namespace App\Livewire;

use App\Models\CashAccount;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class ExpensesPage extends Component
{
    public function render()
    {
        $search = trim((string) request('search'));
        $editingExpense = request()->filled('edit')
            ? Expense::query()->with(['category', 'paymentAccount'])->find(request('edit'))
            : null;

        return view('livewire.expenses-page', [
            'accounts' => CashAccount::query()
                ->where('is_active', true)
                ->withSum(['ledgerEntries as incoming_total' => function (Builder $query) {
                    $query->where('direction', 'in')->where('status', 'posted');
                }], 'amount')
                ->withSum(['ledgerEntries as outgoing_total' => function (Builder $query) {
                    $query->where('direction', 'out')->where('status', 'posted');
                }], 'amount')
                ->orderBy('name')
                ->get(),
            'categories' => ExpenseCategory::query()->where('is_active', true)->orderBy('name')->get(),
            'editingExpense' => $editingExpense,
            'expenses' => Expense::query()
                ->with(['category', 'paymentAccount'])
                ->when($search !== '', function (Builder $query) use ($search) {
                    $query->where(function (Builder $expenseQuery) use ($search) {
                        $expenseQuery
                            ->where('expense_no', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%")
                            ->orWhere('status', 'like', "%{$search}%")
                            ->orWhereHas('category', function (Builder $categoryQuery) use ($search) {
                                $categoryQuery->where('name', 'like', "%{$search}%");
                            })
                            ->orWhereHas('paymentAccount', function (Builder $accountQuery) use ($search) {
                                $accountQuery
                                    ->where('name', 'like', "%{$search}%")
                                    ->orWhere('account_number', 'like', "%{$search}%")
                                    ->orWhere('account_holder', 'like', "%{$search}%");
                            });
                    });
                })
                ->latest('transaction_date')
                ->limit(30)
                ->get(),
        ])->layout('layouts.app', [
            'pageTitle' => 'Pengeluaran',
            'pageHeading' => 'Manajemen Pengeluaran',
            'activeNav' => 'expenses',
            'searchPlaceholder' => 'Cari pengeluaran...',
        ]);
    }
}
