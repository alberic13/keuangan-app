<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Sistem Keuangan MAN 2' }}</title>
    <style>{!! file_get_contents(resource_path('css/app.css')) !!}</style>
</head>
<body>
    <div class="shell">
        <aside class="sidebar">
            <div class="brand">
                <div class="brand-head">
                    <img src="{{ asset('images/man2-logo.png') }}" alt="Logo MAN 2 Surakarta" class="brand-logo">
                    <div class="brand-copy">
                        <p>Dashboard, kas keluar, dan laporan.</p>
                    </div>
                </div>
            </div>

            <nav class="menu">
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    Dashboard
                </a>
                <a href="{{ route('expenses.create') }}" class="{{ request()->routeIs('expenses.*') ? 'active' : '' }}">
                    Kas Keluar
                </a>
                <a href="{{ route('reports.index') }}" class="{{ request()->routeIs('reports.*') ? 'active' : '' }}">
                    Laporan
                </a>
            </nav>

            <div class="sidebar-note">
                <strong>Singkat</strong>
                <p>Input sekali, data langsung sinkron.</p>
            </div>

            <div class="actions" style="margin-top: 16px;">
                <form action="{{ route('logout') }}" method="POST" style="width: 100%;">
                    @csrf
                    <button type="submit" class="btn" style="width: 100%;">Logout</button>
                </form>
            </div>
        </aside>

        <main class="content">
            @yield('content')
        </main>
    </div>
</body>
</html>
