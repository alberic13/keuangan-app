@php
    $resetDashboardUrl = route('dashboard', request()->except(['month', 'page']));
    $dashboardQuery = request()->except(['month', 'page']);
@endphp

<div class="space-y-8">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-surface-container-lowest p-6 rounded-xl shadow-sm">
            <p class="text-sm font-medium text-on-surface-variant">Total Pembayaran</p>
            <h3 class="text-3xl font-extrabold mt-2 text-on-surface">Rp {{ number_format($summary['total_payments'], 0, ',', '.') }}</h3>
            <p class="mt-2 text-sm text-on-surface-variant">{{ $summary['is_month_filtered'] ? 'Pembayaran pada '.$summary['month_label'] : 'Akumulasi seluruh bulan' }}</p>
        </div>
        <div class="bg-surface-container-lowest p-6 rounded-xl shadow-sm">
            <p class="text-sm font-medium text-on-surface-variant">Sisa Tagihan</p>
            <h3 class="text-3xl font-extrabold mt-2 text-tertiary">Rp {{ number_format($summary['outstanding'], 0, ',', '.') }}</h3>
            <p class="mt-2 text-sm text-on-surface-variant">{{ $summary['is_month_filtered'] ? 'Tagihan periode '.$summary['month_label'] : 'Akumulasi seluruh periode, termasuk tunggakan lama' }}</p>
        </div>
        <div class="bg-gradient-to-br from-primary to-primary-container p-6 rounded-xl shadow-lg text-white">
            <p class="text-sm font-medium text-white/80">Saldo Kas Bersih</p>
            <h3 class="text-3xl font-extrabold mt-2">Rp {{ number_format($summary['net_cash_balance'], 0, ',', '.') }}</h3>
            <p class="mt-2 text-sm text-white/80">{{ $summary['is_month_filtered'] ? 'Selisih pemasukan dan pengeluaran '.$summary['month_label'] : 'Akumulasi semua transaksi kas' }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        <div class="lg:col-span-8 bg-surface-container-low rounded-xl p-8">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h4 class="text-xl font-bold text-on-surface">Tren Pembayaran</h4>
                    <p class="text-sm text-on-surface-variant">6 bulan terakhir</p>
                </div>
                <div class="flex flex-wrap items-center justify-end gap-3">
                    @if ($summary['is_month_filtered'])
                        <a class="inline-flex items-center rounded-xl bg-surface-container-lowest px-4 py-2 text-sm font-semibold text-on-surface hover:bg-white" href="{{ $resetDashboardUrl }}">
                            Keseluruhan Bulan
                        </a>
                    @endif
                    <div class="rounded-full bg-surface-container-lowest px-4 py-2 text-sm font-semibold text-on-surface">
                        {{ $summary['is_month_filtered'] ? $summary['month_label'] : 'Semua Bulan' }}
                    </div>
                </div>
            </div>
            @php $maxTrend = max(array_column($trend, 'amount')) ?: 1; @endphp
            <div class="relative h-64 flex items-end gap-4 pt-10">
                @foreach ($trend as $item)
                    <a class="flex-1 flex flex-col items-center gap-3" href="{{ route('dashboard', array_merge($dashboardQuery, ['month' => $item['month_key']])) }}" title="Lihat ringkasan {{ $item['label'] }}">
                        <div class="w-full rounded-t-lg transition-colors {{ $item['is_selected'] ? 'bg-primary' : 'bg-primary/15 hover:bg-primary/70' }}" style="height: {{ max(16, ($item['amount'] / $maxTrend) * 220) }}px;"></div>
                        <span class="text-xs font-medium text-on-surface-variant">{{ $item['label'] }}</span>
                    </a>
                @endforeach
            </div>
        </div>

        <div class="lg:col-span-4 bg-surface-container-lowest rounded-xl p-6 shadow-sm">
            <h4 class="text-lg font-bold text-on-surface mb-4">Top Tunggakan</h4>
            <p class="mb-4 text-sm text-on-surface-variant">{{ $summary['is_month_filtered'] ? 'Periode '.$summary['month_label'] : 'Seluruh periode aktif' }}</p>
            <div class="space-y-4">
                @forelse ($arrears as $invoice)
                    <div class="rounded-lg bg-surface-container-low p-4">
                        <p class="text-sm font-bold text-on-surface">{{ $invoice->student->full_name }}</p>
                        <p class="text-xs text-on-surface-variant">{{ $invoice->feeType->name }} • {{ $invoice->billingCycle?->period_label }}</p>
                        <p class="text-sm font-semibold text-error mt-2">Rp {{ number_format($invoice->outstanding_amount, 0, ',', '.') }}</p>
                    </div>
                @empty
                    <p class="text-sm text-on-surface-variant">Belum ada tunggakan aktif.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="bg-surface-container-lowest rounded-xl shadow-sm overflow-hidden">
        <div class="px-8 py-6 border-b border-surface-container flex justify-between items-center">
            <div>
                <h4 class="font-headline font-bold text-on-surface">Pratinjau Buku Kas</h4>
                <p class="text-sm text-on-surface-variant">{{ $summary['is_month_filtered'] ? 'Periode '.$summary['month_label'] : '30 hari terakhir' }}</p>
            </div>
            <a class="text-sm font-semibold text-primary" href="{{ route('cash-ledger.index') }}">Buka buku kas</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-surface-container-low">
                <tr>
                    <th class="px-8 py-4 text-xs uppercase tracking-widest text-slate-500">Tanggal</th>
                    <th class="px-8 py-4 text-xs uppercase tracking-widest text-slate-500">Deskripsi</th>
                    <th class="px-8 py-4 text-xs uppercase tracking-widest text-slate-500">Debit</th>
                    <th class="px-8 py-4 text-xs uppercase tracking-widest text-slate-500">Kredit</th>
                    <th class="px-8 py-4 text-xs uppercase tracking-widest text-slate-500">Saldo</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-surface-container">
                @forelse ($ledger as $row)
                    <tr>
                        <td class="px-8 py-4 text-sm">{{ $row['date'] }}</td>
                        <td class="px-8 py-4 text-sm">{{ $row['description'] }}</td>
                        <td class="px-8 py-4 text-sm">Rp {{ number_format($row['debit'], 0, ',', '.') }}</td>
                        <td class="px-8 py-4 text-sm">Rp {{ number_format($row['credit'], 0, ',', '.') }}</td>
                        <td class="px-8 py-4 text-sm font-semibold">Rp {{ number_format($row['balance'], 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td class="px-8 py-6 text-sm text-on-surface-variant" colspan="5">Belum ada data ledger.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
