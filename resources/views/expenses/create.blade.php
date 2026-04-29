@extends('layouts.app', ['title' => 'Input Kas Keluar'])

@section('content')
    <div class="topbar">
        <div>
            <h2>Input Kas Keluar</h2>
            <p>Input pengeluaran.</p>
        </div>
        <div class="pill">Tabel <strong>transaksi</strong></div>
    </div>

    @if (session('success'))
        <div class="alert success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert warning">
            <strong>Periksa input.</strong>
            <ul class="errors">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($categories->isEmpty() || $submitters->isEmpty())
        <div class="alert warning">
            Isi kategori dan user dulu.
        </div>
    @endif

    <section class="layout-two">
        <article class="card">
            <div class="toolbar">
                <div>
                    <h3>Form</h3>
                    <div class="muted">Isi data transaksi.</div>
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
                        <label for="submitter_id">Petugas Input</label>
                        <select id="submitter_id" name="submitter_id" required>
                            <option value="">Pilih petugas</option>
                            @foreach ($submitters as $submitter)
                                <option value="{{ $submitter->id }}" @selected(old('submitter_id') == $submitter->id)>{{ $submitter->name }}</option>
                            @endforeach
                        </select>
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

                <div class="helper">PDF/JPG/PNG, maks. 5 MB.</div>

                <div class="actions">
                    <button type="submit" class="btn" @disabled($categories->isEmpty() || $submitters->isEmpty())>Simpan Kas Keluar</button>
                    <a href="{{ route('dashboard') }}" class="btn secondary">Kembali</a>
                </div>
            </form>
        </article>

        <aside class="card">
            <h3>Catatan</h3>
            <div class="progress-list">
                <div class="progress-row">
                    <strong>1. Kategori</strong>
                    <div class="muted">Hanya <code>pengeluaran</code>.</div>
                </div>
                <div class="progress-row">
                    <strong>2. Referensi</strong>
                    <div class="muted">Harus unik.</div>
                </div>
                <div class="progress-row">
                    <strong>3. Nota</strong>
                    <div class="muted">Masuk ke <code>storage/app/public/nota</code>.</div>
                </div>
                <div class="progress-row">
                    <strong>4. Sinkron</strong>
                    <div class="muted">Dashboard dan laporan ikut update.</div>
                </div>
            </div>
        </aside>
    </section>
@endsection
