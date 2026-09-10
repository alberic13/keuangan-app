<!DOCTYPE html>
<html class="light" lang="id">
@include('layouts.partials.head')
<body class="bg-surface-bright text-on-surface min-h-screen">
<div class="flex min-h-screen">
    @include('layouts.partials.sidebar')

    <main class="app-main flex-1 min-h-screen">
        @include('layouts.partials.header')

        <div class="app-content-shell px-4 pb-12 pt-24 sm:px-6 lg:px-8">
            @include('layouts.partials.alerts')

            {{ $slot }}
        </div>
    </main>
</div>
@livewireScripts
@include('layouts.partials.scripts')
</body>
</html>
