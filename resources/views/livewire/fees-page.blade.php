@php
    $categoryLabels = [
        'spp' => 'SPP',
        'activity' => 'Kegiatan',
        'meal' => 'Makan',
        'other' => 'Lainnya',
    ];
    $frequencyLabels = [
        'monthly' => 'Bulanan',
        'one_time' => 'Sekali',
        'custom' => 'Kustom',
    ];
    $appliesToLabels = [
        'all' => 'Semua siswa',
        'regular' => 'Reguler',
        'boarding' => 'Asrama',
    ];
    $isEditingFeeType = $editingFeeType !== null;
    $isEditingFeeScheme = $editingFeeScheme !== null;
    $feeTypeFormAction = $isEditingFeeType ? route('fee-types.update', $editingFeeType) : route('fee-types.store');
    $feeSchemeFormAction = $isEditingFeeScheme ? route('fee-schemes.update', $editingFeeScheme) : route('fee-schemes.store');
    $baseQuery = request()->except(['edit_fee_type', 'edit_fee_scheme', 'page']);
    $canManageFees = auth()->user()?->hasRole('admin_keuangan');
@endphp

<div class="space-y-8">
    @if ($canManageFees)
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
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
        </div>
    @endif

    <div class="space-y-8">
        <div class="bg-surface-container-lowest rounded-xl shadow-sm overflow-hidden">
            <div class="px-8 py-6 border-b border-surface-container">
                <h3 class="text-lg font-headline font-bold">Daftar Jenis Biaya</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-surface-container-low">
                    <tr>
                        <th class="px-8 py-4 text-xs uppercase text-slate-500">Kode</th>
                        <th class="px-8 py-4 text-xs uppercase text-slate-500">Nama</th>
                        <th class="px-8 py-4 text-xs uppercase text-slate-500">Kategori</th>
                        <th class="px-8 py-4 text-xs uppercase text-slate-500">Cicilan</th>
                        <th class="px-8 py-4 text-xs uppercase text-slate-500">Aksi</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-container">
                    @foreach ($feeTypes as $feeType)
                        <tr>
                            <td class="px-8 py-4 text-sm font-semibold">{{ $feeType->code }}</td>
                            <td class="px-8 py-4 text-sm">{{ $feeType->name }}</td>
                            <td class="px-8 py-4 text-sm">{{ $categoryLabels[$feeType->category] ?? $feeType->category }}</td>
                            <td class="px-8 py-4 text-sm">{{ $feeType->installment_allowed ? 'Ya' : 'Tidak' }}</td>
                            <td class="px-8 py-4 text-sm">
                                @if ($canManageFees)
                                    <a class="inline-flex items-center rounded-lg bg-surface-container-low px-3 py-2 font-semibold text-primary hover:bg-surface-container-high" href="{{ route('fees.index', array_merge($baseQuery, ['edit_fee_type' => $feeType->id])) }}">
                                        Edit
                                    </a>
                                @else
                                    <span class="text-sm text-on-surface-variant">Lihat saja</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-surface-container-lowest rounded-xl shadow-sm overflow-hidden">
            <div class="px-8 py-6 border-b border-surface-container">
                <h3 class="text-lg font-headline font-bold">Skema Tarif Aktif</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-surface-container-low">
                    <tr>
                        <th class="px-8 py-4 text-xs uppercase text-slate-500">Jenis</th>
                        <th class="px-8 py-4 text-xs uppercase text-slate-500">Angkatan</th>
                        <th class="px-8 py-4 text-xs uppercase text-slate-500">Nominal</th>
                        <th class="px-8 py-4 text-xs uppercase text-slate-500">Periode</th>
                        <th class="px-8 py-4 text-xs uppercase text-slate-500">Aksi</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-container">
                    @foreach ($feeSchemes as $scheme)
                        <tr>
                            <td class="px-8 py-4 text-sm">{{ $scheme->feeType->name }}</td>
                            <td class="px-8 py-4 text-sm">{{ $scheme->batch?->academic_year ?? 'Semua' }}</td>
                            <td class="px-8 py-4 text-sm">Rp {{ number_format($scheme->nominal, 0, ',', '.') }}</td>
                            <td class="px-8 py-4 text-sm">{{ $scheme->effective_start->format('d/m/Y') }} - {{ $scheme->effective_end?->format('d/m/Y') ?? 'aktif' }}</td>
                            <td class="px-8 py-4 text-sm">
                                @if ($canManageFees)
                                    <a class="inline-flex items-center rounded-lg bg-surface-container-low px-3 py-2 font-semibold text-primary hover:bg-surface-container-high" href="{{ route('fees.index', array_merge($baseQuery, ['edit_fee_scheme' => $scheme->id])) }}">
                                        Edit
                                    </a>
                                @else
                                    <span class="text-sm text-on-surface-variant">Lihat saja</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
