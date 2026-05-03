@php
    $tableRows = $rows instanceof \Illuminate\Support\Collection
        ? $rows->map(fn ($row) => is_object($row) ? (array) $row : $row)->all()
        : (is_array($rows) ? $rows : []);
@endphp

<div class="space-y-8">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
        @php
            $monthLabels = [
                1 => 'Januari',
                2 => 'Februari',
                3 => 'Maret',
                4 => 'April',
                5 => 'Mei',
                6 => 'Juni',
                7 => 'Juli',
                8 => 'Agustus',
                9 => 'September',
                10 => 'Oktober',
                11 => 'November',
                12 => 'Desember',
            ];

            $filter = is_array(($filter ?? null)) ? $filter : ['mode' => 'daily'];
            $mode = $filter['mode'] ?? 'daily';
            $yearNow = now()->year;
            $years = range($yearNow, $yearNow - 5);
        @endphp

        @if (! empty($periodAlert))
            <div class="mb-6 alert warning">
                {{ $periodAlert }}
            </div>
        @endif

        <form class="space-y-6" method="GET">
            <input name="type" type="hidden" value="cashflow">

            <div class="grid gap-4 lg:grid-cols-2">
                <label class="group flex cursor-pointer items-start gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 transition hover:border-emerald-300 hover:bg-emerald-50/60">
                    <input @checked($mode === 'daily') class="mt-1 h-4 w-4 border-slate-300 text-emerald-700 focus:ring-emerald-600" name="mode" type="radio" value="daily">
                    <div class="flex-1 space-y-4">
                        <div>
                            <div class="text-sm font-semibold text-slate-950">Mutasi Harian</div>
                            <div class="mt-1 text-sm leading-6 text-slate-500">Gunakan jika ingin melihat transaksi per tanggal tertentu.</div>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-3">
                            <select class="w-full rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm font-medium text-slate-900 shadow-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" name="from_day">
                                @for ($d = 1; $d <= 31; $d++)
                                    <option @selected((int) ($filter['from_day'] ?? now()->day) === $d) value="{{ $d }}">{{ $d }}</option>
                                @endfor
                            </select>
                            <select class="w-full rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm font-medium text-slate-900 shadow-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" name="from_month">
                                @for ($m = 1; $m <= 12; $m++)
                                    <option @selected((int) ($filter['from_month'] ?? now()->month) === $m) value="{{ $m }}">{{ $monthLabels[$m] }}</option>
                                @endfor
                            </select>
                            <select class="w-full rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm font-medium text-slate-900 shadow-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" name="from_year">
                                @foreach ($years as $y)
                                    <option @selected((int) ($filter['from_year'] ?? now()->year) === $y) value="{{ $y }}">{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-2 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                            <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Sampai Periode</div>
                            <div class="grid gap-3 sm:grid-cols-3">
                                <select class="w-full rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm font-medium text-slate-900 shadow-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" name="to_day">
                                    @for ($d = 1; $d <= 31; $d++)
                                        <option @selected((int) ($filter['to_day'] ?? ($filter['from_day'] ?? now()->day)) === $d) value="{{ $d }}">{{ $d }}</option>
                                    @endfor
                                </select>
                                <select class="w-full rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm font-medium text-slate-900 shadow-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" name="to_month">
                                    @for ($m = 1; $m <= 12; $m++)
                                        <option @selected((int) ($filter['to_month'] ?? ($filter['from_month'] ?? now()->month)) === $m) value="{{ $m }}">{{ $monthLabels[$m] }}</option>
                                    @endfor
                                </select>
                                <select class="w-full rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm font-medium text-slate-900 shadow-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" name="to_year">
                                    @foreach ($years as $y)
                                        <option @selected((int) ($filter['to_year'] ?? ($filter['from_year'] ?? now()->year)) === $y) value="{{ $y }}">{{ $y }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="rounded-xl bg-white px-4 py-3 text-sm text-slate-600 ring-1 ring-slate-200">
                            Pilih tanggal mulai dan sampai, lalu sistem akan menyesuaikan bila urutannya terbalik.
                        </div>
                    </div>
                </label>

                <label class="group flex cursor-pointer items-start gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 transition hover:border-emerald-300 hover:bg-emerald-50/60">
                    <input @checked($mode === 'monthly') class="mt-1 h-4 w-4 border-slate-300 text-emerald-700 focus:ring-emerald-600" name="mode" type="radio" value="monthly">
                    <div class="flex-1 space-y-4">
                        <div>
                            <div class="text-sm font-semibold text-slate-950">Mutasi Bulanan</div>
                            <div class="mt-1 text-sm leading-6 text-slate-500">Pilih satu bulan penuh untuk ringkasan yang lebih cepat dibaca.</div>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2">
                            <select class="w-full rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm font-medium text-slate-900 shadow-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" name="month">
                                @for ($m = 1; $m <= 12; $m++)
                                    <option @selected((int) ($filter['month'] ?? now()->month) === $m) value="{{ $m }}">{{ $monthLabels[$m] }}</option>
                                @endfor
                            </select>
                            <select class="w-full rounded-xl border border-slate-200 bg-white px-3 py-3 text-sm font-medium text-slate-900 shadow-sm outline-none transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100" name="year">
                                @foreach ($years as $y)
                                    <option @selected((int) ($filter['year'] ?? now()->year) === $y) value="{{ $y }}">{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>

                    </div>
                </label>
            </div>

            <div class="grid gap-4 lg:grid-cols-[1.4fr_1fr_auto] lg:items-end">
                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                    <div class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Periode terpilih</div>
                    <div class="mt-2 text-base font-semibold text-slate-900">{{ $periodLabel ?? 'Periode terpilih' }}</div>
                    <div class="mt-1 text-sm text-slate-500">Perubahan filter akan langsung dipakai saat tombol ditampilkan ditekan.</div>
                </div>

                <div class="flex flex-wrap gap-3">
                    <a class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-emerald-300 hover:text-emerald-800" href="{{ route('reports.export', array_merge(request()->query(), ['type' => $type, 'format' => 'pdf'])) }}">Export PDF</a>
                    <a class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-emerald-300 hover:text-emerald-800" href="{{ route('reports.export', array_merge(request()->query(), ['type' => $type, 'format' => 'xlsx'])) }}">Export Excel</a>
                </div>

                <button class="inline-flex items-center justify-center rounded-xl bg-emerald-700 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-700/20 transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-200" type="submit">Tampilkan</button>
            </div>
        </form>
    </section>

    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-6 py-5 sm:px-8 flex items-center justify-between gap-4">
            <div>
                <h3 class="text-lg font-bold text-slate-950">Riwayat Transaksi</h3>
                <p class="mt-1 text-sm text-slate-500">Uang masuk dan uang keluar digabung dalam satu history agar lebih ringkas.</p>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-50">
                <tr>
                    <th class="px-6 py-4 text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Tanggal</th>
                    <th class="px-6 py-4 text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Jenis</th>
                    <th class="px-6 py-4 text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">No Bukti</th>
                    <th class="px-6 py-4 text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Keterangan</th>
                    <th class="px-6 py-4 text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Akun</th>
                    <th class="px-6 py-4 text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Uang Masuk</th>
                    <th class="px-6 py-4 text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Uang Keluar</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @forelse (($history ?? []) as $historyRow)
                    <tr>
                        <td class="px-6 py-4 text-slate-600">{{ $historyRow['date']?->format('d/m/Y') }}</td>
                        <td class="px-6 py-4">
                            @if (($historyRow['kind'] ?? '') === 'uang_masuk')
                                <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">Uang Masuk</span>
                            @else
                                <span class="rounded-full bg-rose-50 px-3 py-1 text-xs font-semibold text-rose-700">Uang Keluar</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 font-semibold text-slate-900">{{ $historyRow['reference'] }}</td>
                        <td class="px-6 py-4 text-slate-700">{{ $historyRow['description'] }}</td>
                        <td class="px-6 py-4 text-slate-600">{{ $historyRow['account'] }}</td>
                        <td class="px-6 py-4 font-semibold text-emerald-700">
                            {{ ($historyRow['income'] ?? 0) > 0 ? 'Rp '.number_format((int) $historyRow['income'], 0, ',', '.') : '-' }}
                        </td>
                        <td class="px-6 py-4 font-semibold text-rose-700">
                            {{ ($historyRow['expense'] ?? 0) > 0 ? 'Rp '.number_format((int) $historyRow['expense'], 0, ',', '.') : '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-6 py-8 text-sm text-slate-500" colspan="7">Belum ada riwayat transaksi untuk periode ini.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
