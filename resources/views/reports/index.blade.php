@extends('layouts.app', ['title' => 'Laporan Keuangan'])

@section('content')
    <div class="topbar">
        <div>
            <h2>Laporan Keuangan</h2>
            <p>Lihat ringkasan dan detail transaksi dengan filter yang lebih sederhana.</p>
        </div>
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
                <div class="muted">Pilih periode cepat atau atur rentang tanggal sendiri bila perlu.</div>
            </div>
            <div class="actions">
                <a href="{{ route('reports.export', request()->query()) }}" class="btn">Ekspor PDF</a>
                <a href="{{ route('reports.export.excel', request()->query()) }}" class="btn secondary">Export to Excel</a>
            </div>
        </div>

        <form action="{{ route('reports.index') }}" method="GET" class="grid">
            <div class="filter-grid" style="grid-template-columns: repeat(4, minmax(0, 1fr));">
                <div class="field">
                    <label for="preset">Periode Cepat</label>
                    <select name="preset" id="preset">
                        <option value="hari_ini" @selected($filters['preset'] === 'hari_ini')>Hari ini</option>
                        <option value="bulan_ini" @selected($filters['preset'] === 'bulan_ini')>Bulan ini</option>
                        <option value="tahun_ini" @selected($filters['preset'] === 'tahun_ini')>Tahun ini</option>
                        <option value="custom" @selected($filters['preset'] === 'custom')>Custom</option>
                    </select>
                </div>

                <div class="field">
                    <label for="start_date">Mulai Tanggal</label>
                    <input type="date" id="start_date" name="start_date" value="{{ $filters['start_date'] }}">
                </div>

                <div class="field">
                    <label for="end_date">Sampai Tanggal</label>
                    <input type="date" id="end_date" name="end_date" value="{{ $filters['end_date'] }}">
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

            <div class="actions" style="justify-content: space-between; align-items: center;">
                <div class="muted">
                    Filter aktif: {{ $filterDescription }}
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
                <div class="muted">Urutan transaksi terbaru ditampilkan lebih dulu agar mudah dicek.</div>
            </div>
        </div>

        @if (session('success'))
            <div class="alert success">{{ session('success') }}</div>
        @endif

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
                        <th>Aksi</th>
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
                            <td>
                                <div class="actions action-icons">
                                    <a
                                        href="{{ route('transactions.edit', ['transaction' => $transaksi->id, 'return_url' => request()->fullUrl()]) }}"
                                        class="action-icon"
                                        title="Edit transaksi"
                                        aria-label="Edit transaksi"
                                    >
                                        <svg viewBox="0 0 24 24" aria-hidden="true">
                                            <path d="M4 20h4l10.5-10.5-4-4L4 16v4zm13.7-12.3 1.6-1.6a1.4 1.4 0 0 0 0-2l-1.4-1.4a1.4 1.4 0 0 0-2 0l-1.6 1.6 3.4 3.4z" fill="currentColor"/>
                                        </svg>
                                    </a>
                                    <form action="{{ route('transactions.destroy', $transaksi->id) }}" method="POST" onsubmit="return confirm('Hapus transaksi ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="return_url" value="{{ request()->fullUrl() }}">
                                        <button type="submit" class="action-icon danger" title="Hapus transaksi" aria-label="Hapus transaksi">
                                            <svg viewBox="0 0 24 24" aria-hidden="true">
                                                <path d="M9 3h6l1 2h4v2H4V5h4l1-2zm1 7h2v8h-2v-8zm4 0h2v8h-2v-8zM7 8h10l-1 12H8L7 8z" fill="currentColor"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
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
