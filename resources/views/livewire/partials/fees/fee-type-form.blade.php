<div class="bg-surface-container-lowest rounded-xl shadow-sm p-6">
    <div class="mb-4 flex items-start justify-between gap-4">
        <div>
            <h3 class="text-lg font-headline font-bold">{{ $isEditingFeeType ? 'Edit Jenis Biaya' : 'Tambah Jenis Biaya' }}</h3>
            <p class="text-sm text-on-surface-variant">
                {{ $isEditingFeeType ? 'Perbarui detail jenis biaya yang dipilih.' : 'Tambahkan jenis biaya baru untuk penagihan sekolah.' }}
            </p>
        </div>
        @if ($isEditingFeeType)
            <a class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50" href="{{ route('fees.index', $baseQuery) }}">
                Batal
            </a>
        @endif
    </div>
    <form action="{{ $feeTypeFormAction }}" class="space-y-4" method="POST">
        @csrf
        @if ($isEditingFeeType)
            @method('PUT')
        @endif
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <input class="rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="code" placeholder="Kode" required type="text" value="{{ old('code', $editingFeeType?->code) }}">
            <input class="rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="name" placeholder="Nama biaya" required type="text" value="{{ old('name', $editingFeeType?->name) }}">
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <select class="w-full rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="category" required>
                <option @selected(old('category', $editingFeeType?->category) === 'spp') value="spp">SPP</option>
                <option @selected(old('category', $editingFeeType?->category) === 'activity') value="activity">Uang Kegiatan</option>
                <option @selected(old('category', $editingFeeType?->category) === 'meal') value="meal">Uang Makan</option>
                <option @selected(old('category', $editingFeeType?->category) === 'other') value="other">Lainnya</option>
            </select>
            <select class="w-full rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="billing_frequency" required>
                <option @selected(old('billing_frequency', $editingFeeType?->billing_frequency) === 'monthly') value="monthly">Bulanan</option>
                <option @selected(old('billing_frequency', $editingFeeType?->billing_frequency) === 'one_time') value="one_time">Sekali</option>
                <option @selected(old('billing_frequency', $editingFeeType?->billing_frequency) === 'custom') value="custom">Kustom</option>
            </select>
            <select class="w-full rounded-xl border-none bg-surface-container-low px-4 py-3 text-sm" name="applies_to" required>
                <option @selected(old('applies_to', $editingFeeType?->applies_to) === 'all') value="all">Semua siswa</option>
                <option @selected(old('applies_to', $editingFeeType?->applies_to) === 'regular') value="regular">Reguler</option>
                <option @selected(old('applies_to', $editingFeeType?->applies_to) === 'full_day') value="full_day">Full Day</option>
                <option @selected(old('applies_to', $editingFeeType?->applies_to) === 'boarding') value="boarding">Asrama</option>
            </select>
        </div>
        <label class="flex items-center gap-2 text-sm text-on-surface-variant">
            <input @checked(old('installment_allowed', $editingFeeType?->installment_allowed)) name="installment_allowed" type="checkbox" value="1"> Boleh cicilan
        </label>
        <button class="w-full rounded-xl bg-primary text-white font-semibold px-5 py-3" type="submit">
            {{ $isEditingFeeType ? 'Perbarui Jenis Biaya' : 'Simpan Jenis Biaya' }}
        </button>
    </form>
</div>
