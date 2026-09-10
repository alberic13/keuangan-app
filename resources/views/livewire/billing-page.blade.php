@php
    $cycleStatusLabels = [
        'open' => 'Terbuka',
        'closed' => 'Tertutup',
    ];
    $invoiceStatusLabels = [
        'unpaid' => 'Belum Lunas',
        'partial' => 'Sebagian',
        'paid' => 'Lunas',
        'void' => 'Dibatalkan',
    ];
@endphp

<div class="space-y-8">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        @include('livewire.partials.billing.cycle-form')
        @include('livewire.partials.billing.generate-form')
    </div>

    @include('livewire.partials.billing.cycle-table')
    @include('livewire.partials.billing.invoice-table')
</div>
