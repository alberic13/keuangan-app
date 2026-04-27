@extends('layouts.app', ['title' => 'Input Kas Keluar'])

@section('content')
    <div class="topbar">
        <div>
            <h2>Input Kas Keluar</h2>
            <p>Masukkan transaksi pengeluaran secara tertib agar dashboard dan laporan selalu sinkron.</p>
        </div>
    </div>

    @if (session('success'))
        <div class="alert success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert warning">
            <strong>Data belum bisa disimpan.</strong>
            <ul class="errors">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($categories->isEmpty())
        <div class="alert warning">
            Halaman input membutuhkan data kategori pengeluaran. Jalankan seeder awal atau isi tabelnya terlebih dahulu.
        </div>
    @endif

    <section class="layout-two">
        <article class="card">
            <div class="toolbar">
                <div>
                    <h3>Form Transaksi</h3>
                    <div class="muted">Semua field penting divalidasi agar data tidak bentrok di dashboard dan laporan.</div>
                </div>
            </div>

            <form action="{{ route('expenses.store') }}" method="POST" enctype="multipart/form-data" class="grid">
                @csrf

                <div class="form-grid">
                    <div class="field">
                        <label for="tanggal">Tanggal Transaksi</label>
                        <input type="date" id="tanggal" name="tanggal" value="{{ old('tanggal', now()->format('Y-m-d')) }}" required>
                    </div>

                    <div class="field">
                        <label for="no_referensi">No Referensi</label>
                        <input type="text" id="no_referensi" name="no_referensi" value="{{ old('no_referensi', 'TRX-' . now()->format('YmdHis')) }}" required>
                    </div>

                    <div class="field full">
                        <label for="deskripsi_kegiatan">Deskripsi Kegiatan</label>
                        <input type="text" id="deskripsi_kegiatan" name="deskripsi_kegiatan" value="{{ old('deskripsi_kegiatan') }}" placeholder="Contoh: Pembelian ATK kegiatan ujian" required>
                    </div>

                    <div class="field">
                        <label for="kategori_id">Kategori Pengeluaran</label>
                        <select id="kategori_id" name="kategori_id" required>
                            <option value="">Pilih kategori</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('kategori_id') == $category->id)>{{ $category->nama_kategori }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field">
                        <label>Petugas Input</label>
                        <input type="text" value="Admin Keuangan" disabled>
                    </div>

                    <div class="field">
                        <label for="nominal">Nominal (Rp)</label>
                        <input type="number" id="nominal" name="nominal" min="1" step="0.01" value="{{ old('nominal') }}" required>
                    </div>

                    <div class="field">
                        <label for="bukti_nota">Bukti Nota</label>
                        <input type="file" id="bukti_nota" name="bukti_nota" accept=".pdf,.jpg,.jpeg,.png">
                    </div>
                </div>

                <div class="helper">Petugas input diisi otomatis dari admin yang sedang login. Format nota yang didukung: PDF, JPG, JPEG, PNG. Maksimal 5 MB.</div>

                <div class="actions">
                    <button type="submit" class="btn" @disabled($categories->isEmpty())>Simpan Kas Keluar</button>
                    <a href="{{ route('dashboard') }}" class="btn secondary">Kembali ke Dashboard</a>
                </div>
            </form>
        </article>

        <aside class="card">
            <h3>Catatan Integrasi</h3>
            <div class="progress-list">
                <div class="progress-row">
                    <strong>1. Validasi kategori</strong>
                    <div class="muted">Hanya kategori bertipe <code>pengeluaran</code> yang bisa dipilih.</div>
                </div>
                <div class="progress-row">
                    <strong>2. No referensi unik</strong>
                    <div class="muted">Mencegah transaksi ganda masuk ke laporan.</div>
                </div>
                <div class="progress-row">
                    <strong>3. Bukti nota tersimpan</strong>
                    <div class="muted">File disimpan ke folder <code>storage/app/public/nota</code>.</div>
                </div>
                <div class="progress-row">
                    <strong>4. Data otomatis terbaca</strong>
                    <div class="muted">Setelah simpan, dashboard dan laporan memakai data yang sama tanpa input ulang.</div>
                </div>
            </div>
        </aside>
    </section>
@endsection
