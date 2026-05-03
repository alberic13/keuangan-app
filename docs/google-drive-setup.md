# Setup Google Drive via Google Apps Script

Panduan ini dipakai untuk upload bukti pembayaran ke Google Drive tanpa Google Cloud Console, OAuth client, access token, atau refresh token.

## 1. Buat Google Apps Script

1. Buka `https://script.google.com/`.
2. Klik `New project`.
3. Ubah nama project, misalnya `Upload Bukti Pembayaran E-Keuangan`.
4. Buka file `Code.gs`.
5. Hapus isi bawaan.
6. Tempel kode Apps Script upload.
7. Pastikan ada dua function:
   - `doGet(e)` untuk cek link web app dari browser.
   - `doPost(e)` untuk menerima upload dari Laravel.

## 2. Deploy sebagai Web App

1. Klik `Deploy`.
2. Klik `New deployment`.
3. Klik ikon gear / `Select type`.
4. Pilih `Web app`.
5. Isi `Description`, misalnya `Upload bukti pembayaran`.
6. Pilih `Execute as: Me`.
7. Pilih `Who has access: Anyone`.
8. Klik `Deploy`.
9. Klik `Authorize access` bila diminta.
10. Pilih akun Google Drive tujuan.
11. Klik `Allow`.
12. Salin URL web app yang berakhiran `/exec`.

## 3. Isi file .env

Isi konfigurasi berikut:

```env
GOOGLE_APPS_SCRIPT_UPLOAD_URL=https://script.google.com/macros/s/ISI_URL_SCRIPT_ANDA/exec
GOOGLE_APPS_SCRIPT_SUBFOLDER="Bukti Pembayaran"
```

Konfigurasi lama `GOOGLE_DRIVE_CLIENT_ID`, `GOOGLE_DRIVE_CLIENT_SECRET`, `GOOGLE_DRIVE_ACCESS_TOKEN`, `GOOGLE_DRIVE_REFRESH_TOKEN`, dan `GOOGLE_DRIVE_FOLDER_ID` tidak dipakai lagi.

## 4. Test koneksi

Jalankan dari terminal project:

```bash
php artisan google-drive:test
```

Jika berhasil, terminal akan menampilkan nama file uji dan URL Google Drive.

## 5. Test dari aplikasi

1. Jalankan aplikasi.
2. Buka menu pembayaran.
3. Buat pembayaran dengan metode `Transfer Manual`.
4. Upload bukti pembayaran.
5. Simpan.
6. Buka daftar pembayaran.
7. Klik link bukti pembayaran.

File akan tersimpan di Google Drive melalui Google Apps Script.
