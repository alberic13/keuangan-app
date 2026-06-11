<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>{{ $pageTitle ?? 'E-Keuangan MAN 2 Surakarta' }}</title>
    <script>
        (() => {
            try {
                const desktopState = localStorage.getItem('sidebarDesktopState');
                document.documentElement.dataset.sidebarDesktop = desktopState === 'closed' ? 'closed' : 'open';
            } catch (error) {
                document.documentElement.dataset.sidebarDesktop = 'open';
            }

            document.documentElement.dataset.sidebarMobile = 'closed';
        })();
    </script>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&amp;family=Inter:wght@300;400;500;600&amp;display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "surface-bright": "#f7f9ff",
                        "surface-variant": "#dfe3e8",
                        "on-surface-variant": "#3f4943",
                        "primary-container": "#005c42",
                        "surface-container-low": "#f1f4fa",
                        "secondary-container": "#cfe5d9",
                        "primary-fixed-dim": "#8bd6b4",
                        "surface-container-high": "#e5e8ee",
                        "surface": "#f7f9ff",
                        "background": "#f7f9ff",
                        "surface-container": "#ebeef4",
                        "error": "#ba1a1a",
                        "outline": "#6f7973",
                        "tertiary": "#622621",
                        "on-surface": "#181c20",
                        "secondary": "#4f6359",
                        "primary-fixed": "#a6f2d0",
                        "surface-container-lowest": "#ffffff",
                        "secondary-fixed": "#d2e7dc",
                        "on-background": "#181c20",
                        "on-primary": "#ffffff",
                        "error-container": "#ffdad6",
                        "surface-dim": "#d7dae0",
                        "primary": "#00422f"
                    },
                    borderRadius: {
                        DEFAULT: "0.125rem",
                        lg: "0.25rem",
                        xl: "0.5rem",
                        full: "0.75rem"
                    },
                    fontFamily: {
                        headline: ["Manrope"],
                        body: ["Inter"],
                        label: ["Inter"]
                    }
                },
            },
        };
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3, .font-headline { font-family: 'Manrope', sans-serif; }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .glass-header {
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
        .app-sidebar,
        .app-main,
        .app-header,
        .app-sidebar-overlay {
            transition: transform 220ms ease, left 220ms ease, margin-left 220ms ease, opacity 220ms ease;
        }
        .app-content-shell {
            width: 100%;
            max-width: 1440px;
            margin-inline: auto;
        }
        .app-header-center {
            justify-content: flex-start;
            text-align: left;
            transition: justify-content 220ms ease, text-align 220ms ease, padding 220ms ease;
        }
        .app-header-title {
            display: block;
            max-width: min(100%, calc(100vw - 9rem));
            width: auto;
            padding: 0;
            background: transparent;
            box-shadow: none;
            transition: width 220ms ease, max-width 220ms ease;
        }
        .app-header-actions {
            min-width: 0;
        }
        .app-header-search {
            width: 15rem;
            transition: width 220ms ease, opacity 220ms ease;
        }
        .app-sidebar {
            transform: translateX(-100%);
        }
        .app-sidebar-overlay {
            opacity: 0;
            pointer-events: none;
        }
        html[data-sidebar-mobile="open"] .app-sidebar {
            transform: translateX(0);
        }
        html[data-sidebar-mobile="open"] .app-sidebar-overlay {
            opacity: 1;
            pointer-events: auto;
        }
        @media (min-width: 768px) {
            .app-sidebar {
                transform: translateX(0);
            }
            .app-main {
                margin-left: 18rem;
            }
            .app-header {
                left: 18rem;
            }
            .app-sidebar-overlay {
                display: none;
            }
            html[data-sidebar-desktop="closed"] .app-sidebar {
                transform: translateX(-100%);
            }
            html[data-sidebar-desktop="closed"] .app-main {
                margin-left: 0;
            }
            html[data-sidebar-desktop="closed"] .app-header {
                left: 0;
            }
            html[data-sidebar-desktop="closed"] .app-header-center {
                padding-left: 0;
            }
            html[data-sidebar-desktop="closed"] .app-header-title {
                width: auto;
                max-width: min(44rem, calc(100vw - 18rem));
            }
            html[data-sidebar-desktop="open"] .app-header-center {
                justify-content: center;
                text-align: center;
                padding-inline: clamp(0.5rem, 1.6vw, 1.25rem);
            }
            html[data-sidebar-desktop="open"] .app-header-title {
                width: min(21rem, calc(100vw - 33rem));
                max-width: min(21rem, calc(100vw - 33rem));
            }
            html[data-sidebar-desktop="open"] .app-header-search {
                width: 10.75rem;
            }
        }
        @media (min-width: 1280px) {
            .app-header-search {
                width: 17rem;
            }
            html[data-sidebar-desktop="open"] .app-header-search {
                width: 11.75rem;
            }
        }
        @media (max-width: 1023px) {
            .app-header-title {
                max-width: calc(100vw - 8rem);
            }
        }
    </style>
    @livewireStyles
</head>
<body class="bg-surface-bright text-on-surface min-h-screen">
<div class="flex min-h-screen">
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

        // Filter navigation items based on user role
        $user = auth()->user();
        if ($user && $user->hasRole('bendahara')) {
            // Bendahara can only see: payments, expenses, and reports
            $navItems = array_filter($navItems, function($item) {
                return in_array($item['key'], ['payments', 'expenses', 'reports']);
            });
        }

        $roleLabels = [
            'admin_keuangan' => 'Admin Keuangan',
            'bendahara' => 'Bendahara',
            'kepala_madrasah' => 'Kepala Madrasah',
            'waka' => 'Waka',
            'admin_tu' => 'Admin TU',
        ];
    @endphp

    @php
        $searchQueryParams = request()->except(['search', 'page']);
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

    <main class="app-main flex-1 min-h-screen">
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
                <form action="{{ request()->url() }}" class="app-header-search hidden xl:flex items-center gap-2 rounded-full bg-surface-container-low px-4 py-2" method="GET">
                    @foreach ($searchQueryParams as $queryKey => $queryValue)
                        @if (is_array($queryValue))
                            @foreach ($queryValue as $nestedKey => $nestedValue)
                                <input name="{{ $queryKey }}[{{ $nestedKey }}]" type="hidden" value="{{ $nestedValue }}">
                            @endforeach
                        @else
                            <input name="{{ $queryKey }}" type="hidden" value="{{ $queryValue }}">
                        @endif
                    @endforeach
                    <span class="material-symbols-outlined text-slate-400 text-lg">search</span>
                    <input
                        class="w-full border-none bg-transparent p-0 text-sm text-slate-700 placeholder:text-slate-400 focus:ring-0"
                        name="search"
                        placeholder="{{ $searchPlaceholder ?? 'Cari...' }}"
                        type="text"
                        value="{{ request('search') }}"
                    >
                    @if (filled(request('search')))
                        <a class="text-xs font-semibold text-slate-500 hover:text-primary" href="{{ request()->url() }}{{ count($searchQueryParams) ? '?'.http_build_query($searchQueryParams) : '' }}">
                            Atur Ulang
                        </a>
                    @endif
                </form>
                <div class="flex items-center gap-1.5 sm:gap-2 md:gap-3">
                    <button class="rounded-full p-2 text-slate-500 transition-colors hover:bg-emerald-50/50" type="button">
                        <span class="material-symbols-outlined">notifications</span>
                    </button>
                    <button class="rounded-full p-2 text-slate-500 transition-colors hover:bg-emerald-50/50" type="button">
                        <span class="material-symbols-outlined">settings</span>
                    </button>
                    <div class="mx-1 hidden h-8 w-px bg-slate-200 sm:block"></div>
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
            </div>
        </header>

        <div class="app-content-shell px-4 pb-12 pt-24 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-900">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-900">
                    <p class="font-semibold mb-1">Ada input yang perlu diperbaiki:</p>
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{ $slot }}
        </div>
    </main>
</div>
@livewireScripts
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const root = document.documentElement;
        const overlay = document.querySelector('.app-sidebar-overlay');
        const toggleButtons = document.querySelectorAll('[data-sidebar-toggle]');
        const closeButtons = document.querySelectorAll('[data-sidebar-close]');
        const mobileQuery = window.matchMedia('(max-width: 767px)');
        const isMobile = () => mobileQuery.matches;

        const setDesktopState = (state) => {
            root.dataset.sidebarDesktop = state;
            try {
                localStorage.setItem('sidebarDesktopState', state);
            } catch (error) {
                // Ignore storage access issues.
            }
        };

        const setMobileState = (state) => {
            root.dataset.sidebarMobile = state;
        };

        toggleButtons.forEach((button) => {
            button.addEventListener('click', () => {
                if (isMobile()) {
                    setMobileState(root.dataset.sidebarMobile === 'open' ? 'closed' : 'open');
                    return;
                }

                setDesktopState(root.dataset.sidebarDesktop === 'closed' ? 'open' : 'closed');
            });
        });

        closeButtons.forEach((button) => {
            button.addEventListener('click', () => setMobileState('closed'));
        });

        overlay?.addEventListener('click', () => setMobileState('closed'));

        mobileQuery.addEventListener('change', (event) => {
            if (! event.matches) {
                setMobileState('closed');
            }
        });
    });
</script>
</body>
</html>
