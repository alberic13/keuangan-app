<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <title>Login | E-Keuangan MAN 2 Surakarta</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&amp;family=Inter:wght@400;500;600&amp;display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        background: "#f7f9ff",
                        primary: "#00422f",
                        "primary-container": "#005c42",
                        "primary-fixed-dim": "#8bd6b4",
                        "surface-container-lowest": "#ffffff",
                        "surface-container-high": "#e5e8ee",
                        "on-surface": "#181c20",
                        "on-surface-variant": "#3f4943"
                    },
                    fontFamily: {
                        headline: ["Manrope"],
                        body: ["Inter"]
                    }
                }
            }
        };
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3 { font-family: 'Manrope', sans-serif; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .bg-pattern {
            background-color: #f7f9ff;
            background-image: radial-gradient(#00422f 0.5px, transparent 0.5px);
            background-size: 24px 24px;
            opacity: 0.03;
        }
    </style>
</head>
<body class="bg-background text-on-surface min-h-screen flex items-center justify-center p-4 sm:p-8">
<div class="fixed inset-0 bg-pattern pointer-events-none"></div>
<main class="relative w-full max-w-6xl grid grid-cols-1 lg:grid-cols-12 overflow-hidden bg-surface-container-lowest rounded-xl shadow-[0_12px_40px_rgba(27,107,80,0.06)]">
    <section class="lg:col-span-7 hidden lg:flex flex-col justify-between p-12 bg-primary text-white">
        <div class="flex items-center gap-3">
            <div class="w-14 h-14 rounded-xl bg-white flex items-center justify-center shadow-lg shadow-black/10 overflow-hidden">
                <img alt="Logo MAN 2 Surakarta" class="h-12 w-12 object-contain" src="{{ asset('images/man2-logo.png') }}">
            </div>
            <div>
                <span class="font-headline font-extrabold text-xl leading-none block">E-Keuangan</span>
                <span class="text-primary-fixed-dim text-xs tracking-widest">MAN 2 SURAKARTA</span>
            </div>
        </div>
        <div class="max-w-md">
            <h1 class="text-4xl font-headline font-bold leading-tight mb-6">
                Integritas dalam Pengelolaan, Kecemerlangan dalam Pendidikan
            </h1>
            <p class="text-primary-fixed-dim text-lg leading-relaxed">
                Sistem manajemen keuangan terintegrasi untuk mendukung administrasi pendidikan yang transparan, rapi, dan siap audit.
            </p>
        </div>
        <div class="flex gap-4">
            <div class="h-1 w-12 bg-primary-fixed-dim rounded-full"></div>
            <div class="h-1 w-4 bg-primary-fixed-dim/30 rounded-full"></div>
            <div class="h-1 w-4 bg-primary-fixed-dim/30 rounded-full"></div>
        </div>
    </section>
    <section class="lg:col-span-5 flex flex-col justify-center p-8 sm:p-12 lg:p-16 bg-surface-container-lowest">
        <div class="mb-8">
            <h3 class="text-on-surface text-3xl font-headline font-bold tracking-tight mb-2">Selamat Datang</h3>
            <p class="text-on-surface-variant">Silakan masuk untuk mengakses dashboard manajemen keuangan institusi.</p>
        </div>

        @if ($errors->any())
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login.store') }}" class="space-y-6" method="POST">
            @csrf
            <div class="space-y-1.5">
                <label class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest block ml-1" for="login">Username atau Email</label>
                <input class="block w-full px-4 py-3.5 bg-surface-container-high border-none border-b-2 border-transparent focus:border-primary focus:ring-0 rounded-xl transition-all font-body text-on-surface placeholder:text-slate-400" id="login" name="login" placeholder="admin@man2surakarta.sch.id" required type="text" value="{{ old('login') }}">
            </div>
            <div class="space-y-1.5">
                <label class="text-on-surface-variant text-xs font-semibold uppercase tracking-widest block ml-1" for="password">Kata Sandi</label>
                <input class="block w-full px-4 py-3.5 bg-surface-container-high border-none border-b-2 border-transparent focus:border-primary focus:ring-0 rounded-xl transition-all font-body text-on-surface placeholder:text-slate-400" id="password" name="password" placeholder="••••••••••••" required type="password">
            </div>
            <div class="flex items-center px-1">
                <input class="w-4 h-4 text-primary bg-surface-container-high border-slate-300 rounded focus:ring-primary" id="remember" name="remember" type="checkbox" value="1">
                <label class="ml-2 text-sm font-medium text-on-surface-variant" for="remember">Ingat saya di perangkat ini</label>
            </div>
            <button class="w-full py-4 px-6 bg-gradient-to-br from-primary to-primary-container text-white font-headline font-bold rounded-xl shadow-lg shadow-primary/10 hover:shadow-primary/20 transition-all flex items-center justify-center gap-2" type="submit">
                Masuk ke Sistem
                <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
            </button>
        </form>
    </section>
</main>
</body>
</html>
