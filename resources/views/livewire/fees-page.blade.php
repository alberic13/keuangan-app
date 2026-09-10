@php
    $categoryLabels = [
        'spp' => 'SPP',
        'activity' => 'Kegiatan',
        'meal' => 'Makan',
        'other' => 'Lainnya',
    ];
    $isEditingFeeType = $editingFeeType !== null;
    $isEditingFeeScheme = $editingFeeScheme !== null;
    $feeTypeFormAction = $isEditingFeeType ? route('fee-types.update', $editingFeeType) : route('fee-types.store');
    $feeSchemeFormAction = $isEditingFeeScheme ? route('fee-schemes.update', $editingFeeScheme) : route('fee-schemes.store');
    $baseQuery = request()->except(['edit_fee_type', 'edit_fee_scheme', 'page']);
    $canManageFees = auth()->user()?->hasRole('admin_keuangan');
@endphp

<div class="space-y-8">
    @if ($canManageFees)
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            @include('livewire.partials.fees.fee-type-form')
            @include('livewire.partials.fees.fee-scheme-form')
        </div>
    @endif

    <div class="space-y-8">
        @include('livewire.partials.fees.fee-type-table')
        @include('livewire.partials.fees.fee-scheme-table')
    </div>
</div>
