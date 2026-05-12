# Panduan Fitur dan Cara Pembuatan

Dokumen ini merangkum fungsi semua fitur utama aplikasi E-Keuangan MAN 2 Surakarta dan cara membuat atau mengembangkannya. Dokumen ini ditujukan untuk developer, admin teknis, dan bahan penjelasan ke klien.

## 1. Gambaran Aplikasi

E-Keuangan adalah aplikasi administrasi keuangan sekolah berbasis Laravel. Aplikasi dipakai untuk mengelola data siswa, jenis biaya, tagihan, pembayaran, kas masuk/keluar, laporan, audit log, dan user internal sekolah.

### Stack teknis

- Backend: Laravel 11
- Frontend server-rendered: Blade dan Livewire
- Database: MySQL
- Hak akses: Spatie Laravel Permission
- PDF: `barryvdh/laravel-dompdf`
- Excel/import: PhpSpreadsheet dan Maatwebsite Excel
- Storage bukti pembayaran: local storage dan opsi Google Drive Apps Script

### Struktur modul utama

- Route web: `routes/web.php`
- Route API: `routes/api.php`
- Halaman Livewire: `app/Livewire`
- Controller web: `app/Http/Controllers/Web`
- Controller API: `app/Http/Controllers/Api`
- Service bisnis: `app/Services`
- Model data: `app/Models`
- Tampilan Blade: `resources/views`
- Migrasi database: `database/migrations`
- Seeder awal: `database/seeders/DatabaseSeeder.php`

## 2. Kebijakan Bisnis yang Wajib Dijaga

### Akses akademik

- Sistem tidak memblokir akses materi atau ujian karena tunggakan.
- Semua siswa tetap boleh mengikuti pembelajaran penuh terlepas dari status pembayaran.

### Tipe siswa

Tipe siswa resmi hanya:

- `regular`: Reguler
- `full_day`: Full Day
- `boarding`: Asrama

Tipe siswa tidak dibuat bebas oleh admin agar kategori data tetap konsisten.

### Biaya

- SPP memakai satu nominal umum untuk semua angkatan.
- Dana Kegiatan berlaku untuk siswa Full Day dan dibayarkan satu kali setahun.
- Uang Makan berlaku untuk siswa Asrama dan ditagih bulanan.
- Admin tetap dapat membuat jenis biaya baru beserta kode transaksinya.

### Tunggakan

- Tunggakan tahun sebelumnya tetap berupa invoice aktif dengan status `unpaid` atau `partial`.
- Pergantian tahun ajaran tidak boleh menghapus atau menutup otomatis utang siswa lama.

## 3. Fitur Login dan Hak Akses

### Fungsi fitur

- Mengizinkan user internal login.
- Menolak user yang tidak aktif.
- Memisahkan hak akses berdasarkan role.
- Menyediakan logout.

### File terkait

- `routes/web.php`
- `routes/api.php`
- `app/Http/Controllers/Auth/AuthenticatedSessionController.php`
- `app/Http/Controllers/Api/AuthController.php`
- `app/Http/Middleware/EnsureUserIsActive.php`
- `app/Models/User.php`
- `database/migrations/2026_04_17_000100_create_permission_tables.php`

### Cara pakai

1. User membuka `/login`.
2. User memasukkan username/email dan password.
3. Sistem mengecek kredensial dan status aktif user.
4. Jika valid, user diarahkan ke dashboard.
5. Jika tidak valid, sistem menampilkan pesan gagal login.

### Cara membuat

1. Buat tabel user dan permission.
2. Tambahkan field user seperti `username`, `email`, `password`, `is_active`, dan `last_login_at`.
3. Pasang Spatie Permission untuk role dan permission.
4. Buat controller login web dan API.
5. Buat middleware `active` agar user nonaktif tidak dapat masuk.
6. Pasang middleware `auth` dan `active` pada route internal.
7. Buat seeder role awal seperti `admin_keuangan`, `bendahara`, dan user read-only.

## 4. Fitur Dashboard

### Fungsi fitur

- Menampilkan ringkasan total pembayaran.
- Menampilkan sisa tagihan atau tunggakan.
- Menampilkan saldo kas bersih.
- Menampilkan tren pembayaran 6 bulan terakhir.
- Menampilkan top tunggakan.
- Menampilkan pratinjau buku kas.

### File terkait

- `app/Livewire/DashboardPage.php`
- `resources/views/livewire/dashboard-page.blade.php`
- `app/Services/ReportService.php`
- `app/Http/Controllers/Api/DashboardController.php`

### Cara pakai

1. User membuka `/dashboard`.
2. Sistem menghitung summary dari invoice, payment, dan cash ledger.
3. User dapat memilih bulan dari grafik tren.
4. Jika bulan dipilih, data dashboard difilter ke bulan tersebut.
5. Jika tidak ada filter bulan, tunggakan dihitung dari seluruh periode aktif.

### Cara membuat

1. Buat service laporan, misalnya `ReportService`.
2. Tambahkan method `dashboardSummary`, `paymentTrend`, `recentPayments`, dan `arrears`.
3. Query total pembayaran dari tabel `payments`.
4. Query outstanding dari tabel `invoices` dengan status `unpaid` dan `partial`.
5. Query kas masuk dan kas keluar dari `cash_ledger_entries`.
6. Buat Livewire page untuk mengirim data summary ke Blade.
7. Buat tampilan card KPI, grafik sederhana, daftar tunggakan, dan tabel kas.

## 5. Fitur Master Siswa

### Fungsi fitur

- Menampilkan daftar siswa.
- Mencari dan memfilter siswa berdasarkan angkatan, kelas, jurusan, tipe siswa, dan status.
- Menambah siswa manual.
- Mengedit data siswa.
- Mengaktifkan atau menonaktifkan siswa.
- Menjaga histori siswa walaupun siswa sudah nonaktif.

### File terkait

- `app/Livewire/StudentsPage.php`
- `app/Livewire/StudentCreatePage.php`
- `resources/views/livewire/students-page.blade.php`
- `resources/views/livewire/student-create-page.blade.php`
- `app/Http/Controllers/Web/StudentManagementController.php`
- `app/Http/Controllers/Api/StudentController.php`
- `app/Models/Student.php`
- `app/Models/StudentType.php`

### Data utama

- `nis`
- `nisn`
- `full_name`
- `class_id`
- `major_id`
- `batch_id`
- `student_type`
- `is_active`
- `enrollment_date`
- `exit_date`

### Cara pakai

1. Admin membuka menu siswa.
2. Admin melihat daftar siswa dan memakai filter jika diperlukan.
3. Untuk tambah siswa, admin mengisi form siswa.
4. Sistem memvalidasi NIS/NISN agar tidak duplikat.
5. Admin dapat menonaktifkan siswa yang keluar atau lulus.
6. Siswa nonaktif tidak mendapat tagihan baru, tetapi histori tetap tampil di laporan.

### Cara membuat

1. Buat tabel `batches`, `classes`, `majors`, `student_types`, dan `students`.
2. Buat model relasi: siswa belongs to batch, class, major.
3. Buat controller store/update/activate/deactivate.
4. Buat validasi NIS dan NISN unik.
5. Buat Livewire page daftar siswa.
6. Buat halaman form siswa.
7. Buat UI referensi untuk menambah angkatan, kelas, dan jurusan.
8. Kunci tipe siswa agar hanya Reguler, Full Day, dan Asrama.
9. Catat audit log setiap create/update/status change.

## 6. Fitur Import Siswa Massal

### Fungsi fitur

- Mengunduh template import siswa.
- Upload file Excel.
- Preview validasi per baris.
- Commit import setelah admin menyetujui preview.
- Menambah siswa baru.
- Memperbarui siswa lama jika NIS atau NISN sudah ada.
- Menyimpan log import dan detail error per baris.

### File terkait

- `app/Livewire/ImportsPage.php`
- `resources/views/livewire/imports-page.blade.php`
- `app/Http/Controllers/Web/StudentImportController.php`
- `app/Http/Controllers/Api/StudentImportController.php`
- `app/Services/StudentImportService.php`
- `app/Models/ImportLog.php`
- `app/Models/ImportLogRow.php`
- `tools/generate_student_import_excel.php`

### Format minimum

- `nis`
- `nisn`
- `full_name`
- `class`
- `major`
- `batch`
- `student_type`
- `is_active`

### Cara pakai

1. Admin membuka menu Import Siswa.
2. Admin mengunduh template.
3. Admin mengisi file Excel.
4. Admin upload file.
5. Sistem menampilkan preview valid dan invalid.
6. Admin melakukan commit jika data sudah benar.
7. Sistem menyimpan siswa baru atau memperbarui siswa lama.
8. Sistem menyimpan log import.

### Alur kenaikan kelas

Untuk kenaikan kelas tahunan, admin tidak perlu memindahkan siswa satu per satu. Sistem memakai NIS/NISN sebagai kunci pencocokan siswa lama.

Contoh: siswa kelas 10 naik ke kelas 11.

1. Admin membuka data siswa tahun berjalan.
2. Admin menyiapkan file Excel dengan kolom minimal `nis` atau `nisn`, `full_name`, `class`, `major`, `batch`, `student_type`, dan `is_active`.
3. Untuk siswa kelas 10, admin mengganti kolom `class` menjadi kelas 11 tujuan, misalnya dari `X-A` menjadi `XI-MIPA 1` atau `XI-IPS 1`.
4. Kolom `batch` tetap mengikuti definisi sekolah. Jika `batch` dipakai sebagai angkatan masuk, nilainya tidak perlu berubah. Jika dipakai sebagai tahun ajaran aktif, nilainya diganti ke tahun ajaran baru.
5. Admin upload file Excel ke menu Import Siswa.
6. Sistem preview semua baris.
7. Saat commit, sistem mencari siswa berdasarkan NIS/NISN.
8. Jika siswa ditemukan, sistem memperbarui kelas, jurusan, angkatan, tipe siswa, dan status aktif.
9. Jika siswa tidak ditemukan, sistem membuat data siswa baru.
10. Siswa lulus/keluar dapat diberi `is_active = false` agar tidak ikut tagihan baru, tetapi histori tetap tersimpan.

Dengan pola ini, 1000 siswa bisa dipindahkan lewat satu file Excel. Admin hanya perlu memastikan daftar kelas tujuan sudah dibuat di master kelas sebelum import.

### Rekomendasi fitur lanjutan kenaikan kelas

Untuk membuat proses lebih cepat lagi, sistem dapat ditambah menu khusus **Kenaikan Kelas**:

1. Admin memilih tahun ajaran sumber dan tahun ajaran tujuan.
2. Sistem menampilkan mapping kelas, misalnya:
   - `X-A` -> `XI-MIPA 1`
   - `XI-MIPA 1` -> `XII-MIPA 1`
   - `XII-MIPA 1` -> nonaktif/lulus
3. Admin klik preview.
4. Sistem menampilkan jumlah siswa yang akan dipindah per kelas.
5. Admin klik proses.
6. Sistem update `class_id`, opsi `batch_id`, dan status siswa secara massal.
7. Sistem menyimpan audit log dan ringkasan hasil.

Menu khusus ini tetap harus menyediakan preview sebelum commit agar admin bisa memeriksa hasil mapping sebelum data berubah.

### Cara membuat

1. Buat endpoint download template.
2. Buat service untuk membaca file Excel dengan PhpSpreadsheet.
3. Normalisasi header agar spasi dan huruf besar/kecil tidak mengganggu.
4. Validasi referensi batch, class, major, dan student type.
5. Validasi NIS/NISN minimal salah satu wajib ada.
6. Simpan preview ke file sementara dengan token.
7. Buat endpoint commit berdasarkan preview token.
8. Saat commit, cari siswa lama dari NIS atau NISN.
9. Jika siswa ada, update data; jika tidak ada, create data.
10. Simpan `import_logs` dan `import_log_rows`.
11. Catat audit log untuk create/update dari import.

## 7. Fitur Master Jenis Biaya

### Fungsi fitur

- Admin dapat membuat jenis biaya baru.
- Setiap jenis biaya punya kode transaksi.
- Jenis biaya memiliki kategori, frekuensi tagihan, aturan cicilan, dan target siswa.
- SPP selalu monthly, berlaku semua siswa, dan tidak boleh cicilan.
- Dana Kegiatan one-time dan ditujukan ke Full Day.
- Uang Makan monthly dan ditujukan ke Asrama.

### File terkait

- `app/Livewire/FeesPage.php`
- `resources/views/livewire/fees-page.blade.php`
- `app/Http/Controllers/Web/FeeManagementController.php`
- `app/Http/Controllers/Api/FeeController.php`
- `app/Models/FeeType.php`

### Field utama

- `code`
- `name`
- `category`
- `installment_allowed`
- `billing_frequency`
- `applies_to`
- `is_active`

### Cara pakai

1. Admin membuka menu Master Bayar.
2. Admin mengisi kode dan nama biaya.
3. Admin memilih kategori biaya.
4. Sistem menormalisasi aturan berdasarkan kategori.
5. Jenis biaya aktif dapat dipakai untuk membuat skema tarif dan generate tagihan.

### Cara membuat

1. Buat tabel `fee_types`.
2. Buat validasi kode unik.
3. Buat enum kategori: `spp`, `activity`, `meal`, `other`.
4. Buat normalisasi:
   - `spp`: monthly, applies to all, no installment.
   - `activity`: one_time, installment allowed, target bisa Full Day.
   - `meal`: monthly, applies to boarding, no installment.
   - `other`: mengikuti input admin.
5. Buat form web dan endpoint API.
6. Catat audit log setiap create/update.

## 8. Fitur Skema Tarif

### Fungsi fitur

- Menentukan nominal biaya yang berlaku pada periode tertentu.
- Mendukung effective start dan effective end.
- Mencegah overlap tarif untuk jenis biaya yang sama.
- SPP dikunci sebagai tarif umum semua angkatan.
- Skema yang tampil pada halaman utama hanya skema aktif.

### File terkait

- `app/Livewire/FeesPage.php`
- `app/Http/Controllers/Web/FeeManagementController.php`
- `app/Http/Controllers/Api/FeeController.php`
- `app/Models/FeeScheme.php`
- `database/migrations/2026_05_12_000000_align_academic_billing_policy.php`

### Cara pakai

1. Admin memilih jenis biaya.
2. Admin mengisi nominal.
3. Admin memilih periode berlaku.
4. Jika biaya adalah SPP, sistem otomatis mengosongkan `batch_id`.
5. Sistem menolak skema yang overlap.
6. Skema aktif dipakai saat generate tagihan.

### Cara membuat

1. Buat tabel `fee_schemes`.
2. Relasikan `fee_scheme` ke `fee_type`.
3. Tambahkan `batch_id` nullable untuk biaya yang suatu saat perlu spesifik angkatan, kecuali SPP.
4. Buat validasi nominal minimal 1.
5. Buat validasi tanggal `effective_end` setelah atau sama dengan `effective_start`.
6. Buat fungsi `normalizeFeeScheme` agar SPP selalu `batch_id = null`.
7. Buat fungsi cek overlap tarif aktif.
8. Query halaman hanya menampilkan `is_active = true`.

## 9. Fitur Billing Cycle

### Fungsi fitur

- Membuat periode tagihan bulanan.
- Menentukan tanggal jatuh tempo.
- Membuka atau menutup periode tagihan.
- Periode tertutup tidak dapat digunakan generate tagihan baru.

### File terkait

- `app/Livewire/BillingPage.php`
- `resources/views/livewire/billing-page.blade.php`
- `app/Http/Controllers/Web/BillingManagementController.php`
- `app/Http/Controllers/Api/BillingController.php`
- `app/Models/BillingCycle.php`

### Cara pakai

1. Admin membuka menu Manajemen Tagihan.
2. Admin membuat periode, misalnya Mei 2026.
3. Admin mengisi due date.
4. Periode berstatus `open`.
5. Jika periode selesai, admin dapat menutup cycle.

### Cara membuat

1. Buat tabel `billing_cycles`.
2. Tambahkan unique index pada `month` dan `year`.
3. Buat controller untuk create/update/close.
4. Validasi month 1-12 dan year minimal tahun yang disepakati.
5. Service generate tagihan harus mengecek status cycle sebelum membuat invoice.

## 10. Fitur Generate Tagihan

### Fungsi fitur

- Membuat invoice massal berdasarkan jenis biaya dan periode.
- Mengambil siswa aktif sesuai filter.
- Mencegah invoice duplikat.
- Mengambil skema tarif aktif sesuai tanggal due date.
- Menghasilkan ringkasan generated, skipped, dan failed.

### File terkait

- `app/Services/BillingService.php`
- `app/Http/Controllers/Web/BillingManagementController.php`
- `app/Http/Controllers/Api/BillingController.php`
- `app/Models/Invoice.php`
- `app/Support/DocumentNumber.php`

### Aturan khusus

- SPP dibuat bulanan untuk semua siswa aktif dengan nominal umum.
- Dana Kegiatan dibuat untuk Full Day.
- Uang Makan dibuat bulanan untuk Asrama.
- Invoice awal berstatus `unpaid`.
- Jika invoice sudah ada untuk siswa, fee, periode, dan referensi yang sama, sistem skip.

### Cara pakai

1. Admin membuka Manajemen Tagihan.
2. Admin memilih billing cycle.
3. Admin memilih jenis biaya.
4. Admin dapat memilih filter siswa.
5. Admin klik generate.
6. Sistem membuat invoice dan menampilkan ringkasan.

### Cara membuat

1. Buat tabel `invoices`.
2. Buat `BillingService`.
3. Method `generate` mengambil `fee_type` dan `billing_cycle`.
4. Tolak generate jika cycle closed.
5. Query siswa aktif sesuai `applies_to` dan filter.
6. Cari skema tarif aktif dengan `findSchemeForStudent`.
7. Cek invoice duplikat dengan kombinasi siswa, fee, cycle, dan reference.
8. Buat invoice dengan nomor otomatis.
9. Catat audit log.
10. Return hasil generate.

## 11. Fitur Invoice dan Cetak Tagihan

### Fungsi fitur

- Menampilkan daftar invoice.
- Menampilkan status unpaid, partial, paid, dan void.
- Mencetak invoice/tagihan ke PDF.
- Melakukan void invoice jika belum memiliki pembayaran.

### File terkait

- `resources/views/livewire/billing-page.blade.php`
- `resources/views/prints/invoice.blade.php`
- `app/Http/Controllers/Web/BillingManagementController.php`
- `app/Http/Controllers/Api/BillingController.php`
- `app/Models/Invoice.php`

### Cara pakai

1. Admin melihat daftar invoice pada menu Manajemen Tagihan.
2. Admin dapat mencetak invoice.
3. Admin dapat void invoice yang belum memiliki pembayaran.
4. Invoice void tidak dihitung sebagai tunggakan aktif.

### Cara membuat

1. Buat relasi invoice ke student, fee type, billing cycle, dan payment items.
2. Buat tampilan daftar invoice.
3. Buat Blade print invoice.
4. Gunakan DomPDF untuk stream PDF.
5. Saat void, cek apakah payment item sudah ada.
6. Jika sudah ada payment, tolak void.
7. Catat audit log void.

## 12. Fitur Pembayaran

### Fungsi fitur

- Mencatat pembayaran siswa.
- Satu pembayaran dapat membayar beberapa invoice.
- Mendukung metode cash atau transfer.
- Mendukung upload bukti pembayaran.
- Menghitung ulang status invoice.
- Membuat entry kas masuk otomatis.
- Mengedit payment posted dengan alasan wajib.
- Mencetak kwitansi.

### File terkait

- `app/Livewire/PaymentsPage.php`
- `app/Livewire/PaymentFormPage.php`
- `resources/views/livewire/payments-page.blade.php`
- `resources/views/livewire/payment-form-page.blade.php`
- `resources/views/prints/receipt.blade.php`
- `app/Http/Controllers/Web/PaymentManagementController.php`
- `app/Http/Controllers/Api/PaymentController.php`
- `app/Services/PaymentService.php`
- `app/Services/GoogleDrivePaymentProofService.php`

### Aturan pembayaran

- SPP tidak boleh dibayar parsial.
- Uang Makan harus dibayar penuh per invoice.
- Biaya dengan `installment_allowed = true` boleh dicicil.
- Nominal tidak boleh lebih besar dari outstanding.
- Invoice paid atau void tidak boleh dibayar.
- Edit payment wajib punya `edited_reason`.

### Cara pakai

1. Bendahara membuka menu Pembayaran.
2. Bendahara memilih siswa.
3. Sistem menampilkan invoice terbuka.
4. Bendahara memilih invoice dan nominal pembayaran.
5. Bendahara memilih akun kas/bank.
6. Sistem menyimpan payment.
7. Sistem memperbarui invoice dan ledger.
8. Bendahara dapat mencetak kwitansi.

### Cara membuat

1. Buat tabel `payments` dan `payment_items`.
2. Buat `PaymentService`.
3. Saat create, buat payment header dengan total sementara 0.
4. Validasi item invoice satu per satu.
5. Simpan payment item.
6. Hitung total payment.
7. Upload bukti pembayaran jika ada.
8. Recalculate invoice: unpaid, partial, atau paid.
9. Buat ledger kas masuk.
10. Catat audit log.
11. Untuk edit, hapus item lama, recalculasi invoice lama, simpan item baru, dan recalculasi ulang.

## 13. Fitur Upload Bukti Pembayaran ke Google Drive

### Fungsi fitur

- Menyimpan bukti pembayaran dari form payment.
- Mengirim file ke Google Apps Script atau storage yang dikonfigurasi.
- Menyimpan URL/ID file pada payment.
- Menghapus bukti lama jika payment diedit dan bukti baru berhasil tersimpan.

### File terkait

- `app/Services/GoogleDrivePaymentProofService.php`
- `config/filesystems.php`
- `.env`
- `docs/google-drive-setup.md`

### Cara pakai

1. Bendahara mengisi form pembayaran.
2. Bendahara upload bukti file PDF/JPG/PNG/WebP.
3. Sistem upload file.
4. Sistem menyimpan metadata bukti pada payment.

### Cara membuat

1. Tambahkan kolom bukti pembayaran pada tabel `payments`.
2. Buat service khusus upload.
3. Ambil konfigurasi endpoint/folder dari `.env`.
4. Validasi file dari controller.
5. Pada transaksi payment, upload file sebelum final update.
6. Jika transaksi gagal, hapus file yang sudah terupload.
7. Jika edit mengganti bukti, hapus bukti lama setelah bukti baru berhasil.

## 14. Fitur Kas, Bank, dan Pengeluaran

### Fungsi fitur

- Mengelola akun kas dan bank.
- Mengelola kategori pengeluaran.
- Mencatat pengeluaran.
- Mengedit pengeluaran.
- Menghapus pengeluaran.
- Membuat ledger kas keluar otomatis.
- Menampilkan buku kas.

### File terkait

- `app/Livewire/ExpensesPage.php`
- `app/Livewire/CashLedgerPage.php`
- `resources/views/livewire/expenses-page.blade.php`
- `resources/views/livewire/cash-ledger-page.blade.php`
- `app/Http/Controllers/Web/CashManagementController.php`
- `app/Http/Controllers/Api/CashController.php`
- `app/Services/ExpenseService.php`
- `app/Models/CashAccount.php`
- `app/Models/Expense.php`
- `app/Models/CashLedgerEntry.php`

### Cara pakai

1. Admin membuat akun kas/bank.
2. Admin membuat kategori pengeluaran.
3. Bendahara mencatat pengeluaran.
4. Sistem membuat ledger dengan direction `out`.
5. Pengeluaran dapat diedit atau dihapus sesuai hak akses.

### Cara membuat

1. Buat tabel `cash_accounts`, `expense_categories`, `expenses`, dan `cash_ledger_entries`.
2. Buat service `ExpenseService`.
3. Saat create expense, validasi akun kas/bank aktif.
4. Buat nomor pengeluaran otomatis.
5. Simpan expense.
6. Buat ledger kas keluar.
7. Catat audit log.
8. Saat update, sinkronkan ulang ledger.
9. Saat delete, hapus ledger terkait dan attachment.

## 15. Fitur Buku Kas dan Ledger

### Fungsi fitur

- Menyimpan semua kas masuk dan kas keluar.
- Kas masuk berasal dari payment.
- Kas keluar berasal dari expense.
- Menjadi sumber laporan BKU dan buku pembantu.

### File terkait

- `app/Models/CashLedgerEntry.php`
- `app/Services/PaymentService.php`
- `app/Services/ExpenseService.php`
- `app/Services/ReportService.php`

### Cara membuat

1. Buat tabel `cash_ledger_entries`.
2. Field wajib: tanggal, akun, direction, source type, source id, amount, description.
3. Saat payment disimpan, buat ledger `direction = in`.
4. Saat expense disimpan, buat ledger `direction = out`.
5. Saat payment/expense diedit, hapus ledger lama dan buat ledger baru.
6. Laporan membaca data dari ledger, bukan menghitung ulang dari tabel sumber.

## 16. Fitur Laporan

### Fungsi fitur

- Laporan kas harian.
- Ringkasan bulanan.
- Ringkasan tahunan.
- Buku kas umum.
- Buku kas tunai.
- Buku pembantu penerimaan cash.
- Buku pembantu penerimaan bank.
- Buku pembantu penerimaan cash dan bank.
- Ledger per siswa.
- Laporan tunggakan.
- Export PDF/Excel/CSV sesuai kebutuhan route.

### File terkait

- `app/Livewire/ReportsPage.php`
- `resources/views/livewire/reports-page.blade.php`
- `resources/views/prints/report-export.blade.php`
- `resources/views/reports/excel.blade.php`
- `app/Http/Controllers/Api/ReportController.php`
- `app/Services/ReportService.php`

### Cara pakai

1. User membuka menu Laporan.
2. User memilih tipe laporan.
3. User mengisi filter tanggal, bulan, tahun, siswa, atau akun.
4. Sistem menampilkan data.
5. User dapat export laporan.

### Cara membuat

1. Buat `ReportService` sebagai pusat query laporan.
2. Gunakan `cash_ledger_entries` untuk laporan kas.
3. Gunakan `invoices` untuk tunggakan.
4. Gunakan relasi siswa, payment, dan invoice untuk ledger per siswa.
5. Buat controller export.
6. Buat Blade print/export.
7. Pastikan laporan tidak menghapus histori siswa nonaktif.

## 17. Fitur Audit Log

### Fungsi fitur

- Mencatat aktivitas penting.
- Menyimpan before dan after data.
- Menyimpan user pelaku.
- Menyimpan alasan edit jika ada.
- Menjadi dasar audit internal.

### File terkait

- `app/Livewire/AuditLogsPage.php`
- `resources/views/livewire/audit-logs-page.blade.php`
- `app/Http/Controllers/Api/AuditLogController.php`
- `app/Services/AuditLogService.php`
- `app/Models/AuditLog.php`

### Aktivitas yang dicatat

- Create/update siswa.
- Import siswa.
- Create/update fee type.
- Create/update fee scheme.
- Generate invoice.
- Void invoice.
- Create/update payment.
- Create/update/delete expense.
- Update user.

### Cara membuat

1. Buat tabel `audit_logs`.
2. Buat service `AuditLogService`.
3. Isi kolom actor, action, auditable type/id, before, after, reason, dan timestamp.
4. Panggil service audit setelah operasi penting.
5. Buat halaman audit log dengan filter dan pencarian.

## 18. Fitur User Management

### Fungsi fitur

- Menampilkan daftar user.
- Membuat user internal.
- Mengedit user.
- Mengubah status aktif/nonaktif.
- Menghapus user jika diizinkan.
- Mengatur role.

### File terkait

- `app/Livewire/UsersPage.php`
- `resources/views/livewire/users-page.blade.php`
- `app/Http/Controllers/Web/UserManagementController.php`
- `app/Http/Controllers/Api/UserController.php`
- `app/Models/User.php`

### Cara pakai

1. Admin membuka menu User.
2. Admin membuat user baru.
3. Admin memilih role.
4. Admin dapat menonaktifkan user yang tidak boleh login.

### Cara membuat

1. Tambahkan field user yang dibutuhkan.
2. Gunakan Spatie Permission untuk role.
3. Buat controller create/update/status.
4. Validasi email/username unik.
5. Hash password.
6. Sync role user.
7. Catat audit log perubahan user.

## 19. Fitur API

### Fungsi fitur

- Menyediakan akses data untuk integrasi atau frontend lain.
- Endpoint memakai middleware `auth` dan `active`.
- Response memakai format JSON standar.

### File terkait

- `routes/api.php`
- `app/Http/Controllers/Api`
- `app/Http/Controllers/Concerns/ApiResponses.php`

### Kelompok endpoint

- Auth: `/api/auth/login`, `/api/auth/logout`, `/api/auth/me`
- Referensi: `/api/batches`, `/api/classes`, `/api/majors`
- Siswa: `/api/students`
- Import: `/api/imports/students/*`
- Fee: `/api/fee-types`, `/api/fee-schemes`
- Billing: `/api/billing-cycles`, `/api/billing/generate`, `/api/invoices`
- Payment: `/api/payments`
- Cash: `/api/cash-accounts`, `/api/expenses`, `/api/cash-ledger`
- Reports: `/api/reports/*`
- Dashboard: `/api/dashboard/*`
- Audit: `/api/audit-logs`
- User: `/api/users`

### Cara membuat

1. Buat controller API per modul.
2. Gunakan service yang sama dengan web controller agar business rule tidak dobel.
3. Gunakan validasi request.
4. Gunakan response helper standar.
5. Pasang middleware `auth` dan `active`.
6. Dokumentasikan endpoint di `docs/api_spec.md`.

## 20. Cara Membuat Fitur Baru

Gunakan pola berikut agar fitur baru konsisten dengan aplikasi.

### Langkah 1 - Definisikan kebutuhan bisnis

Tulis:

- Siapa usernya.
- Data apa yang diinput.
- Output yang diharapkan.
- Rule bisnis.
- Error case.
- Hak akses.

### Langkah 2 - Buat database

1. Buat migration.
2. Tentukan kolom wajib.
3. Tambahkan foreign key.
4. Tambahkan index untuk kolom yang sering difilter.
5. Jalankan `php artisan migrate`.

### Langkah 3 - Buat model dan relasi

1. Buat model di `app/Models`.
2. Isi `$fillable`.
3. Isi cast tanggal/boolean/array.
4. Tambahkan relasi `belongsTo`, `hasMany`, atau relasi lain.

### Langkah 4 - Buat service bisnis

1. Buat class di `app/Services`.
2. Taruh transaksi database di service.
3. Gunakan `DB::transaction` untuk operasi multi tabel.
4. Taruh validasi bisnis yang tidak cukup di request validation.
5. Panggil audit log dari service.

### Langkah 5 - Buat controller

1. Buat Web Controller untuk form Blade/Livewire.
2. Buat API Controller jika perlu endpoint JSON.
3. Validasi input.
4. Panggil service.
5. Return redirect atau JSON.

### Langkah 6 - Buat halaman Livewire dan Blade

1. Buat Livewire page untuk query data dan filter.
2. Buat Blade view.
3. Gunakan route name yang jelas.
4. Tambahkan empty state.
5. Tambahkan pesan sukses/error.

### Langkah 7 - Tambahkan route

1. Tambahkan route web di `routes/web.php`.
2. Tambahkan route API di `routes/api.php` jika perlu.
3. Pasang middleware `auth` dan `active`.
4. Pastikan route memiliki name untuk web.

### Langkah 8 - Tambahkan permission

1. Tambahkan permission di seeder.
2. Assign permission ke role yang sesuai.
3. Cek permission di controller atau middleware.

### Langkah 9 - Tambahkan audit log

1. Catat operasi create/update/delete/status change.
2. Simpan data before dan after.
3. Simpan reason untuk edit transaksi posted.

### Langkah 10 - Tambahkan test dan verifikasi

1. Tambahkan test untuk rule penting.
2. Jalankan `php artisan test`.
3. Jalankan `php -l` untuk file PHP yang diubah.
4. Cek route dengan `php artisan route:list`.
5. Cek data melalui halaman web.

## 21. Checklist Fitur Selesai

Sebuah fitur dianggap siap jika:

- Route web/API tersedia.
- Validasi input lengkap.
- Service bisnis tidak menggandakan logika di banyak controller.
- Data tersimpan dengan relasi benar.
- Audit log tercatat.
- Hak akses diterapkan.
- UI punya empty state dan pesan hasil.
- Laporan terkait ikut membaca data baru jika diperlukan.
- Test atau smoke test sudah dilakukan.
- Dokumen fitur diperbarui.

## 22. Perintah Developer yang Sering Dipakai

```bash
php artisan migrate
php artisan db:seed
php artisan test
php artisan route:list
php artisan config:clear
php artisan cache:clear
php artisan serve --host=127.0.0.1 --port=8002
```

Untuk membuat template Excel import siswa:

```bash
php tools/generate_student_import_excel.php
```

## 23. Urutan Implementasi dari Nol

Jika aplikasi harus dibuat ulang dari awal, urutan yang disarankan:

1. Setup Laravel, database, auth, role, dan middleware active user.
2. Buat master referensi: batch, class, major, student type.
3. Buat master siswa dan import siswa.
4. Buat master fee type dan fee scheme.
5. Buat billing cycle dan invoice.
6. Buat generate tagihan.
7. Buat payment dan payment item.
8. Buat cash account, expense, dan cash ledger.
9. Buat dashboard dan laporan.
10. Buat audit log.
11. Buat user management.
12. Tambahkan export PDF/Excel dan integrasi bukti pembayaran.

## 24. Catatan Teknis Penting

- Jangan membuat fitur blokir akademik berdasarkan tunggakan.
- Jangan menambah tipe siswa di luar `regular`, `full_day`, dan `boarding`.
- Jangan membuat SPP berbeda per angkatan.
- Jangan menghapus invoice lama saat tahun ajaran berganti.
- Semua transaksi keuangan yang mengubah kas harus masuk ke `cash_ledger_entries`.
- Semua edit transaksi posted harus memiliki audit trail.
- Siswa nonaktif tidak ditagih baru, tetapi histori tetap tampil.
- Query tunggakan harus membaca invoice `unpaid` dan `partial` dari seluruh periode kecuali user memang memasang filter.
