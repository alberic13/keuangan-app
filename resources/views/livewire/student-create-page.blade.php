<div class="space-y-8">
    @if (session('status'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900">
            {{ session('status') }}
        </div>
    @endif

    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h3 class="text-lg font-headline font-bold text-on-surface">Form Tambah Siswa Manual</h3>
            <p class="text-sm text-on-surface-variant">Gunakan halaman ini untuk input satu siswa baru secara manual tanpa bercampur dengan tabel daftar siswa.</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a class="inline-flex items-center rounded-xl border border-slate-200 px-5 py-3 text-sm font-semibold text-slate-700 transition-colors hover:bg-slate-50" href="{{ route('students.index') }}">
                Kembali ke Daftar Siswa
            </a>
            <a class="inline-flex items-center rounded-xl bg-surface-container-low px-5 py-3 text-sm font-semibold text-on-surface transition-colors hover:bg-surface-container-high" href="{{ route('imports.students') }}">
                Ke Import Siswa
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <div class="lg:col-span-8">
            @include('livewire.partials.students.student-form')
        </div>

        @include('livewire.partials.students.reference-manager')
    </div>
</div>
