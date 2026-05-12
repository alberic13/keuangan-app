<?php

namespace App\Livewire;

use App\Models\Batch;
use App\Models\FeeScheme;
use App\Models\FeeType;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class FeesPage extends Component
{
    public function render()
    {
        $search = trim((string) request('search'));
        $editingFeeType = request('edit_fee_type')
            ? FeeType::query()->find(request('edit_fee_type'))
            : null;
        $editingFeeScheme = request('edit_fee_scheme')
            ? FeeScheme::query()->with('feeType')->find(request('edit_fee_scheme'))
            : null;

        return view('livewire.fees-page', [
            'editingFeeType' => $editingFeeType,
            'editingFeeScheme' => $editingFeeScheme,
            'feeTypes' => FeeType::query()
                ->when($search !== '', function (Builder $query) use ($search) {
                    $query->where(function (Builder $feeTypeQuery) use ($search) {
                        $feeTypeQuery
                            ->where('code', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%")
                            ->orWhere('category', 'like', "%{$search}%")
                            ->orWhere('billing_frequency', 'like', "%{$search}%")
                            ->orWhere('applies_to', 'like', "%{$search}%");
                    });
                })
                ->latest()
                ->get(),
            'feeTypeOptions' => FeeType::query()
                ->orderBy('name')
                ->get(),
            'feeSchemes' => FeeScheme::query()
                ->with('feeType')
                ->where('is_active', true)
                ->when($search !== '', function (Builder $query) use ($search) {
                    $query->where(function (Builder $schemeQuery) use ($search) {
                        $schemeQuery
                            ->where('nominal', 'like', "%{$search}%")
                            ->orWhereHas('feeType', function (Builder $feeTypeQuery) use ($search) {
                                $feeTypeQuery
                                    ->where('name', 'like', "%{$search}%")
                                    ->orWhere('code', 'like', "%{$search}%");
                            })
                            ->orWhereHas('batch', function (Builder $batchQuery) use ($search) {
                                $batchQuery->where('academic_year', 'like', "%{$search}%");
                            });
                    });
                })
                ->latest('effective_start')
                ->limit(30)
                ->get(),
            'batches' => Batch::query()->orderByDesc('academic_year')->get(),
        ])->layout('layouts.app', [
            'pageTitle' => 'Master Bayar',
            'pageHeading' => 'Master Bayar',
            'activeNav' => 'fees',
            'searchPlaceholder' => 'Cari master bayar...',
        ]);
    }
}
