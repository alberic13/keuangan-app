@extends('layouts.app', ['title' => 'Laporan Keuangan'])

@section('content')
    <div class="topbar">
        <div>
            <h2>Laporan Keuangan</h2>
            <p>Rekap transaksi berdasarkan periode, kategori, dan tipe transaksi.</p>
        </div>
        <div class="pill">Filter tanggal aktif: <strong>{{ $filters['start_date'] }}</strong> s.d. <strong>{{ $filters['end_date'] }}</strong></div>
    </div>

    <section class="grid cards">
        <article class="card">
            <h3>Total Pemasukan</h3>
            <div class="metric small">Rp {{ number_format($summary['income'], 0, ',', '.') }}</div>
        </article>
        <article class="card">
            <h3>Total Pengeluaran</h3>
            <div class="metric small">Rp {{ number_format($summary['expense'], 0, ',', '.') }}</div>
        </article>
        <article class="card">
            <h3>Saldo Periode</h3>
            <div class="metric small">Rp {{ number_format($summary['balance'], 0, ',', '.') }}</div>
        </article>
        <article class="card">
            <h3>Jumlah Baris</h3>
            <div class="metric small">{{ $summary['count'] }}</div>
        </article>
    </section>

    <section class="card" style="margin-top: 20px;">
        <div class="toolbar">
            <div>
                <h3>Filter Laporan</h3>
                <div class="muted">Gunakan filter ini sebelum ekspor agar hasil laporan tetap konsisten.</div>
            </div>
            <div class="actions">
                <a href="{{ route('reports.export', request()->query()) }}" class="btn">Ekspor CSV</a>
                <a href="{{ route('reports.export-excel', request()->query()) }}" class="btn secondary">Cetak Excel</a>
            </div>
        </div>

        <form action="{{ route('reports.index') }}" method="GET" class="grid">
            <div class="filter-grid">
                <div class="field">
                    <label for="period">Periode</label>
                    <select name="period" id="period">
                        <option value="daily" @selected($filters['period'] === 'daily')>Harian</option>
                        <option value="monthly" @selected($filters['period'] === 'monthly')>Bulanan</option>
                        <option value="yearly" @selected($filters['period'] === 'yearly')>Tahunan</option>
                    </select>
                </div>

                <div class="field">
                    <label for="date">Tanggal Harian</label>
                    <input type="date" id="date" name="date" value="{{ $filters['date'] }}">
                </div>

                <div class="field">
                    <label for="start_date">Mulai</label>
                    <input type="date" id="start_date" name="start_date" value="{{ $filters['start_date'] }}">
                </div>

                <div class="field">
                    <label for="end_date">Sampai</label>
                    <input type="date" id="end_date" name="end_date" value="{{ $filters['end_date'] }}">
                </div>

                <div class="field">
                    <label for="year">Tahun</label>
                    <input type="number" id="year" name="year" value="{{ $filters['year'] }}" min="2000" max="2100">
                </div>

                <div class="field">
                    <label for="category_id">Kategori</label>
                    <select name="category_id" id="category_id">
                        <option value="">Semua kategori</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected($filters['category_id'] == $category->id)>
                                {{ $category->nama_kategori }} ({{ $category->tipe }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="field">
                    <label for="type">Tipe</label>
                    <select name="type" id="type">
                        <option value="">Semua tipe</option>
                        <option value="pemasukan" @selected($filters['type'] === 'pemasukan')>Pemasukan</option>
                        <option value="pengeluaran" @selected($filters['type'] === 'pengeluaran')>Pengeluaran</option>
                    </select>
                </div>
            </div>

            <div class="actions">
                <button type="submit" class="btn">Terapkan Filter</button>
                <a href="{{ route('reports.index') }}" class="btn secondary">Reset</a>
            </div>
        </form>
    </section>

    <section class="card" style="margin-top: 20px;">
        <div class="toolbar">
            <div>
                <h3>Detail Transaksi</h3>
                <div class="muted">Tabel ini memakai sumber data yang sama dengan dashboard, jadi rekap selalu konsisten.</div>
            </div>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>No Referensi</th>
                        <th>Keterangan</th>
                        <th>Kategori</th>
                        <th>Tipe</th>
                        <th>Nominal</th>
                        <th>Submitter</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transactions as $transaksi)
                        <tr>
                            <td>{{ $transaksi->tanggal?->format('d M Y') }}</td>
                            <td>{{ $transaksi->no_referensi }}</td>
                            <td>{{ $transaksi->deskripsi_kegiatan }}</td>
                            <td>{{ $transaksi->kategori?->nama_kategori ?? '-' }}</td>
                            <td><span class="tag">{{ ucfirst($transaksi->kategori?->tipe ?? '-') }}</span></td>
                            <td class="amount {{ optional($transaksi->kategori)->tipe === 'pemasukan' ? 'income' : 'expense' }}">
                                Rp {{ number_format($transaksi->nominal, 0, ',', '.') }}
                            </td>
                            <td>{{ $transaksi->submitter?->name ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">Belum ada data transaksi untuk filter yang dipilih.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination">
            {{ $transactions->links() }}
        </div>
    </section>
@endsection
