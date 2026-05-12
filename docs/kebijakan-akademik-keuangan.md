# Kebijakan Akademik dan Keuangan

Dokumen ini merangkum kebijakan terbaru yang harus dijaga oleh sistem E-Keuangan.

Panduan lengkap fungsi fitur dan cara pembuatannya ada di `docs/panduan-fitur-dan-cara-pembuatan.md`.

## Akses Akademik

- Sistem tidak memblokir akses materi atau ujian berdasarkan status tunggakan.
- Semua siswa tetap berhak mengikuti proses pembelajaran penuh terlepas dari status administrasi keuangan.

## Data Siswa

- Tipe siswa resmi hanya:
  - `regular` / Reguler
  - `full_day` / Full Day
  - `boarding` / Asrama
- Tipe siswa dikunci di UI agar tidak bertambah menjadi kategori bebas.
- Pembaruan kenaikan kelas dilakukan melalui import massal Excel. Jika NIS atau NISN sudah ada, baris import akan memperbarui data siswa tersebut, bukan membuat siswa baru.

## Biaya dan Tagihan

- Admin tetap dapat menambah jenis biaya dan kode transaksi secara mandiri.
- SPP memakai satu tarif umum untuk semua angkatan.
- Dana Kegiatan ditargetkan untuk siswa Full Day dan dibayarkan satu kali setahun.
- Uang Makan ditargetkan untuk siswa Asrama dan ditagih bulanan.
- Tunggakan tahun ajaran sebelumnya tetap disimpan sebagai invoice terbuka. Pergantian tahun tidak menghapus atau menutup utang siswa lama.
