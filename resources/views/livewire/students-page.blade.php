@php
    $studentTypeLabels = [
        'regular' => 'Reguler',
        'boarding' => 'Asrama',
    ];
    $baseQuery = request()->except(['page']);
@endphp

<div class="space-y-8">
    <div class="bg-surface-container-lowest rounded-xl shadow-sm p-6">
        <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h3 class="text-lg font-headline font-bold text-on-surface">Data Siswa</h3>
                <p class="text-sm text-on-surface-variant">Kelola data siswa aktif dan nonaktif dari satu halaman yang lebih lapang.</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a class="inline-flex items-center rounded-xl bg-primary px-5 py-3 text-sm font-semibold text-white transition-colors hover:bg-primary-container" href="{{ route('students.create') }}">
                    Tambah Siswa Manual
                </a>
                <a class="inline-flex items-center rounded-xl bg-surface-container-low px-5 py-3 text-sm font-semibold text-on-surface transition-colors hover:bg-surface-container-high" href="{{ route('imports.students') }}">
                    Import Data Siswa
                </a>
            </div>
        </div>

        <form class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6" method="GET">
            <input class="md:col-span-2 rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="search" placeholder="Cari nama/NIS/NISN" type="text" value="{{ request('search') }}">
            <select class="rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="batch_id">
                <option value="">Semua Angkatan</option>
                @foreach ($batches as $batch)
                    <option @selected((string) request('batch_id') === (string) $batch->id) value="{{ $batch->id }}">{{ $batch->academic_year }}</option>
                @endforeach
            </select>
            <select class="rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="class_id">
                <option value="">Semua Kelas</option>
                @foreach ($classes as $class)
                    <option @selected((string) request('class_id') === (string) $class->id) value="{{ $class->id }}">{{ $class->name }}</option>
                @endforeach
            </select>
            <select class="rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="major_id">
                <option value="">Semua Jurusan</option>
                @foreach ($majors as $major)
                    <option @selected((string) request('major_id') === (string) $major->id) value="{{ $major->id }}">{{ $major->name }}</option>
                @endforeach
            </select>
            <select class="rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="student_type">
                <option value="">Semua Tipe</option>
                <option @selected(request('student_type') === 'regular') value="regular">Reguler</option>
                <option @selected(request('student_type') === 'boarding') value="boarding">Asrama</option>
            </select>
            <button class="rounded-xl bg-primary text-white font-semibold px-5 py-3 text-sm" type="submit">Filter</button>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-surface-container-low">
                <tr>
                    <th class="px-8 py-4 text-xs uppercase tracking-widest text-slate-500">NIS</th>
                    <th class="px-8 py-4 text-xs uppercase tracking-widest text-slate-500">NISN</th>
                    <th class="px-8 py-4 text-xs uppercase tracking-widest text-slate-500">Nama</th>
                    <th class="px-8 py-4 text-xs uppercase tracking-widest text-slate-500">Kelas</th>
                    <th class="px-8 py-4 text-xs uppercase tracking-widest text-slate-500">Jurusan</th>
                    <th class="px-8 py-4 text-xs uppercase tracking-widest text-slate-500">Angkatan</th>
                    <th class="px-8 py-4 text-xs uppercase tracking-widest text-slate-500">Tipe</th>
                    <th class="px-8 py-4 text-xs uppercase tracking-widest text-slate-500">Status</th>
                    <th class="px-8 py-4 text-xs uppercase tracking-widest text-slate-500">Aksi</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-surface-container">
                @forelse ($students as $student)
                    <tr>
                        <td class="px-8 py-4 text-sm">{{ $student->nis ?: '-' }}</td>
                        <td class="px-8 py-4 text-sm">{{ $student->nisn ?: '-' }}</td>
                        <td class="px-8 py-4 text-sm font-semibold">{{ $student->full_name }}</td>
                        <td class="px-8 py-4 text-sm">{{ $student->classRoom->name }}</td>
                        <td class="px-8 py-4 text-sm">{{ $student->major->name }}</td>
                        <td class="px-8 py-4 text-sm">{{ $student->batch->academic_year }}</td>
                        <td class="px-8 py-4 text-sm">{{ $studentTypeLabels[$student->student_type] ?? ucfirst($student->student_type) }}</td>
                        <td class="px-8 py-4 text-sm">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $student->is_active ? 'bg-emerald-100 text-emerald-900' : 'bg-red-100 text-red-900' }}">
                                {{ $student->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-8 py-4">
                            <div class="flex flex-wrap gap-3">
                                @if ($student->is_active)
                                    <form action="{{ route('students.deactivate', $student) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button class="inline-flex items-center rounded-lg bg-red-100 px-3 py-2 text-sm font-semibold text-red-800 transition-colors hover:bg-red-200" type="submit">Nonaktifkan</button>
                                    </form>
                                @else
                                    <form action="{{ route('students.activate', $student) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button class="inline-flex items-center rounded-lg bg-emerald-100 px-3 py-2 text-sm font-semibold text-emerald-800 transition-colors hover:bg-emerald-200" type="submit">Aktifkan</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td class="px-8 py-6 text-sm text-on-surface-variant" colspan="9">Belum ada data siswa.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
