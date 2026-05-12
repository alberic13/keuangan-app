# Setup Google Drive via Google Apps Script

Panduan ini dipakai untuk upload bukti pembayaran ke Google Drive tanpa Google Cloud Console, OAuth client, access token, atau refresh token.

Yang dipakai aplikasi:

- Upload dikirim Laravel ke Google Apps Script lewat `GOOGLE_APPS_SCRIPT_UPLOAD_URL`.
- Apps Script menyimpan file ke Google Drive akun yang membuat/deploy script.
- Nama subfolder diambil dari `GOOGLE_APPS_SCRIPT_SUBFOLDER`, default: `Bukti Pembayaran`.

## 1. Siapkan folder Google Drive

1. Buka `https://drive.google.com/`.
2. Login dengan akun Google yang akan menjadi pemilik file bukti pembayaran.
3. Klik tombol `+ New` / `+ Baru`.
4. Klik `New folder` / `Folder baru`.
5. Isi nama folder, misalnya `E-Keuangan MAN 2 Surakarta`.
6. Klik `Create` / `Buat`.
7. Buka folder tersebut.
8. Klik tombol `+ New` / `+ Baru`.
9. Klik `New folder` / `Folder baru`.
10. Isi nama subfolder: `Bukti Pembayaran`.
11. Klik `Create` / `Buat`.

Catatan: Apps Script di bawah akan membuat subfolder otomatis kalau belum ada. Langkah folder manual tetap disarankan agar lokasi Drive mudah dicek.

## 2. Buat project Google Apps Script

1. Buka `https://script.google.com/`.
2. Pastikan akun Google di kanan atas adalah akun yang sama dengan akun Google Drive tujuan.
3. Klik `New project` / `Project baru`.
4. Di kiri atas, klik tulisan `Untitled project`.
5. Isi nama project: `Upload Bukti Pembayaran E-Keuangan`.
6. Klik `Rename` / `Ganti nama`.
7. Di panel kiri, klik file `Code.gs`.
8. Blok semua isi bawaan di editor.
9. Hapus isi bawaan tersebut.
10. Tempel kode berikut ke `Code.gs`.

```javascript
const ROOT_FOLDER_NAME = 'E-Keuangan MAN 2 Surakarta';

function doGet() {
  return jsonResponse({
    success: true,
    message: 'Google Apps Script upload endpoint aktif.',
    time: new Date().toISOString(),
  });
}

function doPost(e) {
  try {
    const params = e.parameter || {};
    const encodedFile = params.file;
    const filename = params.filename || `bukti-${Date.now()}.bin`;
    const mimeType = params.mime_type || 'application/octet-stream';
    const subfolderName = params.subfolder || 'Bukti Pembayaran';

    if (!encodedFile) {
      throw new Error('Parameter file kosong.');
    }

    const bytes = Utilities.base64Decode(encodedFile);
    const blob = Utilities.newBlob(bytes, mimeType, filename);

    const rootFolder = getOrCreateFolder_(DriveApp.getRootFolder(), ROOT_FOLDER_NAME);
    const targetFolder = getOrCreateFolder_(rootFolder, subfolderName);
    const file = targetFolder.createFile(blob);

    // Agar link bukti pembayaran bisa dibuka dari aplikasi oleh user yang menerima URL.
    // Jika dokumen sangat sensitif, hapus baris ini dan atur akses folder/file secara manual.
    file.setSharing(DriveApp.Access.ANYONE_WITH_LINK, DriveApp.Permission.VIEW);

    return jsonResponse({
      success: true,
      fileId: file.getId(),
      name: file.getName(),
      mimeType: file.getMimeType(),
      url: file.getUrl(),
    });
  } catch (error) {
    return jsonResponse({
      success: false,
      error: error && error.message ? error.message : String(error),
    });
  }
}

function getOrCreateFolder_(parentFolder, folderName) {
  const folders = parentFolder.getFoldersByName(folderName);

  if (folders.hasNext()) {
    return folders.next();
  }

  return parentFolder.createFolder(folderName);
}

function jsonResponse(payload) {
  return ContentService
    .createTextOutput(JSON.stringify(payload))
    .setMimeType(ContentService.MimeType.JSON);
}
```

11. Klik ikon `Save project` / ikon disket di toolbar.
12. Tunggu tulisan saving selesai.

## 3. Deploy sebagai Web App

1. Di kanan atas Apps Script, klik `Deploy`.
2. Klik `New deployment`.
3. Di jendela deployment, klik ikon gear `Select type`.
4. Klik `Web app`.
5. Di field `Description`, isi: `Upload bukti pembayaran`.
6. Di `Execute as`, pilih `Me`.
7. Di `Who has access`, pilih `Anyone`.
8. Klik `Deploy`.
9. Jika muncul jendela `Authorize access`, klik `Authorize access`.
10. Pilih akun Google Drive tujuan.
11. Jika muncul peringatan `Google hasn't verified this app`, klik `Advanced`.
12. Klik `Go to Upload Bukti Pembayaran E-Keuangan (unsafe)`.
13. Klik `Allow`.
14. Setelah deploy sukses, cari bagian `Web app URL`.
15. Klik `Copy`.
16. Pastikan URL yang disalin berakhiran `/exec`, contoh:

```text
https://script.google.com/macros/s/AKfycbxxxxxxxxxxxxxxxx/exec
```

Jangan pakai URL yang berakhiran `/dev` untuk aplikasi biasa. Pakai `/exec`.

## 3A. Kalau Tampilan Google Pakai Bahasa Indonesia

Bagian error `Google Apps Script belum bisa diakses publik` dibetulkan di halaman deploy Apps Script, bukan di Laravel.

Lokasinya:

1. Buka `https://script.google.com/`.
2. Buka project script upload bukti pembayaran.
3. Di kanan atas, klik `Terapkan`.
4. Klik `Kelola deployment`.
5. Pilih deployment yang tipenya `Aplikasi web`.
6. Klik ikon pensil `Edit`.
7. Pada bagian `Konfigurasi deployment`, cek pilihan berikut:
   - `Jalankan sebagai`: pilih `Saya`.
   - `Siapa saja yang memiliki akses`: pilih `Siapa saja`.
8. Pada bagian `Versi`, pilih `Versi baru`.
9. Isi deskripsi, misalnya `Buka akses upload Laravel`.
10. Klik `Terapkan`.
11. Kalau muncul izin akses, klik `Izinkan akses`.
12. Pilih akun Google yang dipakai untuk Drive.
13. Jika muncul halaman peringatan aplikasi belum diverifikasi:
    - klik `Lanjutan`.
    - klik `Buka Upload Bukti Pembayaran E-Keuangan (tidak aman)`.
    - klik `Izinkan`.
14. Setelah selesai, salin `URL aplikasi web`.
15. Pastikan URL yang disalin berakhiran `/exec`.

Padanan istilah Inggris dan Indonesia:

| Inggris | Indonesia |
| --- | --- |
| Deploy | Terapkan |
| New deployment | Deployment baru |
| Manage deployments | Kelola deployment |
| Web app | Aplikasi web |
| Description | Deskripsi |
| Execute as | Jalankan sebagai |
| Me | Saya |
| Who has access | Siapa saja yang memiliki akses |
| Anyone | Siapa saja |
| Authorize access | Izinkan akses |
| Advanced | Lanjutan |
| Allow | Izinkan |
| Web app URL | URL aplikasi web |

Jika pilihan `Siapa saja` tidak ada, berarti akun Google sekolah/workspace membatasi publikasi Apps Script. Minta admin Google Workspace mengizinkan Web App publik, atau gunakan akun Google lain yang punya pilihan `Siapa saja`.

## 4. Isi file `.env`

1. Buka file `.env` di root project Laravel.
2. Cari baris:

```env
GOOGLE_APPS_SCRIPT_UPLOAD_URL=
GOOGLE_APPS_SCRIPT_SUBFOLDER="Bukti Pembayaran"
```

3. Tempel URL dari Apps Script ke `GOOGLE_APPS_SCRIPT_UPLOAD_URL`.

Contoh:

```env
GOOGLE_APPS_SCRIPT_UPLOAD_URL=https://script.google.com/macros/s/AKfycbxxxxxxxxxxxxxxxx/exec
GOOGLE_APPS_SCRIPT_SUBFOLDER="Bukti Pembayaran"
```

4. Simpan file `.env`.
5. Kalau server Laravel sedang berjalan, hentikan dulu dengan `Ctrl+C`.
6. Jalankan ulang server:

```bash
PATH=/Applications/XAMPP/xamppfiles/bin:$PATH php artisan serve --host=127.0.0.1 --port=8001
```

Konfigurasi lama berikut tidak dipakai lagi:

- `GOOGLE_DRIVE_CLIENT_ID`
- `GOOGLE_DRIVE_CLIENT_SECRET`
- `GOOGLE_DRIVE_ACCESS_TOKEN`
- `GOOGLE_DRIVE_REFRESH_TOKEN`
- `GOOGLE_DRIVE_FOLDER_ID`

## 5. Test link Apps Script dari browser

1. Buka tab browser baru.
2. Tempel `Web app URL` yang berakhiran `/exec`.
3. Tekan `Enter`.
4. Jika benar, browser menampilkan JSON seperti ini:

```json
{"success":true,"message":"Google Apps Script upload endpoint aktif.","time":"..."}
```

Jika muncul halaman login atau akses ditolak, ulangi deploy dan pastikan:

- `Execute as` = `Me`
- `Who has access` = `Anyone`
- URL yang dipakai berakhiran `/exec`

## 6. Test upload dari terminal Laravel

Jalankan dari terminal project:

```bash
PATH=/Applications/XAMPP/xamppfiles/bin:$PATH php artisan google-drive:test
```

Jika berhasil, terminal menampilkan:

```text
Upload via Google Apps Script berhasil.
Nama file: TEST-koneksi-apps-script-...
URL file : https://drive.google.com/...
```

Setelah itu:

1. Buka URL file yang muncul.
2. Pastikan file PDF test bisa dibuka.
3. Buka Google Drive.
4. Masuk ke folder `E-Keuangan MAN 2 Surakarta`.
5. Masuk ke subfolder `Bukti Pembayaran`.
6. Pastikan file test muncul di sana.

## 7. Test dari aplikasi

1. Jalankan Laravel.
2. Buka `http://127.0.0.1:8001/login`.
3. Login sebagai `admin_keuangan` atau `bendahara`.
4. Buka menu `Pembayaran`.
5. Klik tombol untuk input/tambah pembayaran.
6. Cari dan pilih siswa.
7. Pilih tagihan yang akan dibayar.
8. Pilih metode pembayaran `Transfer Manual`.
9. Isi tanggal, akun kas/bank, nominal, dan catatan bila perlu.
10. Di field bukti pembayaran, klik `Choose File` / `Pilih File`.
11. Pilih file `.pdf`, `.jpg`, `.jpeg`, atau `.png`.
12. Klik `Simpan`.
13. Buka daftar pembayaran.
14. Cari pembayaran yang baru dibuat.
15. Klik link bukti pembayaran.
16. Pastikan file terbuka dari Google Drive.

## 8. Kalau mengubah kode Apps Script

Jika kode di `Code.gs` diubah setelah deployment pertama, URL lama tidak otomatis memakai kode terbaru.

1. Klik `Deploy`.
2. Klik `Manage deployments`.
3. Pilih deployment Web app yang sudah ada.
4. Klik ikon pensil `Edit`.
5. Di bagian `Version`, pilih `New version`.
6. Isi `Description`, misalnya `Update upload script`.
7. Klik `Deploy`.
8. Salin lagi `Web app URL` jika berubah.
9. Pastikan `.env` memakai URL `/exec` terbaru.

## 9. Troubleshooting

### `GOOGLE_APPS_SCRIPT_UPLOAD_URL` kosong

Isi URL `/exec` dari Apps Script ke `.env`, lalu restart server Laravel.

### Browser menampilkan `You need access`

Ulangi deploy:

1. Klik `Deploy`.
2. Klik `Manage deployments`.
3. Klik ikon pensil `Edit`.
4. Pastikan `Execute as` = `Me`.
5. Pastikan `Who has access` = `Anyone`.
6. Pilih `New version`.
7. Klik `Deploy`.

Jika akun Google sekolah tidak menampilkan pilihan `Anyone`, kemungkinan admin Google Workspace membatasi publikasi web app. Solusinya pakai akun yang diizinkan admin, atau minta admin mengaktifkan akses external Apps Script web app.

### Test terminal gagal dengan status 401/403

Biasanya deployment belum `Anyone`, URL salah, atau masih memakai URL `/dev`.

### File berhasil upload tapi link tidak bisa dibuka user lain

Pastikan kode masih memiliki baris:

```javascript
file.setSharing(DriveApp.Access.ANYONE_WITH_LINK, DriveApp.Permission.VIEW);
```

Jika tidak ingin `anyone with link`, atur permission folder/file Google Drive secara manual sesuai kebijakan sekolah.

### Upload lama atau gagal untuk file besar

Gunakan file bukti pembayaran yang wajar, misalnya PDF/JPG hasil scan yang sudah dikompres. Apps Script punya batas waktu eksekusi, jadi file terlalu besar bisa gagal.
