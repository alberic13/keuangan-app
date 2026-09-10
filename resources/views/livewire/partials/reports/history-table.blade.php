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
                <th class="px-6 py-4 text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Pembayaran</th>
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
                    <td class="px-6 py-4 text-slate-600">{{ $historyRow['method'] ?? '-' }}</td>
                    <td class="px-6 py-4 font-semibold text-emerald-700">
                        {{ ($historyRow['income'] ?? 0) > 0 ? 'Rp '.number_format((int) $historyRow['income'], 0, ',', '.') : '-' }}
                    </td>
                    <td class="px-6 py-4 font-semibold text-rose-700">
                        {{ ($historyRow['expense'] ?? 0) > 0 ? 'Rp '.number_format((int) $historyRow['expense'], 0, ',', '.') : '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td class="px-6 py-8 text-sm text-slate-500" colspan="8">Belum ada riwayat transaksi untuk periode ini.</td>
                </tr>
            @endforelse
            </tbody>
            <tfoot class="border-t border-slate-200 bg-slate-50">
            <tr>
                <td class="px-6 py-4 text-sm font-semibold text-slate-700" colspan="6">Total Periode</td>
                <td class="px-6 py-4 text-sm font-bold text-emerald-700">
                    Rp {{ number_format((int) (($summary['income'] ?? 0)), 0, ',', '.') }}
                </td>
                <td class="px-6 py-4 text-sm font-bold text-rose-700">
                    Rp {{ number_format((int) (($summary['expense'] ?? 0)), 0, ',', '.') }}
                </td>
            </tr>
            </tfoot>
        </table>
    </div>
</section>
