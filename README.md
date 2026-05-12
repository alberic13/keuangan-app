# E-Keuangan MAN 2 Surakarta

Aplikasi web internal untuk administrasi keuangan sekolah berbasis `Laravel 11 + Livewire + MariaDB/XAMPP`.

## URL Lokal

- Login: `http://localhost/e-keuangan-man/login`
- Dashboard: `http://localhost/e-keuangan-man/dashboard`

## Akun Demo

Semua akun demo memakai password: `password123`

- `admin_keuangan`
- `bendahara`
- `kepala_madrasah`
- `waka`
- `admin_tu`

Email demo juga sudah tersedia di database seed.

## Database Lokal

Konfigurasi default local (lihat file `.env`) sekarang mengarah ke:

- Host: `127.0.0.1`
- Port: `3306`
- Database: `db_keuangan_man2`
- User: `root`
- Password: kosong

## Struktur Penting

- `docs/`: PRD, workflow, ERD, API spec, backlog
- `docs/kebijakan-akademik-keuangan.md`: kebijakan tipe siswa, biaya, tunggakan, dan akses akademik
- `docs/mockups/`: HTML mockup asli yang dijadikan acuan UI
- `app/`, `resources/views/`, `routes/`: source utama Laravel
- `/Applications/XAMPP/xamppfiles/htdocs/e-keuangan-man-app`: copy source untuk runtime Apache lokal
- `/Applications/XAMPP/xamppfiles/htdocs/e-keuangan-man`: web root publik yang diakses browser

## Menjalankan Ulang Local Setup

Jika ingin refresh database:

```bash
php artisan migrate:fresh --seed
```

## Migrasi dari Database Lama (keuangan-app)

Jika database lama masih memakai tabel `transaksi` dan `kategori_transaksi`, jalankan:

```bash
php artisan migrate
php artisan legacy:import-transaksi
```

## Catatan

- Session local memakai `file` agar login stabil tanpa tabel session tambahan.
- Cache local memakai `file` dan queue memakai `sync` agar setup lokal sederhana.
- Laporan BKU final masih perlu workbook asli sekolah bila ingin hasil visual 100% sama dengan format institusi.
