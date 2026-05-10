<?php

namespace App\Livewire;

use App\Models\CashAccount;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Services\ReportService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Component;

class CashLedgerPage extends Component
{
    public function render(ReportService $reportService)
    {
        $activeSection = request('section') === 'expenses' ? 'expenses' : 'ledger';
        $filters = request()->only(['account_id', 'direction', 'date_from', 'date_to', 'source_type']);
        $search = trim((string) request('search'));
        $ledgerRows = collect($reportService->bku($filters))
            ->when($search !== '', function (Collection $rows) use ($search) {
                return $rows->filter(function (array $row) use ($search) {
                    return str_contains(strtolower(implode(' ', [
                        (string) ($row['date'] ?? ''),
                        (string) ($row['entry_no'] ?? ''),
                        (string) ($row['account'] ?? ''),
                        (string) ($row['source_type'] ?? ''),
                        (string) ($row['description'] ?? ''),
                    ])), strtolower($search));
                })->values();
            });

        return view('livewire.cash-ledger-page', [
            'activeSection' => $activeSection,
            'accounts' => CashAccount::query()->orderBy('name')->get(),
            'categories' => ExpenseCategory::query()->where('is_active', true)->orderBy('name')->get(),
            'ledgerRows' => $ledgerRows,
            'expenses' => Expense::query()
                ->with(['category', 'paymentAccount'])
                ->when($search !== '' && $activeSection === 'expenses', function (Builder $query) use ($search) {
                    $query->where(function (Builder $expenseQuery) use ($search) {
                        $expenseQuery
                            ->where('expense_no', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%")
                            ->orWhere('status', 'like', "%{$search}%")
                            ->orWhereHas('category', function (Builder $categoryQuery) use ($search) {
                                $categoryQuery
                                    ->where('code', 'like', "%{$search}%")
                                    ->orWhere('name', 'like', "%{$search}%");
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
            'pageTitle' => 'Kas & Pengeluaran',
            'pageHeading' => 'Kas & Pengeluaran',
            'activeNav' => 'cash-ledger',
            'searchPlaceholder' => $activeSection === 'expenses'
                ? 'Cari pengeluaran...'
                : 'Cari entri buku kas...',
        ]);
    }
}
