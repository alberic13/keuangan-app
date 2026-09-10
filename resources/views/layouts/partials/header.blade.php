@php
    $roleLabels = [
        'admin_keuangan' => 'Admin Keuangan',
        'bendahara' => 'Bendahara',
        'kepala_madrasah' => 'Kepala Madrasah',
        'waka' => 'Waka',
        'admin_tu' => 'Admin TU',
    ];
@endphp

<header class="app-header fixed top-0 left-0 right-0 z-30 bg-white/80 glass-header shadow-sm px-4 md:px-8 py-3 grid grid-cols-[auto,minmax(0,1fr),auto] items-center gap-3 md:gap-3">
    <div class="flex items-center">
        <button class="inline-flex items-center justify-center rounded-xl p-2 text-slate-500 hover:bg-emerald-50/70" data-sidebar-toggle title="Tampilkan atau sembunyikan sidebar" aria-label="Tampilkan atau sembunyikan sidebar" type="button">
            <span class="material-symbols-outlined">menu</span>
        </button>
    </div>
    <div class="app-header-center flex min-w-0">
        <div class="app-header-title min-w-0 max-w-full">
            <h2 class="truncate font-headline text-base font-semibold tracking-tight text-emerald-900 sm:text-lg md:text-[1.35rem]">
                {{ $pageHeading ?? $pageTitle ?? 'E-Keuangan' }}
            </h2>
        </div>
    </div>
    <div class="app-header-actions flex shrink-0 items-center justify-end gap-2 sm:gap-3 md:gap-3">
        <div class="flex items-center gap-2 sm:gap-3">
            <div class="text-right hidden sm:block">
                <p class="text-xs font-bold text-emerald-900">{{ auth()->user()?->name }}</p>
                <p class="text-[10px] text-slate-500 uppercase">{{ $roleLabels[auth()->user()?->getRoleNames()->first()] ?? auth()->user()?->getRoleNames()->first() }}</p>
            </div>
            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-primary font-bold text-white">
                {{ str(auth()->user()?->name ?? 'A')->substr(0, 1) }}
            </div>
        </div>
    </div>
</header>
