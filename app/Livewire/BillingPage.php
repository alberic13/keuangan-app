<?php

namespace App\Livewire;

use App\Models\AcademicClass;
use App\Models\Batch;
use App\Models\BillingCycle;
use App\Models\FeeType;
use App\Models\Invoice;
use App\Models\StudentType;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class BillingPage extends Component
{
    public function render()
    {
        $search = trim((string) request('search'));

        return view('livewire.billing-page', [
            'cycles' => BillingCycle::query()
                ->when($search !== '', function (Builder $query) use ($search) {
                    $query->where(function (Builder $cycleQuery) use ($search) {
                        $cycleQuery
                            ->where('period_label', 'like', "%{$search}%")
                            ->orWhere('status', 'like', "%{$search}%")
                            ->orWhereDate('due_date', $search);
                    });
                })
                ->latest('year')
                ->latest('month')
                ->get(),
            'feeTypes' => FeeType::query()
                ->where('is_active', true)
                ->when($search !== '', function (Builder $query) use ($search) {
                    $query->where(function (Builder $feeTypeQuery) use ($search) {
                        $feeTypeQuery
                            ->where('code', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%")
                            ->orWhere('category', 'like', "%{$search}%");
                    });
                })
                ->orderBy('name')
                ->get(),
            'invoices' => Invoice::query()
                ->with(['student.batch', 'feeType', 'billingCycle'])
                ->when($search !== '', function (Builder $query) use ($search) {
                    $query->where(function (Builder $invoiceQuery) use ($search) {
                        $invoiceQuery
                            ->where('invoice_no', 'like', "%{$search}%")
                            ->orWhere('reference_name', 'like', "%{$search}%")
                            ->orWhere('status', 'like', "%{$search}%")
                            ->orWhereHas('student', function (Builder $studentQuery) use ($search) {
                                $studentQuery
                                    ->where('full_name', 'like', "%{$search}%")
                                    ->orWhere('nis', 'like', "%{$search}%")
                                    ->orWhere('nisn', 'like', "%{$search}%");
                            })
                            ->orWhereHas('feeType', function (Builder $feeTypeQuery) use ($search) {
                                $feeTypeQuery
                                    ->where('name', 'like', "%{$search}%")
                                    ->orWhere('code', 'like', "%{$search}%");
                            })
                            ->orWhereHas('billingCycle', function (Builder $cycleQuery) use ($search) {
                                $cycleQuery->where('period_label', 'like', "%{$search}%");
                            });
                    });
                })
                ->latest()
                ->limit(30)
                ->get(),
            'batches' => Batch::query()->orderByDesc('academic_year')->get(),
            'classes' => AcademicClass::query()->orderBy('level')->orderBy('name')->get(),
            'studentTypes' => StudentType::query()->where('is_active', true)->orderBy('label')->get(),
        ])->layout('layouts.app', [
            'pageTitle' => 'Manajemen Tagihan',
            'pageHeading' => 'Manajemen Tagihan',
            'activeNav' => 'billing',
            'searchPlaceholder' => 'Cari invoice atau siswa...',
        ]);
    }
}
