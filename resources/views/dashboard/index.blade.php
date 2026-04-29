@extends('layouts.app', ['title' => 'Dashboard Alokasi Dana'])

@section('content')
    <div class="topbar">
        <div>
            <h2>Dashboard Alokasi Dana</h2>
            <p>Ringkasan utama.</p>
        </div>
        <div class="pill">Data <strong>transaksi</strong></div>
    </div>

    <section class="grid cards">
        <article class="card">
            <h3>Total Pemasukan</h3>
            <div class="metric">Rp {{ number_format($incomeTotal, 0, ',', '.') }}</div>
            <div class="muted">Total pemasukan.</div>
        </article>
        <article class="card">
            <h3>Total Pengeluaran</h3>
            <div class="metric">Rp {{ number_format($expenseTotal, 0, ',', '.') }}</div>
            <div class="muted">Total pengeluaran.</div>
        </article>
        <article class="card">
            <h3>Saldo Saat Ini</h3>
            <div class="metric">Rp {{ number_format($balanceTotal, 0, ',', '.') }}</div>
            <div class="muted">Pemasukan - pengeluaran.</div>
        </article>
        <article class="card">
            <h3>Transaksi Terbaru</h3>
            <div class="metric">{{ $recentTransactions->count() }}</div>
            <div class="muted">Baris tampil.</div>
        </article>
    </section>

    <section class="layout-two">
        <article class="card">
            <div class="toolbar">
                <div>
                    <h3>Tren 6 Bulan</h3>
                    <div class="muted">Kas keluar per bulan.</div>
                </div>
            </div>

            @php
                $maxMonthlyExpense = max($monthlyExpenses->max('total'), 1);
            @endphp

            <div class="chart-bars">
                @foreach ($monthlyExpenses as $month)
                    <div class="bar-group">
                        <div class="muted">Rp {{ number_format($month['total'], 0, ',', '.') }}</div>
                        <div class="bar" style="height: {{ max(($month['total'] / $maxMonthlyExpense) * 180, 16) }}px;"></div>
                        <strong>{{ $month['label'] }}</strong>
                    </div>
                @endforeach
            </div>
        </article>

        <article class="card">
            <h3>Alokasi Kategori</h3>
            <div class="muted">Porsi pengeluaran.</div>

            @if ($categoryExpenses->isEmpty())
                <div class="empty-state" style="margin-top: 18px;">Belum ada kategori.</div>
            @else
                <div class="progress-list">
                    @foreach ($categoryExpenses as $category)
                        <div class="progress-row">
                            <div class="progress-meta">
                                <strong>{{ $category['nama'] }}</strong>
                                <span>{{ $category['percentage'] }}%</span>
                            </div>
                            <div class="progress-track">
                                <div class="progress-value" style="width: {{ $category['percentage'] }}%;"></div>
                            </div>
                            <div class="muted">Rp {{ number_format($category['total'], 0, ',', '.') }}</div>
                        </div>
                    @endforeach
                </div>
            @endif
        </article>
    </section>

    <section class="card" style="margin-top: 20px;">
        <div class="toolbar">
            <div>
                <h3>Transaksi Terbaru</h3>
                <div class="muted">Data terbaru.</div>
            </div>
            <div class="actions">
                <a href="{{ route('expenses.create') }}" class="btn">Tambah</a>
                <a href="{{ route('reports.index') }}" class="btn secondary">Buka Laporan</a>
            </div>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>No Referensi</th>
                        <th>Kegiatan</th>
                        <th>Kategori</th>
                        <th>Nominal</th>
                        <th>Submitter</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentTransactions as $transaksi)
                        <tr>
                            <td>{{ $transaksi->tanggal?->format('d M Y') }}</td>
                            <td>{{ $transaksi->no_referensi }}</td>
                            <td>{{ $transaksi->deskripsi_kegiatan }}</td>
                            <td><span class="tag">{{ $transaksi->kategori?->nama_kategori ?? '-' }}</span></td>
                            <td class="amount {{ optional($transaksi->kategori)->tipe === 'pemasukan' ? 'income' : 'expense' }}">
                                Rp {{ number_format($transaksi->nominal, 0, ',', '.') }}
                            </td>
                            <td>{{ $transaksi->submitter?->name ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">Belum ada transaksi.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
