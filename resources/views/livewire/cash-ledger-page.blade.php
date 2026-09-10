@php
    $activeSection = $activeSection ?? 'ledger';
    $accountTypeLabels = [
        'cash' => 'Tunai',
        'bank' => 'Bank',
    ];
    $sectionLinks = [
        'ledger' => route('cash-ledger.index', ['section' => 'ledger']),
        'expenses' => route('cash-ledger.index', ['section' => 'expenses']),
    ];
@endphp

<div class="space-y-8">
    <div class="bg-surface-container-lowest rounded-xl shadow-sm p-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h3 class="text-lg font-headline font-bold text-on-surface">Kelola Arus Kas Sekolah</h3>
                <p class="text-sm text-on-surface-variant">Gunakan tombol mode di bawah untuk berpindah antara pemantauan buku kas dan pencatatan kas keluar.</p>
            </div>
            <div class="inline-flex w-full flex-col gap-3 rounded-2xl bg-surface-container-low p-2 sm:w-auto sm:flex-row">
                <a class="inline-flex items-center justify-center rounded-xl px-5 py-3 text-sm font-semibold transition-all {{ $activeSection === 'ledger' ? 'bg-primary text-white shadow-sm' : 'text-on-surface hover:bg-surface-container-high' }}" href="{{ $sectionLinks['ledger'] }}">
                    Buku Kas
                </a>
                <a class="inline-flex items-center justify-center rounded-xl px-5 py-3 text-sm font-semibold transition-all {{ $activeSection === 'expenses' ? 'bg-primary text-white shadow-sm' : 'text-on-surface hover:bg-surface-container-high' }}" href="{{ $sectionLinks['expenses'] }}">
                    Kas Keluar
                </a>
            </div>
        </div>
    </div>

    @if ($activeSection === 'ledger')
        @include('livewire.partials.cash-ledger.ledger-filter')
        @include('livewire.partials.cash-ledger.ledger-table')
    @else
        @include('livewire.partials.cash-ledger.expense-form')
        @include('livewire.partials.cash-ledger.expense-table')
    @endif
</div>
