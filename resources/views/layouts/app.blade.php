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
                <h1>MAN 2 Surakarta</h1>
            </div>

            <nav class="menu">
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    Dashboard Alokasi Dana
                </a>
                <a href="{{ route('expenses.create') }}" class="{{ request()->routeIs('expenses.*') ? 'active' : '' }}">
                    Input Kas Keluar
                </a>
                <a href="{{ route('reports.index') }}" class="{{ request()->routeIs('reports.*') ? 'active' : '' }}">
                    Laporan Keuangan
                </a>
            </nav>

            <div class="sidebar-note">
                <strong>Alur aman</strong>
                <p>Mulai dari input transaksi, lalu dashboard otomatis ikut berubah.</p>
            </div>

            <div class="actions" style="margin-top: 16px;">
                <form action="{{ route('logout') }}" method="POST" style="width: 100%;">
                    @csrf
                    <button type="submit" class="btn" style="width: 100%;">Logout Admin</button>
                </form>
            </div>
        </aside>

        <main class="content">
            @yield('content')
        </main>
    </div>
</body>
</html>
