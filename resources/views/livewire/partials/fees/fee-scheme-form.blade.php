<div class="bg-surface-container-lowest rounded-xl shadow-sm p-6">
    <div class="mb-4 flex items-start justify-between gap-4">
        <div>
            <h3 class="text-lg font-headline font-bold">{{ $isEditingFeeScheme ? 'Edit Skema Tarif' : 'Tambah Skema Tarif' }}</h3>
            <p class="text-sm text-on-surface-variant">
                {{ $isEditingFeeScheme ? 'Perbarui nominal atau periode skema tarif yang dipilih.' : 'Tambahkan skema tarif aktif berdasarkan jenis biaya dan angkatan.' }}
            </p>
        </div>
        @if ($isEditingFeeScheme)
            <a class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50" href="{{ route('fees.index', $baseQuery) }}">
                Batal
            </a>
        @endif
    </div>
    <form action="{{ $feeSchemeFormAction }}" class="space-y-4" method="POST">
        @csrf
        @if ($isEditingFeeScheme)
            @method('PUT')
        @endif
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <select class="w-full rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="fee_type_id" required>
                <option value="">Pilih jenis biaya</option>
                @foreach ($feeTypeOptions as $feeType)
                    <option @selected((string) old('fee_type_id', $editingFeeScheme?->fee_type_id) === (string) $feeType->id) value="{{ $feeType->id }}">{{ $feeType->name }}</option>
                @endforeach
            </select>
            <select class="w-full rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="batch_id">
                <option value="">Semua angkatan</option>
                @foreach ($batches as $batch)
                    <option @selected((string) old('batch_id', $editingFeeScheme?->batch_id) === (string) $batch->id) value="{{ $batch->id }}">{{ $batch->academic_year }}</option>
                @endforeach
            </select>
        </div>
        <input class="w-full rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" min="1" name="nominal" placeholder="Nominal" required type="number" value="{{ old('nominal', $editingFeeScheme?->nominal) }}">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <input class="rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="effective_start" required type="date" value="{{ old('effective_start', $editingFeeScheme?->effective_start?->toDateString()) }}">
            <input class="rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="effective_end" type="date" value="{{ old('effective_end', $editingFeeScheme?->effective_end?->toDateString()) }}">
        </div>
        <button class="w-full rounded-xl bg-primary text-white font-semibold px-5 py-3" type="submit">
            {{ $isEditingFeeScheme ? 'Perbarui Tarif' : 'Simpan Tarif' }}
        </button>
    </form>
</div>
