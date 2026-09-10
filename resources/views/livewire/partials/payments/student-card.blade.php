<div class="rounded-xl border border-surface-container bg-surface-container-lowest p-6 shadow-sm">
    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div>
            <p class="text-xs uppercase tracking-[0.24em] text-on-surface-variant">Siswa Pembayar</p>
            <h4 class="mt-2 text-2xl font-headline font-bold text-on-surface">{{ $selectedStudent->full_name }}</h4>
            <p class="mt-1 text-sm text-on-surface-variant">
                {{ $selectedStudent->nis ?: '-' }} / {{ $selectedStudent->nisn ?: '-' }}
            </p>
        </div>
        <span class="rounded-full bg-secondary-container px-3 py-1 text-xs font-semibold text-on-surface">
            {{ $invoiceOptions->count() }} tagihan tersedia
        </span>
    </div>

    <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-3 text-sm">
        <div class="rounded-xl bg-surface-container-low px-4 py-3">
            <p class="text-xs uppercase tracking-widest text-on-surface-variant">Kelas</p>
            <p class="mt-1 font-semibold text-on-surface">{{ $selectedStudent->classRoom->name ?? '-' }}</p>
        </div>
        <div class="rounded-xl bg-surface-container-low px-4 py-3">
            <p class="text-xs uppercase tracking-widest text-on-surface-variant">Angkatan</p>
            <p class="mt-1 font-semibold text-on-surface">{{ $selectedStudent->batch->academic_year ?? '-' }}</p>
        </div>
        <div class="rounded-xl bg-surface-container-low px-4 py-3">
            <p class="text-xs uppercase tracking-widest text-on-surface-variant">Tipe Siswa</p>
            <p class="mt-1 font-semibold text-on-surface">{{ $studentTypeLabels[$selectedStudent->student_type] ?? ucfirst($selectedStudent->student_type) }}</p>
        </div>
    </div>
</div>
