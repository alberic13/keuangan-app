<div class="space-y-8">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <div class="lg:col-span-4 bg-surface-container-lowest rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-headline font-bold mb-4">Upload Import Siswa</h3>
            <p class="text-sm text-on-surface-variant mb-4">Gunakan file Excel untuk tambah siswa baru atau pembaruan massal saat kenaikan kelas. Baris dengan NIS/NISN yang sudah ada akan memperbarui data siswa tersebut.</p>
            <div class="flex flex-col gap-3">
                <a class="inline-flex items-center justify-center rounded-xl bg-surface-container-low px-5 py-3 text-sm font-semibold text-on-surface" href="{{ route('imports.students.template') }}">Unduh Template</a>
                <form action="{{ route('imports.students.preview') }}" class="space-y-3" enctype="multipart/form-data" method="POST">
                    @csrf
                    <input class="w-full rounded-xl bg-surface-container-low px-4 py-3 text-sm" name="file" required type="file">
                    <button class="w-full rounded-xl bg-primary text-white font-semibold px-5 py-3" type="submit">Pratinjau Import</button>
                </form>

                @if (!empty($preview['preview_token']))
                    <form action="{{ route('imports.students.commit') }}" method="POST">
                        @csrf
                        <input name="preview_token" type="hidden" value="{{ $preview['preview_token'] }}">
                        <button class="w-full rounded-xl bg-emerald-600 text-white font-semibold px-5 py-3" type="submit">Proses Import</button>
                    </form>
                @endif
            </div>
        </div>

        <div class="lg:col-span-8 bg-surface-container-lowest rounded-xl shadow-sm p-6">
            <h3 class="text-lg font-headline font-bold mb-4">Pratinjau Import</h3>
            @if (!empty($preview))
                <div class="grid grid-cols-3 gap-4 mb-6">
                    <div class="rounded-xl bg-surface-container-low p-4">
                        <p class="text-xs uppercase text-slate-500">Total Baris</p>
                        <p class="text-2xl font-bold">{{ $preview['summary']['total_rows'] }}</p>
                    </div>
                    <div class="rounded-xl bg-emerald-50 p-4">
                        <p class="text-xs uppercase text-emerald-700">Baris Valid</p>
                        <p class="text-2xl font-bold text-emerald-900">{{ $preview['summary']['valid_rows'] }}</p>
                    </div>
                    <div class="rounded-xl bg-red-50 p-4">
                        <p class="text-xs uppercase text-red-700">Baris Tidak Valid</p>
                        <p class="text-2xl font-bold text-red-900">{{ $preview['summary']['invalid_rows'] }}</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-surface-container-low">
                        <tr>
                            <th class="px-4 py-3 text-xs uppercase text-slate-500">Baris</th>
                            <th class="px-4 py-3 text-xs uppercase text-slate-500">Nama</th>
                            <th class="px-4 py-3 text-xs uppercase text-slate-500">Status</th>
                            <th class="px-4 py-3 text-xs uppercase text-slate-500">Kesalahan</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-surface-container">
                        @foreach ($preview['rows'] as $row)
                            <tr>
                                <td class="px-4 py-3 text-sm">{{ $row['row_number'] }}</td>
                                <td class="px-4 py-3 text-sm">{{ $row['payload']['full_name'] }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $row['status'] === 'valid' ? 'bg-emerald-100 text-emerald-900' : 'bg-red-100 text-red-900' }}">
                                        {{ $row['status'] === 'valid' ? 'Valid' : 'Tidak Valid' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-on-surface-variant">{{ implode('; ', array_values($row['errors'])) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-sm text-on-surface-variant">Belum ada preview. Unggah file untuk melihat hasil validasi per baris.</p>
            @endif
        </div>
    </div>

    <div class="bg-surface-container-lowest rounded-xl shadow-sm p-6">
        <h3 class="text-lg font-headline font-bold mb-4">Riwayat Import</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-surface-container-low">
                <tr>
                    <th class="px-4 py-3 text-xs uppercase text-slate-500">File</th>
                    <th class="px-4 py-3 text-xs uppercase text-slate-500">Baris</th>
                    <th class="px-4 py-3 text-xs uppercase text-slate-500">Berhasil</th>
                    <th class="px-4 py-3 text-xs uppercase text-slate-500">Gagal</th>
                    <th class="px-4 py-3 text-xs uppercase text-slate-500">Pengguna</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-surface-container">
                @forelse ($logs as $log)
                    <tr>
                        <td class="px-4 py-3 text-sm">{{ $log->file_name }}</td>
                        <td class="px-4 py-3 text-sm">{{ $log->total_rows }}</td>
                        <td class="px-4 py-3 text-sm">{{ $log->success_rows }}</td>
                        <td class="px-4 py-3 text-sm">{{ $log->failed_rows }}</td>
                        <td class="px-4 py-3 text-sm">{{ $log->importer?->name }}</td>
                    </tr>
                @empty
                    <tr><td class="px-4 py-4 text-sm text-on-surface-variant" colspan="5">Belum ada riwayat import.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
