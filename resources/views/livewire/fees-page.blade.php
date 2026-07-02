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
        'full_day' => 'Full Day',
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
                                    <div class="flex items-center gap-2">
                                        @if ($editingFeeType?->id === $feeType->id)
                                            <a class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-600 transition hover:border-slate-300 hover:text-slate-700" href="{{ route('fees.index', $baseQuery) }}" title="Batal Edit">
                                                <svg viewBox="0 0 24 24" aria-hidden="true" class="h-4 w-4">
                                                    <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z" fill="currentColor"/>
                                                </svg>
                                            </a>
                                        @else
                                            <a class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-blue-200 bg-white text-blue-600 transition hover:border-blue-300 hover:text-blue-700" href="{{ route('fees.index', array_merge($baseQuery, ['edit_fee_type' => $feeType->id])) }}" title="Edit jenis biaya">
                                                <svg viewBox="0 0 24 24" aria-hidden="true" class="h-4 w-4">
                                                    <path d="M4 20h4l10.5-10.5-4-4L4 16v4zm13.7-12.3 1.6-1.6a1.4 1.4 0 0 0 0-2l-1.4-1.4a1.4 1.4 0 0 0-2 0l-1.6 1.6 3.4 3.4z" fill="currentColor"/>
                                                </svg>
                                            </a>
                                        @endif

                                        <form action="{{ route('fee-types.destroy', $feeType) }}" method="POST" onsubmit="return confirm('Hapus jenis biaya ini?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-red-200 bg-white text-red-600 transition hover:border-red-300 hover:text-red-700" title="Hapus jenis biaya">
                                                <svg viewBox="0 0 24 24" aria-hidden="true" class="h-4 w-4">
                                                    <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z" fill="currentColor"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
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
                                    <div class="flex items-center gap-2">
                                        @if ($editingFeeScheme?->id === $scheme->id)
                                            <a class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-600 transition hover:border-slate-300 hover:text-slate-700" href="{{ route('fees.index', $baseQuery) }}" title="Batal Edit">
                                                <svg viewBox="0 0 24 24" aria-hidden="true" class="h-4 w-4">
                                                    <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z" fill="currentColor"/>
                                                </svg>
                                            </a>
                                        @else
                                            <a class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-blue-200 bg-white text-blue-600 transition hover:border-blue-300 hover:text-blue-700" href="{{ route('fees.index', array_merge($baseQuery, ['edit_fee_scheme' => $scheme->id])) }}" title="Edit skema tarif">
                                                <svg viewBox="0 0 24 24" aria-hidden="true" class="h-4 w-4">
                                                    <path d="M4 20h4l10.5-10.5-4-4L4 16v4zm13.7-12.3 1.6-1.6a1.4 1.4 0 0 0 0-2l-1.4-1.4a1.4 1.4 0 0 0-2 0l-1.6 1.6 3.4 3.4z" fill="currentColor"/>
                                                </svg>
                                            </a>
                                        @endif

                                        <form action="{{ route('fee-schemes.destroy', $scheme) }}" method="POST" onsubmit="return confirm('Hapus skema tarif ini?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-red-200 bg-white text-red-600 transition hover:border-red-300 hover:text-red-700" title="Hapus skema tarif">
                                                <svg viewBox="0 0 24 24" aria-hidden="true" class="h-4 w-4">
                                                    <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z" fill="currentColor"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
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
