# Sistem Keuangan MAN 2

Project ini dibuat di Laravel 10 dan disusun agar alur fitur aman:

1. Input kas keluar menyimpan data ke tabel `transaksi`.
2. Dashboard membaca rekap dari tabel yang sama.
3. Laporan memakai sumber data yang sama dan bisa diekspor ke CSV.

## Struktur fitur

- `Dashboard`: `/dashboard`
- `Input kas keluar`: `/expenses/create`
- `Laporan keuangan`: `/reports`

## Langkah setup urut

1. Buat database baru di MySQL, misalnya `db_keuangan_man2`.
2. Import file SQL yang sudah Anda buat: `db_keuangan_man2.sql`.
3. Masuk ke folder project:

```powershell
cd "C:\Users\MyBook Hype AMD\OneDrive\Documents\New project\keuangan-app"
```

4. Edit file `.env` dan sesuaikan bagian database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_keuangan_man2
DB_USERNAME=root
DB_PASSWORD=
```

5. Buat symbolic link untuk file nota:

```powershell
php artisan storage:link
```

6. Jika tabel `users` atau `kategori_transaksi` masih kosong, isi data awal:

```powershell
php artisan db:seed
```

Seeder akan membuat:

- user admin: `admin@man2.test`
- password: `password123`
- beberapa kategori pemasukan dan pengeluaran

7. Jalankan aplikasi:

```powershell
php artisan serve
```

8. Buka browser:

```text
http://127.0.0.1:8000
```

## Catatan penting

- Jangan jalankan migrasi untuk tabel inti jika Anda sudah mengandalkan file SQL sendiri.
- Jika ingin menambah fitur pemasukan, gunakan tabel `transaksi` yang sama dan pilih kategori bertipe `pemasukan`.
- Export laporan saat ini berupa CSV agar stabil tanpa dependensi tambahan PDF.
- Tampilan tidak membutuhkan `npm` atau `vite build`, jadi cukup jalankan Laravel saja.
