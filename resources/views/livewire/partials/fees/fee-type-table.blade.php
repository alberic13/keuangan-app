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
