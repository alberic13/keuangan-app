@php
    $studentTypeLabels = [
        'regular' => 'Reguler',
        'boarding' => 'Asrama',
    ];
@endphp

<div class="space-y-8">
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
        <div class="lg:col-span-8 bg-surface-container-lowest rounded-xl shadow-sm p-6">
            <form action="{{ route('students.store') }}" class="space-y-6" method="POST">
                @csrf

                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-on-surface" for="full_name">Nama Lengkap</label>
                        <input class="w-full rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" id="full_name" name="full_name" placeholder="Masukkan nama lengkap siswa" required type="text" value="{{ old('full_name') }}">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-on-surface" for="nis">NIS</label>
                        <input class="w-full rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" id="nis" name="nis" placeholder="Masukkan NIS" type="text" value="{{ old('nis') }}">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-on-surface" for="nisn">NISN</label>
                        <input class="w-full rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" id="nisn" name="nisn" placeholder="Masukkan NISN" type="text" value="{{ old('nisn') }}">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-on-surface" for="batch_id">Angkatan</label>
                        <select class="w-full rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" id="batch_id" name="batch_id" required>
                            <option value="">Pilih angkatan</option>
                            @foreach ($batches as $batch)
                                <option @selected((string) old('batch_id') === (string) $batch->id) value="{{ $batch->id }}">{{ $batch->academic_year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-on-surface" for="class_id">Kelas</label>
                        <select class="w-full rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" id="class_id" name="class_id" required>
                            <option value="">Pilih kelas</option>
                            @foreach ($classes as $class)
                                <option @selected((string) old('class_id') === (string) $class->id) value="{{ $class->id }}">{{ $class->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-on-surface" for="major_id">Jurusan</label>
                        <select class="w-full rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" id="major_id" name="major_id" required>
                            <option value="">Pilih jurusan</option>
                            @foreach ($majors as $major)
                                <option @selected((string) old('major_id') === (string) $major->id) value="{{ $major->id }}">{{ $major->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-on-surface" for="student_type">Tipe Siswa</label>
                        <select class="w-full rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" id="student_type" name="student_type" required>
                            @foreach ($studentTypeLabels as $value => $label)
                                <option @selected(old('student_type', 'regular') === $value) value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-on-surface" for="enrollment_date">Tanggal Masuk</label>
                        <input class="w-full rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" id="enrollment_date" name="enrollment_date" type="date" value="{{ old('enrollment_date') }}">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-on-surface">Status</label>
                        <label class="flex items-center gap-3 rounded-xl bg-surface-container-low px-4 py-3 text-sm text-on-surface">
                            <input @checked(old('is_active', '1') === '1') class="rounded border-slate-300 text-primary focus:ring-primary" name="is_active" type="checkbox" value="1">
                            Aktif saat disimpan
                        </label>
                    </div>
                </div>

                <div class="flex flex-wrap gap-3 pt-2">
                    <button class="inline-flex items-center rounded-xl bg-primary px-6 py-3 text-sm font-semibold text-white transition-colors hover:bg-primary-container" type="submit">
                        Simpan Siswa
                    </button>
                    <a class="inline-flex items-center rounded-xl bg-surface-container-low px-6 py-3 text-sm font-semibold text-on-surface transition-colors hover:bg-surface-container-high" href="{{ route('students.index') }}">
                        Batal
                    </a>
                </div>
            </form>
        </div>

        <div class="lg:col-span-4 space-y-6">
            <div class="bg-surface-container-lowest rounded-xl shadow-sm p-6">
                <h4 class="text-base font-headline font-bold text-on-surface">Panduan Input</h4>
                <div class="mt-4 space-y-3 text-sm text-on-surface-variant">
                    <p>Isi minimal nama lengkap, angkatan, kelas, jurusan, dan tipe siswa.</p>
                    <p>NIS dan NISN bersifat unik. Jika salah satu sudah pernah dipakai, sistem akan menolak penyimpanan.</p>
                    <p>Gunakan tipe `Asrama` untuk siswa boarding agar billing boarding bisa dipetakan dengan benar.</p>
                </div>
            </div>

            <div class="bg-surface-container-lowest rounded-xl shadow-sm p-6">
                <h4 class="text-base font-headline font-bold text-on-surface">Setelah Disimpan</h4>
                <div class="mt-4 space-y-3 text-sm text-on-surface-variant">
                    <p>Data siswa akan langsung masuk ke daftar siswa.</p>
                    <p>Status aktif menentukan apakah siswa akan ikut proses generate tagihan berikutnya.</p>
                    <p>Jika butuh input massal, gunakan menu `Import Siswa` agar lebih cepat.</p>
                </div>
            </div>
        </div>
    </div>
</div>
