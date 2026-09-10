<div class="bg-surface-container-lowest rounded-xl shadow-sm p-6">
    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
        <h3 class="text-lg font-headline font-bold mb-4">Pilih Siswa</h3>
        <span class="rounded-full bg-secondary-container px-3 py-1 text-xs font-semibold text-on-surface">
            {{ $students->count() }} siswa tampil
        </span>
    </div>

    <form class="mt-5 grid grid-cols-1 gap-3 lg:grid-cols-12" method="GET">
        @foreach ($paymentFilters as $filterName => $filterValue)
            <input name="{{ $filterName }}" type="hidden" value="{{ $filterValue }}">
        @endforeach

        <input class="rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm lg:col-span-4" name="student_search" placeholder="Cari nama siswa, NIS, atau angkatan" type="text" value="{{ request('student_search', request('search')) }}">

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:col-span-4">
            <select class="rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="student_class_id">
                <option value="">Semua kelas</option>
                @foreach ($classOptions as $class)
                    <option @selected((string) request('student_class_id') === (string) $class->id) value="{{ $class->id }}">
                        {{ $class->name }}
                    </option>
                @endforeach
            </select>

            <select class="rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="student_batch_id">
                <option value="">Semua angkatan</option>
                @foreach ($batchOptions as $batch)
                    <option @selected((string) request('student_batch_id') === (string) $batch->id) value="{{ $batch->id }}">
                        {{ $batch->academic_year }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex gap-3 lg:col-span-4">
            <button class="flex-1 rounded-xl bg-surface-container-low px-5 py-3 text-sm font-semibold text-primary" type="submit">Filter</button>
            <a class="rounded-xl bg-white px-5 py-3 text-sm font-semibold text-on-surface-variant shadow-sm" href="{{ route('payments.index', $paymentFilters) }}">Reset</a>
        </div>
    </form>

    <form action="{{ route('payments.create') }}" class="mt-4 grid grid-cols-1 gap-3 lg:grid-cols-12" method="GET">
        @foreach ($currentFilters as $filterName => $filterValue)
            <input name="{{ $filterName }}" type="hidden" value="{{ $filterValue }}">
        @endforeach

        <select class="rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm lg:col-span-9" name="student_id" required>
            <option value="">Pilih siswa aktif</option>
            @foreach ($students as $student)
                <option value="{{ $student->id }}">
                    {{ $student->full_name }} &bull; {{ $student->nis ?: $student->nisn ?: '-' }} &bull; {{ $student->classRoom?->name ?? '-' }} &bull; {{ $student->batch?->academic_year ?? '-' }}
                </option>
            @endforeach
        </select>

        <button class="rounded-xl bg-primary px-5 py-3.5 text-base font-bold text-white shadow-sm transition-colors hover:bg-primary-container focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 lg:col-span-3" type="submit">
            Muat Tagihan
        </button>
    </form>
</div>
