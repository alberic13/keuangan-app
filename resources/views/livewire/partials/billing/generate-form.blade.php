<div class="lg:col-span-8 bg-surface-container-lowest rounded-xl shadow-sm p-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-lg font-headline font-bold">Generate Tagihan</h3>
            <p class="text-sm text-on-surface-variant">Gunakan jenis biaya dan periode aktif untuk membuat invoice massal.</p>
        </div>
        <a class="text-sm font-semibold text-primary" href="{{ route('fees.index') }}">Kelola tarif</a>
    </div>
    <form action="{{ route('billing.generate') }}" class="grid grid-cols-1 md:grid-cols-2 gap-4" method="POST">
        @csrf
        <select class="rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="fee_type_id" required>
            <option value="">Pilih jenis biaya</option>
            @foreach ($feeTypes as $feeType)
                <option value="{{ $feeType->id }}">{{ $feeType->name }}</option>
            @endforeach
        </select>
        <select class="rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="billing_cycle_id" required>
            <option value="">Pilih siklus tagihan</option>
            @foreach ($cycles as $cycle)
                <option value="{{ $cycle->id }}">{{ $cycle->period_label }} • {{ $cycleStatusLabels[$cycle->status] ?? strtoupper($cycle->status) }}</option>
            @endforeach
        </select>
        <select class="rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="filters[batch_id]">
            <option value="">Semua angkatan</option>
            @foreach ($batches as $batch)
                <option value="{{ $batch->id }}">{{ $batch->academic_year }}</option>
            @endforeach
        </select>
        <select class="rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="filters[class_id]">
            <option value="">Semua kelas</option>
            @foreach ($classes as $class)
                <option value="{{ $class->id }}">{{ $class->name }}</option>
            @endforeach
        </select>
        <select class="rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="filters[student_type]">
            <option value="all">Semua tipe siswa</option>
            @foreach ($studentTypes as $studentType)
                <option value="{{ $studentType->slug }}">{{ $studentType->label }}</option>
            @endforeach
        </select>
        <input class="md:col-span-2 rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="reference_name" placeholder="Referensi kegiatan, opsional untuk uang kegiatan" type="text">
        <button class="md:col-span-2 rounded-xl bg-primary text-white font-semibold px-5 py-3" type="submit">Buat Invoice</button>
    </form>
</div>
