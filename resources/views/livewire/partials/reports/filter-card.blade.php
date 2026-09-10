<section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
    @php
        $monthLabels = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
        $filter = is_array(($filter ?? null)) ? $filter : ['mode' => 'daily'];
        $mode = $filter['mode'] ?? 'daily';
        $yearNow = now()->year;
        $years = range($yearNow, $yearNow - 5);
    @endphp

    @if (! empty($periodAlert))
        <div class="mb-6 alert warning">{{ $periodAlert }}</div>
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
