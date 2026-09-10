<div class="bg-surface-container-lowest rounded-xl shadow-sm p-6">
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
