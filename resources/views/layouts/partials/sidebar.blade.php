@php
    $navItems = [
        ['key' => 'dashboard', 'label' => 'Dasbor', 'href' => route('dashboard'), 'icon' => 'dashboard'],
        ['key' => 'students', 'label' => 'Manajemen Siswa', 'href' => route('students.index'), 'icon' => 'group'],
        ['key' => 'fees', 'label' => 'Master Bayar', 'href' => route('fees.index'), 'icon' => 'sell'],
        ['key' => 'billing', 'label' => 'Manajemen Tagihan', 'href' => route('billing.index'), 'icon' => 'receipt_long'],
        ['key' => 'payments', 'label' => 'Pembayaran', 'href' => route('payments.index'), 'icon' => 'payments'],
        ['key' => 'expenses', 'label' => 'Pengeluaran', 'href' => route('expenses.index'), 'icon' => 'account_balance_wallet'],
        ['key' => 'audit-logs', 'label' => 'Log Audit', 'href' => route('audit-logs.index'), 'icon' => 'history_edu'],
        ['key' => 'users', 'label' => 'Manajemen Pengguna', 'href' => route('users.index'), 'icon' => 'manage_accounts'],
        ['key' => 'reports', 'label' => 'Laporan', 'href' => route('reports.index'), 'icon' => 'analytics'],
    ];

    $user = auth()->user();
    if ($user && $user->hasRole('bendahara')) {
        $navItems = array_filter($navItems, function($item) {
            return in_array($item['key'], ['payments', 'expenses', 'reports']);
        });
    }
@endphp

<div class="app-sidebar-overlay fixed inset-0 z-30 bg-slate-950/30 md:hidden"></div>

<aside class="app-sidebar flex flex-col h-screen w-72 bg-slate-50 fixed left-0 top-0 z-40 py-6 shadow-xl md:shadow-none">
    <div class="px-6 mb-10 flex items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-xl bg-white flex items-center justify-center shadow-lg shadow-emerald-900/10 ring-1 ring-emerald-900/10 overflow-hidden">
                <img alt="Logo MAN 2 Surakarta" class="h-11 w-11 object-contain" src="{{ asset('images/man2-logo.png') }}">
            </div>
            <div>
                <h1 class="text-lg font-black text-emerald-900 leading-tight">E-Keuangan</h1>
                <p class="text-xs text-slate-500 uppercase tracking-widest">MAN 2 Surakarta</p>
            </div>
        </div>
        <button class="inline-flex items-center justify-center rounded-xl p-2 text-slate-500 hover:bg-slate-200 md:hidden" data-sidebar-close title="Tutup sidebar" aria-label="Tutup sidebar" type="button">
            <span class="material-symbols-outlined">close</span>
        </button>
        <button class="hidden items-center justify-center rounded-xl p-2 text-slate-500 hover:bg-slate-200 md:inline-flex" data-sidebar-toggle title="Sembunyikan sidebar" aria-label="Sembunyikan sidebar" type="button">
            <span class="material-symbols-outlined">menu</span>
        </button>
    </div>
    <nav class="flex-1 space-y-1 px-2 overflow-y-auto">
        @foreach ($navItems as $item)
            <a class="flex items-center gap-3 px-4 py-3 rounded-lg mx-2 transition-all {{ ($activeNav ?? '') === $item['key'] ? 'bg-emerald-100 text-emerald-900 translate-x-1' : 'text-slate-600 hover:bg-slate-200' }}" href="{{ $item['href'] }}">
                <span class="material-symbols-outlined">{{ $item['icon'] }}</span>
                <span class="text-sm font-medium">{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>
    <div class="mt-auto px-2">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button class="w-full text-left flex items-center gap-3 px-4 py-3 text-tertiary hover:bg-red-50 rounded-lg transition-all mx-2" type="submit">
                <span class="material-symbols-outlined">logout</span>
                <span class="text-sm font-medium">Keluar</span>
            </button>
        </form>
    </div>
</aside>
