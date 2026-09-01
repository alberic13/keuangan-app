@extends('layouts.app', ['title' => 'Edit Transaksi'])

@section('content')
    <div class="topbar">
        <div>
            <h2>Edit Transaksi</h2>
            <p>Perbarui data transaksi dari laporan keuangan tanpa mengubah alur utama aplikasi.</p>
        </div>
        <div class="pill">No Referensi: <strong>{{ $transaction->no_referensi }}</strong></div>
    </div>

    @if ($errors->any())
        <div class="alert warning">
            <strong>Data belum bisa diperbarui.</strong>
            <ul class="errors">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="layout-two">
        <article class="card">
            <div class="toolbar">
                <div>
                    <h3>Form Edit</h3>
                    <div class="muted">Silakan perbarui kategori, nominal, tanggal, atau keterangan transaksi sesuai kebutuhan.</div>
                </div>
            </div>

            <form action="{{ route('transactions.update', $transaction->id) }}" method="POST" enctype="multipart/form-data" class="grid">
                @csrf
                @method('PUT')
                <input type="hidden" name="return_url" value="{{ $returnUrl }}">

                <div class="form-grid">
                    <div class="field">
                        <label for="tanggal">Tanggal Transaksi</label>
                        <input type="date" id="tanggal" name="tanggal" value="{{ old('tanggal', $transaction->tanggal?->format('Y-m-d')) }}" required>
                    </div>

                    <div class="field">
                        <label for="no_referensi">No Referensi</label>
                        <input type="text" id="no_referensi" name="no_referensi" value="{{ old('no_referensi', $transaction->no_referensi) }}" required>
                    </div>

                    <div class="field full">
                        <label for="deskripsi_kegiatan">Deskripsi Kegiatan</label>
                        <input type="text" id="deskripsi_kegiatan" name="deskripsi_kegiatan" value="{{ old('deskripsi_kegiatan', $transaction->deskripsi_kegiatan) }}" required>
                    </div>

                    <div class="field">
                        <label for="kategori_id">Kategori Transaksi</label>
                        <select id="kategori_id" name="kategori_id" required>
                            <option value="">Pilih kategori</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('kategori_id', $transaction->kategori_id) == $category->id)>
                                    {{ $category->nama_kategori }} ({{ ucfirst($category->tipe) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field">
                        <label for="nominal">Nominal (Rp)</label>
                        <input type="text" value="Rp {{ number_format($transaction->nominal, 0, ',', '.') }}" disabled>
                        <div class="muted">Nominal tidak bisa diedit.</div>
                    </div>

                    <div class="field">
                        <label>Petugas Input</label>
                        <input type="text" value="{{ auth()->user()->name ?? ($transaction->submitter?->name ?? 'Admin Keuangan') }}" disabled>
                    </div>

                    <div class="field">
                        <label for="bukti_nota">Ganti Bukti Nota</label>
                        <input type="file" id="bukti_nota" name="bukti_nota" accept=".pdf,.jpg,.jpeg,.png">
                    </div>
                </div>

                <div class="helper">
                    @if ($transaction->bukti_nota)
                        Bukti saat ini tersimpan di <code>storage/{{ $transaction->bukti_nota }}</code>. Upload file baru hanya jika ingin mengganti.
                    @else
                        Transaksi ini belum memiliki bukti nota. Anda bisa menambahkannya sekarang bila diperlukan.
                    @endif
                </div>

                <div class="actions">
                    <button type="submit" class="btn">Simpan Perubahan</button>
                    <a href="{{ $returnUrl }}" class="btn secondary">Kembali ke Laporan</a>
                </div>
            </form>
        </article>

        <aside class="card">
            <h3>Catatan</h3>
            <div class="progress-list">
                <div class="progress-row">
                    <strong>1. Perubahan langsung aktif</strong>
                    <div class="muted">Setelah disimpan, dashboard dan laporan akan memakai data terbaru.</div>
                </div>
                <div class="progress-row">
                    <strong>2. Referensi tetap unik</strong>
                    <div class="muted">Nomor referensi tidak boleh sama dengan transaksi lain.</div>
                </div>
                <div class="progress-row">
                    <strong>3. Bukti nota opsional</strong>
                    <div class="muted">Kalau diunggah file baru, file lama akan diganti otomatis.</div>
                </div>
            </div>
        </aside>
    </section>
@endsection
