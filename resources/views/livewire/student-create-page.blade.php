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

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-on-surface" for="student_type">Tipe Siswa</label>
                        <select class="w-full rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" id="student_type" name="student_type" required>
                            @foreach ($studentTypes as $studentType)
                                <option @selected(old('student_type', $studentTypes->first()?->slug ?? 'regular') === $studentType->slug) value="{{ $studentType->slug }}">{{ $studentType->label }}</option>
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
            <div class="bg-surface-container-lowest rounded-xl shadow-sm p-6 space-y-6">
                <div>
                    <h4 class="text-base font-headline font-bold text-on-surface">Tambah Opsi Dropdown</h4>
                    <p class="mt-2 text-sm text-on-surface-variant">Semua data yang ditambahkan di sini langsung tersimpan ke master dan muncul di dropdown form siswa.</p>
                </div>

                <div class="space-y-5">
                    <form class="space-y-3 rounded-xl bg-surface-container-low px-4 py-4" wire:submit.prevent="addBatch">
                        <div class="flex items-center justify-between gap-3">
                            <h5 class="text-sm font-semibold text-on-surface">Tambah Angkatan</h5>
                            <span class="text-xs text-on-surface-variant">batch</span>
                        </div>
                        <input class="w-full rounded-xl border-none bg-white px-4 py-3 text-sm" placeholder="Label angkatan, mis. 2026" type="text" wire:model.defer="batchYearLabel">
                        <input class="w-full rounded-xl border-none bg-white px-4 py-3 text-sm" placeholder="Tahun ajaran, mis. 2026/2027" type="text" wire:model.defer="batchAcademicYear">
                        <button class="w-full rounded-xl bg-primary px-4 py-3 text-sm font-semibold text-white" type="submit">Simpan Angkatan</button>
                    </form>
                    <div class="rounded-xl border border-dashed border-surface-container bg-white px-4 py-4">
                        <div class="mb-3 flex items-center justify-between gap-3">
                            <h5 class="text-sm font-semibold text-on-surface">Angkatan Tersedia</h5>
                            <span class="text-xs text-on-surface-variant">{{ $batches->count() }} data</span>
                        </div>
                        <div class="space-y-2">
                            @forelse ($batches as $batch)
                                <div class="flex items-center justify-between gap-3 rounded-lg bg-surface-container-low px-3 py-2 text-sm">
                                    <div>
                                        <div class="font-semibold text-on-surface">{{ $batch->academic_year }}</div>
                                        <div class="text-xs text-on-surface-variant">{{ $batch->year_label }}</div>
                                    </div>
                                    <button class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-rose-200 text-rose-600 hover:bg-rose-50" title="Hapus angkatan" type="button" wire:click="deleteBatch({{ $batch->id }})" wire:confirm="Hapus angkatan ini?">
                                        <span aria-hidden="true" class="text-lg leading-none">×</span>
                                    </button>
                                </div>
                            @empty
                                <p class="text-xs text-on-surface-variant">Belum ada angkatan.</p>
                            @endforelse
                        </div>
                    </div>

                    <form class="space-y-3 rounded-xl bg-surface-container-low px-4 py-4" wire:submit.prevent="addClass">
                        <div class="flex items-center justify-between gap-3">
                            <h5 class="text-sm font-semibold text-on-surface">Tambah Kelas</h5>
                            <span class="text-xs text-on-surface-variant">class</span>
                        </div>
                        <input class="w-full rounded-xl border-none bg-white px-4 py-3 text-sm" placeholder="Nama kelas, mis. X PAI 1" type="text" wire:model.defer="className">
                        <input class="w-full rounded-xl border-none bg-white px-4 py-3 text-sm" placeholder="Level opsional, mis. X" type="text" wire:model.defer="classLevel">
                        <button class="w-full rounded-xl bg-primary px-4 py-3 text-sm font-semibold text-white" type="submit">Simpan Kelas</button>
                    </form>
                    <div class="rounded-xl border border-dashed border-surface-container bg-white px-4 py-4">
                        <div class="mb-3 flex items-center justify-between gap-3">
                            <h5 class="text-sm font-semibold text-on-surface">Kelas Tersedia</h5>
                            <span class="text-xs text-on-surface-variant">{{ $classes->count() }} data</span>
                        </div>
                        <div class="space-y-2">
                            @forelse ($classes as $class)
                                <div class="flex items-center justify-between gap-3 rounded-lg bg-surface-container-low px-3 py-2 text-sm">
                                    <div>
                                        <div class="font-semibold text-on-surface">{{ $class->name }}</div>
                                        <div class="text-xs text-on-surface-variant">{{ $class->level ?: 'Tanpa level' }}</div>
                                    </div>
                                    <button class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-rose-200 text-rose-600 hover:bg-rose-50" title="Hapus kelas" type="button" wire:click="deleteClass({{ $class->id }})" wire:confirm="Hapus kelas ini?">
                                        <span aria-hidden="true" class="text-lg leading-none">×</span>
                                    </button>
                                </div>
                            @empty
                                <p class="text-xs text-on-surface-variant">Belum ada kelas.</p>
                            @endforelse
                        </div>
                    </div>



                    <div class="space-y-2 rounded-xl bg-surface-container-low px-4 py-4">
                        <div class="flex items-center justify-between gap-3">
                            <h5 class="text-sm font-semibold text-on-surface">Tipe Siswa Dikunci</h5>
                            <span class="text-xs text-on-surface-variant">student type</span>
                        </div>
                        <p class="text-sm text-on-surface-variant">Kebijakan sekolah hanya memakai tiga tipe siswa: Reguler, Full Day, dan Asrama.</p>
                    </div>
                    <div class="rounded-xl border border-dashed border-surface-container bg-white px-4 py-4">
                        <div class="mb-3 flex items-center justify-between gap-3">
                            <h5 class="text-sm font-semibold text-on-surface">Tipe Siswa Tersedia</h5>
                            <span class="text-xs text-on-surface-variant">{{ $studentTypes->count() }} data</span>
                        </div>
                        <div class="space-y-2">
                            @forelse ($studentTypes as $studentType)
                                <div class="flex items-center justify-between gap-3 rounded-lg bg-surface-container-low px-3 py-2 text-sm">
                                    <div>
                                        <div class="font-semibold text-on-surface">{{ $studentType->label }}</div>
                                        <div class="text-xs text-on-surface-variant">{{ $studentType->slug }}</div>
                                    </div>
                                    @unless (in_array($studentType->slug, ['regular', 'full_day', 'boarding'], true))
                                        <button class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-rose-200 text-rose-600 hover:bg-rose-50" title="Hapus tipe siswa" type="button" wire:click="deleteStudentType({{ $studentType->id }})" wire:confirm="Hapus tipe siswa ini?">
                                            <span aria-hidden="true" class="text-lg leading-none">×</span>
                                        </button>
                                    @endunless
                                </div>
                            @empty
                                <p class="text-xs text-on-surface-variant">Belum ada tipe siswa.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-surface-container-lowest rounded-xl shadow-sm p-6">
                <h4 class="text-base font-headline font-bold text-on-surface">Panduan Input</h4>
                <div class="mt-4 space-y-3 text-sm text-on-surface-variant">
                    <p>Isi minimal nama lengkap, angkatan, kelas, dan tipe siswa.</p>
                    <p>NIS dan NISN bersifat unik. Jika salah satu sudah pernah dipakai, sistem akan menolak penyimpanan.</p>
                    <p>Gunakan tipe Reguler, Full Day, atau Asrama sesuai kebijakan biaya siswa.</p>
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
