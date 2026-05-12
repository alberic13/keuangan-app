# Workflow - E-Keuangan MAN 2 Surakarta

**Versi:** 1.0  
**Tanggal:** 15 April 2026  
**Tujuan:** Dokumen alur proses bisnis dan alur sistem untuk implementasi frontend, backend, dan integrasi secara bertahap di VS Code/Codex.

---

## 1. Prinsip Umum Workflow

Dokumen ini melengkapi `prd.md` dengan fokus pada:
- alur operasional user,
- status data dan transisinya,
- keputusan bisnis di setiap proses,
- exception flow,
- kebutuhan integrasi antar modul.

### Aktor utama
- **Admin Keuangan**: setup master data, tarif, generate tagihan, koreksi data tertentu.
- **Bendahara**: pencatatan pembayaran, kas keluar, cetak bukti, monitoring operasional.
- **Kepala Madrasah**: akses dashboard dan laporan read-only.
- **Waka / Pimpinan**: akses dashboard dan laporan read-only.
- **Admin TU**: akses terbatas sesuai policy, terutama informasi administratif read-only.

### Prinsip bisnis global
- SPP bersifat **fixed** dan **tidak boleh dicicil**.
- Uang kegiatan **boleh dicicil**.
- Uang makan hanya untuk siswa **boarding** dan ditagih **per bulan**.
- Transfer bank diproses sebagai **transfer manual** tanpa workflow approval yang kompleks.
- Satu pembayaran boleh mencakup **beberapa invoice**.
- Transaksi posted **boleh diedit oleh role berwenang**, tetapi **alasan edit wajib** dan audit trail harus lengkap.
- Siswa nonaktif tidak menerima tagihan baru, tetapi histori tetap muncul di laporan.
- Laporan harus mengikuti baseline workbook existing: **BKU, Buku Kas Tunai, Buku Pembantu Penerimaan Cash, Buku Pembantu Penerimaan Bank, dan Buku Pembantu Penerimaan Cash+Bank**.

---

## 2. Daftar Status Inti

### 2.1 Status siswa
- `active`
- `inactive`

### 2.2 Status invoice
- `draft`
- `unpaid`
- `partial`
- `paid`
- `void`

### 2.3 Status payment
- `posted`
- `edited`
- `void` *(disiapkan untuk fase lanjutan bila dipakai)*

### 2.4 Status billing cycle
- `open`
- `closed`

### 2.5 Status akun kas/bank
- `active`
- `inactive`

---

## 3. Workflow Master Data Siswa

### 3.1 Tujuan
Membuat data siswa yang konsisten sebagai dasar penagihan, pembayaran, dan laporan historis.

### 3.2 Preconditions
- User sudah login.
- User memiliki hak akses `manage_students`.
- Master angkatan, kelas, dan jurusan sudah tersedia, atau dapat dibuat lebih dulu.

### 3.3 Alur utama - input manual siswa
1. Admin membuka menu **Master Siswa**.
2. Admin klik **Tambah Siswa**.
3. Admin mengisi data wajib:
   - NIS
   - NISN
   - nama
   - kelas
   - jurusan
   - angkatan
   - status boarding/reguler
   - status aktif/nonaktif
4. Sistem memvalidasi kelengkapan dan keunikan NIS/NISN.
5. Jika valid, sistem menyimpan data siswa.
6. Sistem mencatat audit log create.
7. Data siswa tampil di daftar siswa.

### 3.4 Alur edit siswa
1. Admin membuka detail siswa.
2. Admin klik **Edit**.
3. Admin mengubah field yang diizinkan.
4. Sistem memvalidasi perubahan.
5. Jika valid, sistem menyimpan perubahan.
6. Sistem mencatat audit log before/after.

### 3.5 Business rules
- NIS/NISN harus unik.
- Status boarding/reguler memengaruhi tagihan uang makan.
- Siswa inactive tidak diikutkan dalam generate tagihan baru.
- Histori transaksi siswa inactive tetap dipertahankan.

### 3.6 Error / exception
- NIS/NISN duplikat.
- Kelas/jurusan/angkatan tidak ditemukan.
- User tidak punya hak akses.

---

## 4. Workflow Data Upload / Import Siswa

> Ini adalah alur upload data yang harus dipakai sebagai acuan implementasi frontend, backend, dan validasi integrasi.

### 4.1 Tujuan
Mempercepat input awal dan update massal data siswa melalui file Excel.

### 4.2 Preconditions
- User login sebagai Admin Keuangan.
- Template import sudah tersedia dan bisa diunduh.
- Master referensi dasar seperti angkatan, kelas, dan jurusan sudah tersedia atau ada mekanisme mapping.

### 4.3 Format minimum data upload
Kolom minimum yang harus dikenali sistem:
- `nis`
- `nisn`
- `full_name`
- `class`
- `major`
- `batch`
- `student_type` (`regular` / `full_day` / `boarding`)
- `is_active` *(opsional, default true jika kosong)*

### 4.4 Alur utama upload/import
1. Admin membuka menu **Import Siswa**.
2. Admin mengunduh **template import**.
3. Admin mengisi atau menyesuaikan file Excel.
4. Admin mengunggah file ke sistem.
5. Frontend mengirim file ke backend endpoint import.
6. Backend membaca file dan melakukan validasi per baris.
7. Sistem menampilkan **preview hasil validasi** yang berisi:
   - total rows,
   - valid rows,
   - invalid rows,
   - daftar error per baris.
8. Admin meninjau hasil preview.
9. Admin menekan **Konfirmasi Import**.
10. Backend memproses hanya row valid.
11. Sistem menjalankan mode:
   - **insert** jika siswa belum ada,
   - **update** jika siswa sudah ada berdasarkan `nis` atau `nisn`.
12. Sistem menyimpan log import.
13. Sistem menampilkan ringkasan hasil import.
14. Sistem mencatat audit log untuk aktivitas import.

### 4.5 Validasi per baris
- NIS atau NISN wajib ada minimal salah satu, idealnya keduanya.
- Nama wajib terisi.
- Angkatan harus valid.
- Kelas harus valid.
- Jurusan harus valid.
- `student_type` hanya boleh `regular`, `full_day`, atau `boarding`.
- NIS/NISN tidak boleh bentrok dengan siswa lain secara tidak konsisten.

### 4.6 Output import
- `total_rows`
- `success_rows`
- `failed_rows`
- daftar error per row
- `import_log_id`

### 4.7 Business rules
- Import tidak langsung commit semua data tanpa preview.
- Row invalid tidak boleh menghalangi row valid diproses jika user sudah konfirmasi.
- Update data siswa existing tidak boleh menghapus histori tagihan/pembayaran lama.
- Perubahan student type berlaku ke tagihan berikutnya, bukan retroaktif kecuali ada proses koreksi manual.

### 4.8 Error / exception
- File bukan `.xlsx` / `.xls`.
- Header template tidak sesuai.
- Encoding rusak.
- Ada referensi kelas/jurusan/angkatan yang tidak dikenal.
- File terlalu besar.
- Duplikasi NIS/NISN dalam file yang sama.

### 4.9 Catatan implementasi frontend
- Tampilkan tabel preview hasil parsing.
- Tampilkan badge `valid` dan `invalid`.
- Tampilkan error per row, bukan hanya error global.
- Pisahkan tombol **Upload**, **Preview**, dan **Commit Import**.

### 4.10 Catatan implementasi backend
- Parsing file via queue jika dataset besar.
- Simpan file upload sementara sampai proses selesai.
- Gunakan transaction database saat commit batch per chunk.
- Simpan import log dan detail error.

---

## 5. Workflow Setup Tarif Biaya

### 5.1 Tujuan
Menentukan nominal biaya yang akan dipakai saat generate tagihan.

### 5.2 Preconditions
- Fee type sudah tersedia atau akan dibuat bersamaan.
- User memiliki hak akses `manage_fees`.

### 5.3 Alur utama
1. Admin membuka menu **Jenis Biaya & Tarif**.
2. Admin membuat atau memilih fee type:
   - SPP
   - Uang Kegiatan
   - Uang Makan
   - biaya lain bila diperlukan
3. Admin mengisi konfigurasi tarif:
   - nominal
   - batch/angkatan
   - effective start
   - effective end
   - installment allowed
   - applies to
4. Sistem memvalidasi konflik tarif aktif.
5. Sistem menyimpan fee scheme.
6. Sistem mencatat audit log.

### 5.4 Business rules
- SPP: monthly, fixed, installment false.
- Uang kegiatan: installment true.
- Uang makan: monthly, boarding only.
- Tarif dapat berubah antar periode.
- Pada periode tertentu, hanya satu tarif aktif untuk kombinasi fee type + target yang sama.

### 5.5 Error / exception
- Tarif overlap.
- Nominal <= 0.
- effective period tidak valid.

---

## 6. Workflow Generate Tagihan Bulanan

### 6.1 Tujuan
Membuat invoice per siswa berdasarkan tarif aktif dan periode billing.

### 6.2 Preconditions
- Billing cycle terbentuk.
- Fee scheme aktif tersedia.
- Siswa aktif tersedia.

### 6.3 Alur utama generate SPP
1. Admin membuka menu **Generate Tagihan**.
2. Admin memilih periode bulan.
3. Admin memilih fee type = `SPP`.
4. Sistem mengambil siswa aktif.
5. Sistem memakai fee scheme SPP umum yang berlaku untuk semua angkatan.
6. Sistem mengecek apakah invoice untuk kombinasi siswa + fee + periode sudah ada.
7. Jika belum ada, sistem membuat invoice.
8. Status awal invoice adalah `unpaid`.
9. Sistem menampilkan hasil generate: `generated`, `skipped`, `failed`.

### 6.4 Alur utama generate uang makan boarding
1. Admin memilih fee type = `Uang Makan`.
2. Sistem hanya mengambil siswa active dengan `student_type = boarding`.
3. Sistem membuat invoice bulanan sesuai tarif aktif.

### 6.5 Alur utama generate uang kegiatan
1. Admin memilih fee type = `Uang Kegiatan`.
2. Admin memilih referensi kegiatan/program.
3. Sistem membuat invoice dengan outstanding yang bisa dibayar bertahap.

### 6.6 Business rules
- SPP tidak boleh duplikat pada periode sama dan memakai nominal yang sama untuk semua angkatan.
- Uang makan hanya untuk boarding active.
- Uang kegiatan boleh one-time per event/program dan dibayar bertahap.
- Siswa inactive harus diskip.

### 6.7 Error / exception
- Tarif tidak ditemukan.
- Billing cycle closed.
- Generate dijalankan ulang untuk periode yang sama.

---

## 7. Workflow Pembayaran Tagihan

### 7.1 Tujuan
Mencatat pembayaran siswa dan memperbarui invoice serta ledger.

### 7.2 Preconditions
- User memiliki hak akses `manage_payments`.
- Invoice ada dan belum fully paid.
- Akun kas/bank aktif tersedia.

### 7.3 Alur utama pembayaran cash
1. Bendahara membuka menu **Pembayaran**.
2. Bendahara mencari siswa.
3. Sistem menampilkan daftar invoice aktif siswa.
4. Bendahara memilih satu atau beberapa invoice.
5. Bendahara memilih metode bayar `cash`.
6. Bendahara mengisi nominal per invoice.
7. Sistem memvalidasi semua item.
8. Jika valid, sistem membuat record `payments` dan `payment_items`.
9. Sistem memperbarui `paid_amount`, `outstanding_amount`, dan `status` invoice.
10. Sistem membuat ledger cash masuk.
11. Sistem menghasilkan nomor bukti pembayaran.
12. Sistem mencatat audit log.

### 7.4 Alur utama pembayaran transfer manual
1. Bendahara memeriksa bukti transfer atau mutasi bank secara manual.
2. Bendahara membuka menu **Pembayaran**.
3. Bendahara memilih invoice satu atau beberapa item.
4. Bendahara memilih metode `bank_transfer`.
5. Bendahara memilih akun bank tujuan.
6. Bendahara mengisi referensi transfer dan catatan.
7. Sistem menyimpan payment posted.
8. Sistem membuat ledger bank masuk.

### 7.5 Business rules
- SPP harus dibayar penuh per invoice.
- Uang kegiatan boleh dibayar parsial.
- Uang makan dibayar penuh per invoice bulanan.
- Satu payment boleh mencakup banyak invoice.
- Overpayment tidak diizinkan.
- Invoice fully paid tidak dapat dipilih lagi.

### 7.6 Error / exception
- Nominal nol atau negatif.
- SPP dibayar parsial.
- Nominal melebihi outstanding.
- Invoice sudah lunas.
- Akun kas/bank tidak aktif.

---

## 8. Workflow Edit Transaksi Posted

### 8.1 Tujuan
Memungkinkan koreksi transaksi yang sudah tersimpan tanpa kehilangan histori perubahan.

### 8.2 Preconditions
- User adalah role berwenang: Admin Keuangan atau Bendahara.
- Payment berstatus `posted`.
- Jika nanti ada period closing, transaksi hanya dapat diedit di periode `open`.

### 8.3 Alur utama
1. User membuka detail payment.
2. User klik **Edit Transaksi**.
3. Sistem menampilkan form edit.
4. User mengubah data yang diperlukan.
5. User wajib mengisi **alasan edit**.
6. Sistem memvalidasi data baru.
7. Sistem menyimpan perubahan.
8. Sistem menandai transaksi sebagai `edited`.
9. Sistem memperbarui invoice dan ledger terkait bila ada perubahan nominal/alokasi.
10. Sistem mencatat audit log before/after + reason.

### 8.4 Business rules
- Alasan edit wajib.
- Histori perubahan tidak boleh hilang.
- Jika edit memengaruhi distribusi item payment, sistem harus menghitung ulang outstanding invoice terkait.
- Hanya field tertentu yang boleh diubah sesuai policy implementasi.

### 8.5 Error / exception
- User tidak punya akses.
- Data baru melanggar rule SPP / outstanding.
- Invoice target sudah tidak valid.
- Transaksi terkunci oleh period closing.

---

## 9. Workflow Kas Keluar

### 9.1 Tujuan
Mencatat pengeluaran kas/bank secara terstruktur.

### 9.2 Preconditions
- Kategori pengeluaran tersedia.
- Akun kas/bank aktif tersedia.

### 9.3 Alur utama
1. Bendahara membuka menu **Kas Keluar**.
2. Memilih akun kas/bank.
3. Memilih kategori pengeluaran.
4. Mengisi nominal, tanggal, deskripsi, nomor bukti, dan lampiran opsional.
5. Sistem memvalidasi input.
6. Sistem menyimpan `expense`.
7. Sistem membuat `cash_ledger_entry` dengan direction `out`.
8. Sistem mencatat audit log.

### 9.4 Business rules
- Kategori pengeluaran wajib.
- Nominal harus > 0.
- Histori pengeluaran harus bisa ditelusuri di laporan cash/bank.

### 9.5 Error / exception
- Kategori kosong.
- Akun tidak aktif.
- Lampiran tidak valid.

---

## 10. Workflow Dashboard dan Laporan

### 10.1 Tujuan
Menyediakan ringkasan dan laporan operasional/manajerial.

### 10.2 Aktor
- Admin Keuangan
- Bendahara
- Kepala Madrasah
- Waka / Pimpinan

### 10.3 Alur utama dashboard
1. User membuka dashboard.
2. Sistem mengambil KPI berdasarkan role dan filter default.
3. Sistem menampilkan:
   - total tagihan,
   - total pembayaran,
   - total tunggakan,
   - kas masuk periode berjalan,
   - tren penerimaan.

### 10.4 Alur utama laporan
1. User membuka modul laporan.
2. User memilih jenis laporan.
3. User memilih filter tanggal, akun, fee type, batch, kelas, jurusan, atau siswa.
4. Sistem mengeksekusi query laporan.
5. Sistem menampilkan data.
6. User dapat export PDF/Excel.

### 10.5 Jenis laporan minimum
- Kas harian
- Bulanan
- Tahunan
- Per siswa
- Tunggakan
- BKU
- Buku Kas Tunai
- Buku Pembantu Penerimaan Cash
- Buku Pembantu Penerimaan Bank
- Buku Pembantu Penerimaan Cash + Bank

### 10.6 Business rules
- Kepala Madrasah dan Waka bersifat read-only.
- Angka laporan harus konsisten dengan ledger dan transaksi sumber.
- Histori siswa nonaktif tetap muncul jika masuk filter laporan.

---

## 11. Workflow Manajemen User dan Role

### 11.1 Tujuan
Mengatur siapa yang dapat mengakses menu dan aksi tertentu.

### 11.2 Alur utama
1. Admin membuka menu **User & Role**.
2. Admin membuat atau mengubah akun.
3. Admin menetapkan role.
4. Sistem menyimpan perubahan.
5. Sistem mencatat audit log.

### 11.3 Business rules
- Kepala Madrasah dan Waka default read-only.
- Bendahara dan Admin Keuangan memiliki akses operasional.
- Admin TU akses terbatas.

---

## 12. Workflow Audit Trail

### 12.1 Tujuan
Merekam setiap perubahan penting agar siap audit.

### 12.2 Aksi yang wajib dilog
- Login sukses/gagal
- Create/update/deactivate siswa
- Import siswa
- Create/update fee scheme
- Generate tagihan
- Create/edit payment
- Create expense
- Export laporan tertentu bila diperlukan

### 12.3 Data audit minimum
- actor
- waktu
- entitas
- action
- reason *(untuk edit tertentu)*
- before_json
- after_json
- ip_address
- user_agent

---

## 13. Workflow Integrasi Bertahap di VS Code / Codex

### Tahap 1 - Backend foundation
- Authentication & RBAC
- Migrations master data
- CRUD siswa
- Import siswa
- CRUD fee type & fee scheme

### Tahap 2 - Billing & payment core
- Billing cycle
- Generate invoice
- Create payment multi-item
- Ledger posting
- Edit posted transaction

### Tahap 3 - Reporting
- Daily/monthly/yearly reports
- Student ledger report
- BKU baseline reports
- PDF/Excel export

### Tahap 4 - Frontend refinement
- Dashboard
- Form UX enhancement
- Loading/error states
- Print-friendly pages

### Tahap 5 - Hardening
- Audit log detail
- Queue & chunk processing
- Performance optimization
- UAT fixes

---

## 14. Checklist Implementasi per Workflow

### Frontend
- [ ] halaman import dengan preview
- [ ] halaman generate tagihan
- [ ] halaman pembayaran multi-item
- [ ] halaman edit posted transaction + reason
- [ ] halaman laporan baseline BKU

### Backend
- [ ] endpoint import + import log
- [ ] service generate invoice
- [ ] service allocation payment items
- [ ] service edit payment + recalc invoice
- [ ] service ledger posting
- [ ] report query service

### Integrasi
- [ ] validasi response import preview
- [ ] konsistensi invoice status setelah payment/edit
- [ ] konsistensi ledger terhadap payment/expense
- [ ] export PDF/Excel sesuai format laporan
