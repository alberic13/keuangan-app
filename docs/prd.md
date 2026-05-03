# PRD - E-Keuangan MAN 2 Surakarta

**Versi:** Final 1.0  
**Tanggal:** 15 April 2026  
**Jenis produk:** Web application internal  
**Bahasa:** Bahasa Indonesia  
**Tujuan dokumen:** PRD lengkap untuk implementasi bertahap oleh tim frontend, backend, dan integrasi di VS Code/Codex.

---

## Status Dokumen

Dokumen ini merupakan PRD final yang telah diperbarui berdasarkan klarifikasi stakeholder, dengan keputusan berikut:

- Uang makan boarding ditagih **per bulan**.
- Pembayaran transfer bank dicatat sebagai **transfer manual** untuk menjaga sistem tetap sederhana.
- Transaksi **posted dapat diedit** oleh role berwenang dengan **audit trail penuh**.
- Kepala Madrasah dan Waka/Pimpinan bersifat **read-only**.
- Laporan mengikuti **baseline workbook BKU existing**.
- Satu pembayaran **boleh mencakup beberapa invoice**.
- Tarif **dapat berubah antar periode**.
- Siswa nonaktif/alumni **tetap harus muncul pada histori laporan**.

---

## Ringkasan Eksekutif

E-Keuangan MAN 2 Surakarta adalah sistem administrasi keuangan berbasis web untuk mendukung operasional tim keuangan sekolah dalam mengelola master data siswa, tarif biaya, tagihan, pembayaran cash dan transfer manual, kas masuk/keluar, serta laporan audit-ready.

Sistem dirancang mengikuti kebutuhan spesifik sekolah:

- SPP berbeda per angkatan dan tidak boleh dicicil.
- Uang kegiatan dapat dicicil.
- Uang makan hanya berlaku untuk siswa boarding.
- Laporan mengikuti struktur Buku Kas Umum dan buku pembantu existing.
- Sistem hanya dipakai oleh user internal sekolah.

---

# 1. Ringkasan Produk

## 1.1 Nama produk
E-Keuangan MAN 2 Surakarta

## 1.2 Latar belakang
MAN 2 Surakarta membutuhkan sistem administrasi keuangan yang terpusat, mudah dioperasikan, dan siap audit. Proses existing berpotensi tersebar di Excel, rekap manual, dan dokumen cetak, sehingga rawan:

- inkonsistensi data siswa,
- kesalahan hitung tagihan,
- duplikasi pencatatan pembayaran,
- revisi transaksi yang tidak terlacak,
- keterlambatan penyusunan laporan,
- kesulitan menelusuri histori pembayaran per siswa.

Selain itu, sekolah memiliki aturan bisnis spesifik:

- SPP berbeda per angkatan.
- SPP tidak dapat dicicil.
- Uang kegiatan dapat dicicil.
- Uang makan hanya berlaku untuk siswa boarding.
- Sistem harus mendukung laporan kas harian, bulanan, tahunan, dan per siswa.
- Sistem harus mendukung cetak tagihan siswa.
- Sistem harus mendukung import data siswa dari Excel.

## 1.3 Masalah yang diselesaikan
Sistem ini menyelesaikan masalah berikut:

- Data siswa dan komponen biaya belum dikelola dalam satu sumber data yang konsisten.
- Aturan tagihan berbeda per jenis biaya dan sulit dijaga konsistensinya jika hanya mengandalkan spreadsheet.
- Pencatatan pembayaran cash dan transfer manual rawan salah input, salah alokasi, atau tidak terlacak revisinya.
- Laporan operasional dan audit memerlukan waktu lama untuk disiapkan.
- Pimpinan sulit mendapatkan gambaran cepat mengenai penerimaan, tunggakan, dan posisi kas.

## 1.4 Solusi yang ditawarkan
Sistem web internal untuk:

- mengelola master data siswa dan struktur akademik sederhana,
- mengelola jenis biaya dan tarif per angkatan,
- menghasilkan tagihan berdasarkan periode dan aturan bisnis,
- mencatat pembayaran cash dan transfer manual,
- melacak cicilan uang kegiatan,
- mengelola kas masuk dan kas keluar,
- mencetak tagihan dan kwitansi,
- menghasilkan laporan operasional dan laporan audit berdasarkan baseline format BKU sekolah,
- menyediakan dashboard ringkas untuk pimpinan.

## 1.5 Nilai utama produk

- Satu sumber data keuangan yang konsisten.
- Lebih cepat dan akurat dalam pencatatan pembayaran.
- Mempermudah monitoring tunggakan dan histori per siswa.
- Laporan lebih cepat, rapi, dan siap audit.
- Jejak perubahan data dan transaksi terdokumentasi.

---

# 2. Tujuan Produk

## 2.1 Tujuan bisnis

- Menstandarkan administrasi keuangan MAN 2 Surakarta dalam satu sistem.
- Meningkatkan akurasi pencatatan tagihan, pembayaran, dan kas.
- Menyediakan laporan yang lebih cepat dan sesuai kebutuhan audit.
- Mengurangi ketergantungan pada spreadsheet manual.
- Menyediakan data manajerial bagi Kepala Madrasah dan Waka/Pimpinan.

## 2.2 Tujuan pengguna

- Bendahara dapat mencatat pembayaran dengan cepat dan minim kesalahan.
- Admin keuangan dapat mengelola tarif, tagihan, dan data siswa secara efisien.
- Kepala Madrasah dan Waka dapat melihat dashboard serta laporan tanpa mengubah data.
- Admin TU dapat mengakses informasi tertentu yang diperlukan sesuai role.

## 2.3 Keberhasilan yang ingin dicapai

- Seluruh tagihan bulanan tercatat di sistem.
- Seluruh pembayaran siswa tercatat dan dapat ditelusuri.
- Laporan periodik dapat dibuat kapan saja tanpa rekap ulang manual.
- Perubahan transaksi penting dapat diaudit melalui histori before/after.
- Tim keuangan mampu menggunakan sistem sebagai aplikasi utama operasional.

---

# 3. Problem Statement

Pain point utama pengguna adalah ketidakseragaman proses dan data. Sekolah menangani beberapa jenis biaya dengan aturan berbeda, sehingga pendekatan generik tanpa aturan khusus berisiko menimbulkan data yang tidak akurat. Pengelolaan dengan spreadsheet juga menyulitkan pencarian histori per siswa, pemisahan cash dan bank, rekap tunggakan, dan audit terhadap perubahan transaksi.

Solusi ini penting dibangun karena sistem akan menjadi fondasi administrasi keuangan yang:

- konsisten terhadap rule sekolah,
- efisien untuk operasional harian,
- dapat menghasilkan laporan sesuai kebutuhan pimpinan dan audit,
- tetap cukup sederhana agar dapat diadopsi oleh tim internal.

---

# 4. Target Pengguna / User Persona

## 4.1 Persona 1 - Bendahara
**Karakteristik:** pengguna operasional utama, aktif harian.  
**Kebutuhan utama:** input pembayaran, cek tagihan, cetak bukti, lihat ledger.  
**Pain points:** rawan salah input, sulit cek cicilan dan outstanding, rekap manual lama.  
**Perilaku penggunaan:** login harian, banyak melakukan pencarian siswa dan transaksi.

## 4.2 Persona 2 - Admin Keuangan
**Karakteristik:** administrator domain keuangan.  
**Kebutuhan utama:** kelola siswa, tarif, tagihan, import Excel, revisi data transaksi tertentu.  
**Pain points:** data tersebar, perubahan sulit ditelusuri, setup periodik memakan waktu.  
**Perilaku penggunaan:** rutin untuk setup, monitoring, dan maintenance data.

## 4.3 Persona 3 - Kepala Madrasah
**Karakteristik:** stakeholder manajerial, bukan operator harian.  
**Kebutuhan utama:** dashboard, laporan penerimaan, tunggakan, dan ringkasan kas.  
**Pain points:** harus menunggu rekap manual.  
**Perilaku penggunaan:** periodik, sifatnya read-only.

## 4.4 Persona 4 - Waka / Pimpinan
**Karakteristik:** user manajerial pendukung pengambilan keputusan.  
**Kebutuhan utama:** monitoring penerimaan, laporan periodik, akses informasi ringkas.  
**Pain points:** kurangnya visibilitas cepat terhadap kondisi keuangan.  
**Perilaku penggunaan:** periodik, read-only.

## 4.5 Persona 5 - Admin TU
**Karakteristik:** user administratif non-keuangan inti.  
**Kebutuhan utama:** akses terbatas ke informasi yang diperlukan secara administratif.  
**Pain points:** tidak ada akses terstruktur terhadap data yang dibutuhkan.  
**Perilaku penggunaan:** insidental, read-only atau very limited action sesuai policy.

---

# 5. User Journey

## 5.1 Alur utama administrasi keuangan

1. Admin keuangan login ke sistem.
2. Admin menyiapkan atau memperbarui data siswa melalui input manual atau import Excel.
3. Admin mengatur tarif SPP per angkatan, tarif uang kegiatan, dan tarif uang makan boarding.
4. Admin membangkitkan tagihan untuk periode tertentu.
5. Bendahara menerima pembayaran cash atau memeriksa transfer secara manual.
6. Bendahara mencari siswa dan melihat tagihan aktifnya.
7. Bendahara mencatat pembayaran:
   - SPP harus dibayar penuh.
   - Uang kegiatan dapat dibayar sebagian.
   - Uang makan boarding dibayar per bulan.
8. Sistem memperbarui status tagihan, membuat nomor bukti, dan mencatat ledger kas/bank.
9. Jika ada koreksi, role berwenang dapat mengedit transaksi posted dengan alasan wajib dan audit trail penuh.
10. Kepala Madrasah atau Waka melihat dashboard dan laporan read-only.
11. Tim keuangan mengekspor laporan saat dibutuhkan untuk audit atau pelaporan rutin.

---

# 6. Scope Produk

## 6.1 In Scope

- Login, logout, dan session management
- Role-based access control
- Master user dan role
- Master siswa
- Import data siswa Excel
- Master angkatan, kelas, jurusan
- Master jenis biaya dan tarif
- Master akun kas/bank
- Generate tagihan per periode
- Tagihan SPP bulanan
- Tagihan uang kegiatan dengan cicilan
- Tagihan uang makan boarding bulanan
- Pencatatan pembayaran cash
- Pencatatan pembayaran transfer bank manual
- Multi-item payment dalam satu transaksi
- Edit transaksi posted oleh role berwenang dengan alasan dan audit trail
- Kas masuk dan kas keluar
- Ledger / BKU
- Dashboard
- Laporan harian, bulanan, tahunan, per siswa, tunggakan, dan laporan baseline BKU
- Cetak tagihan dan kwitansi
- Export PDF/Excel
- Audit log

## 6.2 Out of Scope

- Portal siswa / orang tua
- Pembayaran online otomatis via payment gateway
- Rekonsiliasi bank otomatis
- Mobile app native
- Multi-sekolah / multi-tenant
- Integrasi notifikasi WhatsApp atau SMS pada fase ini
- Akuntansi ERP penuh dengan jurnal berlapis

---

# 7. Functional Requirements

## 7.1 Fitur: Login dan Manajemen Akses
**Tujuan fitur:** memastikan hanya user berwenang yang dapat mengakses sistem.  
**Deskripsi:** autentikasi berbasis akun internal dengan role dan permission.  
**User flow:** login -> validasi -> redirect dashboard sesuai role.  
**Input:** username/email, password.  
**Output:** sesi aktif dan akses menu sesuai role.  
**Business rules:** hanya user aktif dapat login; Kepala Madrasah dan Waka read-only.  
**Error handling:** kredensial salah, akun nonaktif, session expired.  
**Prioritas:** P0.

## 7.2 Fitur: Master Data Siswa
**Tujuan fitur:** menyediakan data induk siswa sebagai basis tagihan.  
**Deskripsi:** CRUD siswa dan status aktif/nonaktif.  
**User flow:** buka modul -> tambah/edit/nonaktif -> simpan.  
**Input:** NIS, NISN, nama, kelas, jurusan, angkatan, status boarding/reguler, status aktif.  
**Output:** data siswa tersimpan dan dapat ditelusuri histori perubahannya.  
**Business rules:** NIS/NISN unik; siswa nonaktif tidak menerima tagihan baru; siswa nonaktif tetap muncul di laporan historis.  
**Error handling:** duplikasi identitas, field wajib kosong, enum tidak valid.  
**Prioritas:** P0.

## 7.3 Fitur: Import Data Siswa via Excel
**Tujuan fitur:** mempercepat input dan pembaruan massal.  
**Deskripsi:** upload file dengan template tertentu, validasi, preview, lalu commit.  
**User flow:** download template -> upload -> review validasi -> confirm import.  
**Input:** file xlsx/csv.  
**Output:** jumlah data berhasil dan gagal, log import.  
**Business rules:** insert/update berdasarkan NIS/NISN; hanya data valid yang diproses.  
**Error handling:** file invalid, kolom wajib hilang, data duplikat, nilai enum salah.  
**Prioritas:** P0.

## 7.4 Fitur: Master Jenis Biaya dan Tarif
**Tujuan fitur:** mengelola struktur biaya dan nominal yang berlaku.  
**Deskripsi:** pengaturan fee type, fee scheme, periode berlaku, dan rule pembayaran.  
**User flow:** tambah jenis biaya -> atur tarif -> aktifkan untuk periode tertentu.  
**Input:** nama biaya, kategori, nominal, angkatan, effective period, aturan cicilan, berlaku untuk tipe siswa.  
**Output:** tarif aktif yang dapat dipakai generate tagihan.  
**Business rules:** SPP bulanan dan tidak bisa dicicil; uang kegiatan bisa dicicil; uang makan hanya boarding dan ditagih per bulan; tarif dapat berubah antar periode.  
**Error handling:** tarif overlap, nominal invalid, rule tidak lengkap.  
**Prioritas:** P0.

## 7.5 Fitur: Generate Tagihan
**Tujuan fitur:** membentuk tagihan per siswa sesuai aturan dan periode.  
**Deskripsi:** generate massal atau individual dengan review hasil.  
**User flow:** pilih periode -> pilih jenis biaya/filter -> generate -> review -> publish.  
**Input:** periode, fee type, filter batch/kelas/jurusan/student type.  
**Output:** invoice aktif.  
**Business rules:** SPP dibangkitkan bulanan; uang makan hanya untuk boarding aktif; uang kegiatan dapat dibuat berdasarkan program atau kebijakan tertentu dan outstanding-nya dapat dicicil; kombinasi siswa + fee + periode + referensi tidak boleh duplikat.  
**Error handling:** tarif belum ada, tagihan duplikat, data siswa tidak memenuhi syarat.  
**Prioritas:** P0.

## 7.6 Fitur: Pembayaran Tagihan
**Tujuan fitur:** mencatat pelunasan atau cicilan tagihan siswa.  
**Deskripsi:** pencatatan pembayaran cash dan transfer manual, termasuk multi-item payment.  
**User flow:** cari siswa -> pilih satu atau beberapa tagihan -> input metode dan nominal -> simpan -> cetak bukti.  
**Input:** siswa, invoice list, nominal per invoice, metode, tanggal, catatan, referensi transfer opsional.  
**Output:** payment record, payment item, nomor bukti, update invoice status.  
**Business rules:** SPP tidak boleh dibayar parsial; uang kegiatan dapat parsial; uang makan dibayar penuh per tagihan bulanan; satu pembayaran boleh mencakup beberapa invoice; overpayment tidak diizinkan pada versi awal kecuali kebijakan khusus diaktifkan kemudian.  
**Error handling:** nominal nol/negatif, overpayment, invoice sudah lunas, SPP parsial.  
**Prioritas:** P0.

## 7.7 Fitur: Transfer Bank Manual
**Tujuan fitur:** menyederhanakan pencatatan pembayaran bank tanpa workflow approval kompleks.  
**Deskripsi:** bendahara/admin memeriksa mutasi atau bukti transfer lalu mencatatnya langsung ke sistem sebagai transaksi sah.  
**User flow:** cek referensi transfer -> input pembayaran -> simpan -> ledger bank bertambah.  
**Input:** tanggal bayar, nominal, bank/cash account, referensi transfer, catatan, lampiran opsional.  
**Output:** transaksi pembayaran bank manual.  
**Business rules:** tidak ada approval workflow terpisah; validitas transaksi ditentukan oleh petugas yang mencatat; bukti/ref transfer sangat disarankan.  
**Error handling:** referensi kosong jika diwajibkan, nominal invalid, invoice mismatch.  
**Prioritas:** P1.

## 7.8 Fitur: Edit Transaksi Posted
**Tujuan fitur:** memungkinkan koreksi transaksi tanpa mengorbankan auditability.  
**Deskripsi:** transaksi posted dapat diedit oleh role tertentu dalam kondisi tertentu.  
**User flow:** buka detail transaksi -> klik edit -> isi alasan -> ubah data -> simpan.  
**Input:** field transaksi yang diubah, alasan revisi.  
**Output:** transaksi terbarui, log before/after, penanda edited.  
**Business rules:** hanya Admin Keuangan dan Bendahara yang dapat edit posted; alasan revisi wajib; sebelum/sesudah perubahan harus tersimpan; histori edit tidak boleh dihapus; rekomendasi implementasi menambahkan batas edit berdasar periode terbuka jika fase lanjutan dibutuhkan.  
**Error handling:** user tidak berwenang, field perubahan tidak valid, transaksi terkunci.  
**Prioritas:** P0.

## 7.9 Fitur: Kas Masuk/Keluar dan Ledger
**Tujuan fitur:** mengelola arus kas sekolah.  
**Deskripsi:** sistem mencatat kas masuk dari pembayaran dan kas keluar manual berdasarkan kategori pengeluaran.  
**User flow:** input kas keluar atau posting pembayaran -> ledger ter-update -> saldo terbentuk.  
**Input:** tanggal, akun kas/bank, kategori, nominal, deskripsi, nomor bukti, lampiran opsional.  
**Output:** entri ledger dan saldo per akun.  
**Business rules:** pembayaran tagihan otomatis membentuk kas masuk; kas keluar wajib berkategori; pengeluaran mengikuti baseline laporan existing yang memisahkan cash dan bank.  
**Error handling:** nominal invalid, akun tidak aktif, kategori kosong.  
**Prioritas:** P0.

## 7.10 Fitur: Cetak Tagihan dan Kwitansi
**Tujuan fitur:** menyediakan dokumen operasional siap cetak.  
**Deskripsi:** generate PDF tagihan dan kwitansi dari data sistem.  
**User flow:** pilih siswa/transaksi -> klik cetak -> preview -> download/print.  
**Input:** invoice atau payment reference.  
**Output:** PDF dokumen.  
**Business rules:** nomor dokumen unik; format memuat identitas siswa, rincian biaya, tanggal, dan petugas; karena belum ada format resmi, template awal mengikuti gaya dokumen administrasi sekolah yang formal dan mudah disempurnakan kemudian.  
**Error handling:** data belum lengkap, transaksi belum valid.  
**Prioritas:** P0.

## 7.11 Fitur: Dashboard dan Laporan
**Tujuan fitur:** menyediakan insight operasional dan manajerial.  
**Deskripsi:** dashboard KPI dan laporan filterable, termasuk baseline laporan BKU.  
**User flow:** buka dashboard/laporan -> set filter -> lihat hasil -> export.  
**Input:** tanggal, fee type, batch, kelas, jurusan, metode pembayaran, akun kas/bank.  
**Output:** dashboard, tabel laporan, PDF, Excel.  
**Business rules:** pimpinan read-only; laporan harus konsisten dengan transaksi sumber; baseline laporan minimal mencakup BKU, Buku Kas Tunai, Buku Pembantu Penerimaan Cash, Buku Pembantu Penerimaan Bank, dan Buku Pembantu Penerimaan Cash+Bank.  
**Error handling:** filter invalid, no data.  
**Prioritas:** P0.

## 7.12 Fitur: Audit Log
**Tujuan fitur:** memastikan semua perubahan penting dapat diaudit.  
**Deskripsi:** pencatatan actor, waktu, aksi, before, after, dan alasan untuk edit tertentu.  
**User flow:** user melakukan aksi -> sistem menulis audit log -> admin melihat riwayat bila diperlukan.  
**Input:** aksi sistem/user.  
**Output:** audit records.  
**Business rules:** wajib untuk siswa, tarif, tagihan, pembayaran, kas keluar, user, dan edit transaksi posted; audit log tidak dapat dimodifikasi oleh user biasa.  
**Error handling:** jika audit log gagal ditulis maka sistem mencatat system log error.  
**Prioritas:** P0.

## 7.13 Fitur: Manajemen User dan Role
**Tujuan fitur:** mengelola akun internal.  
**Deskripsi:** create/edit/disable akun dan assign role.  
**User flow:** buka user management -> tambah/edit/nonaktifkan -> simpan.  
**Input:** nama, username/email, password awal, role, status.  
**Output:** akun aktif.  
**Business rules:** Kepala Madrasah dan Waka default read-only; password di-hash; akun nonaktif tidak dapat login.  
**Error handling:** username/email duplikat, role tidak valid.  
**Prioritas:** P0.

---

# 8. Detailed Feature Breakdown

## 8.1 Halaman Login
**Tujuan:** autentikasi user.  
**Komponen utama:** form login, pesan error, info identitas sistem.  
**Interaksi pengguna:** input username/password, submit.  
**State:** idle, loading, gagal, sukses.  
**Validasi:** field wajib, panjang password minimum.  
**Loading/empty/error:** spinner submit, alert error kredensial.

## 8.2 Dashboard
**Tujuan:** memberi gambaran ringkas kondisi keuangan.  
**Komponen utama:** KPI card, grafik tren penerimaan, ringkasan tunggakan, transaksi terbaru.  
**Interaksi pengguna:** filter periode, drill-down ke laporan.  
**State:** loading, data siap, no data, error.  
**Validasi:** rentang tanggal valid.  
**Loading/empty/error:** skeleton card, placeholder grafik, empty message.

## 8.3 Modul Master Siswa
**Tujuan:** kelola data siswa.  
**Komponen utama:** tabel, filter, form input/edit, import Excel, histori.  
**Interaksi pengguna:** cari, filter, tambah, edit, nonaktifkan, import.  
**State:** active, inactive, import-preview.  
**Validasi:** NIS/NISN unik, enum valid.  
**Loading/empty/error:** tabel loading, empty state daftar siswa, detail error import per baris.

## 8.4 Modul Tarif
**Tujuan:** kelola jenis biaya dan nominal.  
**Komponen utama:** daftar jenis biaya, konfigurasi tarif, status aktif.  
**Interaksi pengguna:** tambah tarif, ubah, aktif/nonaktif.  
**State:** active, expired, draft.  
**Validasi:** nominal > 0, periode tidak overlap.  
**Loading/empty/error:** empty state saat belum ada tarif, konflik konfigurasi.

## 8.5 Modul Tagihan
**Tujuan:** monitor dan generate invoice.  
**Komponen utama:** daftar invoice, filter, action generate, detail invoice.  
**Interaksi pengguna:** generate batch, publish, lihat detail, cetak.  
**State:** draft, active, unpaid, partial, paid.  
**Validasi:** anti-duplicate, fee scheme wajib tersedia.  
**Loading/empty/error:** progress generate, empty state tagihan.

## 8.6 Modul Pembayaran
**Tujuan:** mencatat pembayaran.  
**Komponen utama:** search siswa, daftar invoice aktif, form payment, daftar transaksi, cetak kwitansi.  
**Interaksi pengguna:** pilih beberapa invoice, isi nominal, pilih metode, simpan, edit posted jika berwenang.  
**State:** posted, edited, canceled/voided jika kelak ditambahkan.  
**Validasi:** rules SPP full payment only, installment only untuk fee tertentu, total tidak melebihi outstanding.  
**Loading/empty/error:** loading pencarian, empty jika tidak ada tagihan, pesan rule violation spesifik.

## 8.7 Modul Kas dan Ledger
**Tujuan:** catat arus kas.  
**Komponen utama:** daftar ledger, kas keluar, saldo akun, filter akun.  
**Interaksi pengguna:** input kas keluar, lihat ledger cash/bank, export.  
**State:** posted, edited.  
**Validasi:** kategori wajib, nominal > 0, akun aktif.  
**Loading/empty/error:** empty ledger, filter no result, error posting.

## 8.8 Modul Laporan
**Tujuan:** menghasilkan laporan operasional dan audit.  
**Komponen utama:** filter panel, tabel hasil, tombol export, print view.  
**Interaksi pengguna:** pilih jenis laporan dan filter, lalu export.  
**State:** loading, ready, empty, export processing.  
**Validasi:** parameter filter valid.  
**Loading/empty/error:** state tanpa data, pesan export gagal.

## 8.9 Modul User dan Role
**Tujuan:** kelola akun.  
**Komponen utama:** list user, form user, permission matrix ringkas.  
**Interaksi pengguna:** tambah/edit/nonaktifkan/reset password.  
**State:** active, inactive, locked.  
**Validasi:** email/username unik, role wajib.  
**Loading/empty/error:** tabel kosong, unauthorized.

## 8.10 Modul Audit Log
**Tujuan:** telusur perubahan.  
**Komponen utama:** daftar log, filter, detail before/after.  
**Interaksi pengguna:** cari, filter, buka detail.  
**State:** loading, no result.  
**Validasi:** hanya role tertentu boleh lihat.  
**Loading/empty/error:** no log, unauthorized.

---

# 9. Non-Functional Requirements

## 9.1 Performance

- Halaman daftar utama target load < 3 detik pada dataset normal.
- Pencarian siswa target < 1 detik untuk 5.000 sampai 20.000 data dengan indexing memadai.
- Generate tagihan massal target maksimal 5 menit untuk 5.000 siswa melalui queue job.
- Export laporan besar berjalan asynchronous jika jumlah data tinggi.

## 9.2 Security

- Password di-hash dengan mekanisme aman bawaan Laravel.
- Semua endpoint sensitif dilindungi authorization policy.
- Session management aman dengan CSRF protection.
- Audit log wajib untuk aksi kritikal.

## 9.3 Scalability

- Sistem dioptimalkan untuk satu institusi namun modular.
- Queue dan cache dapat diaktifkan untuk pekerjaan batch.
- Penyimpanan lampiran dipisah dari database utama.

## 9.4 Reliability

- Backup database harian.
- Application log dan job failure log aktif.
- Data transaksi harus tetap konsisten walau terjadi error parsial saat proses simpan.

## 9.5 Maintainability

- Struktur kode modular per domain.
- Gunakan service layer, policy, request validation, dan testing.
- Naming convention konsisten.

## 9.6 Accessibility

- Desktop-first dan tetap usable di tablet.
- Label form jelas, warna status konsisten, kontras cukup.

## 9.7 Logging and Monitoring

- Auth log
- Application log
- Audit log bisnis
- Monitoring job import/generate/export

## 9.8 Compliance

- Siap audit internal sekolah/komite.
- Riwayat transaksi dan histori edit wajib tersimpan.
- Retensi data mengikuti kebijakan sekolah, minimal untuk beberapa tahun historis.

---

# 10. System / Technical Requirements

## 10.1 Arsitektur sistem
Rekomendasi arsitektur adalah **Laravel modular monolith**. Pilihan ini paling sesuai untuk aplikasi internal sekolah karena lebih cepat dibangun, lebih mudah dideploy, dan lebih sederhana di-maintain dibanding microservices.

## 10.2 Frontend stack

- Laravel Blade
- Livewire
- Tailwind CSS
- Alpine.js
- Chart library ringan untuk dashboard

## 10.3 Backend stack

- Laravel 11
- PHP 8.3
- Laravel Queue
- Laravel Scheduler
- Spatie Laravel Permission
- Laravel Excel

## 10.4 Database
Rekomendasi database adalah **MySQL 8** karena umum, mudah dikelola, dan sesuai untuk lingkungan sekolah/kampus. PostgreSQL adalah opsi alternatif jika tim ingin kemampuan query dan strictness yang lebih tinggi.

## 10.5 Authentication

- Session-based authentication
- Role and permission berbasis RBAC
- Password reset opsional pada fase berikutnya

## 10.6 API design style

- Web routes untuk halaman utama
- JSON endpoints internal untuk operasi asynchronous atau komponen interaktif
- RESTful naming untuk resource utama

## 10.7 Deployment approach

- Ubuntu VPS atau server cloud
- Nginx
- PHP-FPM
- MySQL
- Supervisor untuk queue worker
- SSL aktif

## 10.8 Environment

- local/dev
- staging
- production

**Catatan:** staging wajib untuk UAT dan validasi laporan sebelum production.

---

# 11. Data Model

## 11.1 Entitas utama dan field penting

### 11.1.1 users
- id
- name
- username
- email
- password_hash
- status
- last_login_at

### 11.1.2 roles
- id
- name
- description

### 11.1.3 students
- id
- nis
- nisn
- full_name
- class_id
- major_id
- batch_id
- student_type (`regular`, `boarding`)
- is_active
- enrollment_date
- exit_date

### 11.1.4 classes
- id
- name
- level

### 11.1.5 majors
- id
- name
- code

### 11.1.6 batches
- id
- year_label
- academic_year

### 11.1.7 fee_types
- id
- code
- name
- category (`spp`, `activity`, `meal`, `other`)
- installment_allowed
- billing_frequency
- applies_to
- is_active

### 11.1.8 fee_schemes
- id
- fee_type_id
- batch_id
- nominal
- effective_start
- effective_end
- is_active

### 11.1.9 billing_cycles
- id
- month
- year
- due_date
- status

### 11.1.10 invoices
- id
- invoice_no
- student_id
- fee_type_id
- billing_cycle_id
- total_amount
- paid_amount
- outstanding_amount
- status (`unpaid`, `partial`, `paid`, `edited`)
- published_at

### 11.1.11 payments
- id
- payment_no
- student_id
- payment_date
- method (`cash`, `bank_transfer`)
- total_amount
- bank_reference
- notes
- created_by
- edited_by
- edited_reason

### 11.1.12 payment_items
- id
- payment_id
- invoice_id
- amount

### 11.1.13 cash_accounts
- id
- name
- type (`cash`, `bank`)
- account_number
- is_active

### 11.1.14 cash_ledger_entries
- id
- entry_no
- transaction_date
- account_id
- direction (`in`, `out`)
- source_type (`payment`, `expense`, `adjustment`)
- source_id
- amount
- description
- status

### 11.1.15 expense_categories
- id
- name
- code
- is_active

### 11.1.16 expenses
- id
- expense_no
- transaction_date
- category_id
- amount
- payment_account_id
- description
- attachment_path
- created_by

### 11.1.17 import_logs
- id
- type
- file_name
- imported_by
- total_rows
- success_rows
- failed_rows
- metadata_json

### 11.1.18 audit_logs
- id
- actor_id
- entity_type
- entity_id
- action
- reason
- before_json
- after_json
- ip_address
- user_agent
- created_at

## 11.2 Relasi antar entitas

- student belongs to batch, class, major
- fee_scheme belongs to fee_type dan batch
- invoice belongs to student, fee_type, billing_cycle
- payment belongs to student
- payment has many payment_items
- payment_item belongs to invoice
- cash_ledger_entry berasal dari payment atau expense
- audit_log bersifat polymorphic terhadap entity_type dan entity_id

## 11.3 Contoh schema sederhana

```json
{
  "student": {
    "nisn": "1234567890",
    "full_name": "Ahmad Fulan",
    "batch": "2024",
    "class": "XI IPA 1",
    "major": "IPA",
    "student_type": "boarding",
    "is_active": true
  }
}
```

```json
{
  "invoice": {
    "invoice_no": "INV-2026-04-000123",
    "student_id": 10,
    "fee_type": "SPP",
    "period": "April 2026",
    "total_amount": 350000,
    "paid_amount": 0,
    "outstanding_amount": 350000,
    "status": "unpaid"
  }
}
```

---

# 12. API Requirements

## 12.1 Auth

### 12.1.1 POST `/api/auth/login`
**Tujuan:** login user.  
**Request body:**

```json
{ "login": "bendahara", "password": "secret" }
```

**Response:**

```json
{ "message": "Login berhasil", "user": { "name": "Bendahara" } }
```

**Auth requirement:** No.  
**Error response:** `401 invalid credentials`, `403 inactive account`.

### 12.1.2 POST `/api/auth/logout`
**Tujuan:** logout user.  
**Auth requirement:** Yes.

## 12.2 Students

### 12.2.1 GET `/api/students`
List siswa dengan filter.

### 12.2.2 POST `/api/students`
Tambah siswa.

### 12.2.3 GET `/api/students/{id}`
Detail siswa.

### 12.2.4 PUT `/api/students/{id}`
Edit siswa.

### 12.2.5 PATCH `/api/students/{id}/deactivate`
Nonaktifkan siswa.

## 12.3 Imports

### 12.3.1 POST `/api/imports/students`
Upload dan proses import siswa.

## 12.4 Fee Types dan Fee Schemes

### 12.4.1 GET `/api/fee-types`
### 12.4.2 POST `/api/fee-types`
### 12.4.3 GET `/api/fee-schemes`
### 12.4.4 POST `/api/fee-schemes`

## 12.5 Billing

### 12.5.1 POST `/api/billing/generate`
Generate tagihan massal.

### 12.5.2 GET `/api/invoices`
List invoice.

### 12.5.3 GET `/api/invoices/{id}`
Detail invoice.

## 12.6 Payments

### 12.6.1 POST `/api/payments`
Create payment multi-item.

### 12.6.2 PUT `/api/payments/{id}`
Edit payment posted dengan reason.

### 12.6.3 GET `/api/payments/{id}`
Detail payment.

## 12.7 Cash and Expenses

### 12.7.1 GET `/api/cash-ledger`
Ledger cash/bank.

### 12.7.2 POST `/api/expenses`
Tambah kas keluar.

### 12.7.3 GET `/api/expense-categories`
List kategori kas keluar.

## 12.8 Reports

### 12.8.1 GET `/api/reports/daily-cash`
### 12.8.2 GET `/api/reports/monthly-summary`
### 12.8.3 GET `/api/reports/yearly-summary`
### 12.8.4 GET `/api/reports/student-ledger/{studentId}`
### 12.8.5 GET `/api/reports/arrears`
### 12.8.6 GET `/api/reports/bku`
### 12.8.7 GET `/api/reports/cash-book`
### 12.8.8 GET `/api/reports/cash-receipt-book`
### 12.8.9 GET `/api/reports/bank-receipt-book`
### 12.8.10 GET `/api/reports/cash-bank-receipt-book`

## 12.9 Standar error response

```json
{
  "message": "Validation failed",
  "errors": {
    "field": ["error message"]
  }
}
```

---

# 13. UX / UI Requirements

## 13.1 Struktur halaman

- Login
- Dashboard
- Master Data
  - Siswa
  - Kelas
  - Jurusan
  - Angkatan
  - Jenis Biaya
  - Tarif
  - Akun Kas/Bank
  - Kategori Pengeluaran
  - User dan Role
- Transaksi
  - Generate Tagihan
  - Daftar Tagihan
  - Pembayaran
  - Kas Masuk/Keluar
- Laporan
  - Harian
  - Bulanan
  - Tahunan
  - Per Siswa
  - Tunggakan
  - BKU Baseline Reports
- Audit Log
- Pengaturan

## 13.2 Komponen penting

- global student search
- filter panel
- status badge
- summary card
- data table dengan pagination
- modal konfirmasi aksi penting
- alasan edit wajib
- export dan print button

## 13.3 Prinsip UX

- Form transaksi harus cepat dan minim klik.
- Rule bisnis ditampilkan secara jelas di UI.
- Error message harus spesifik dan membantu user memperbaiki input.
- Dashboard untuk pimpinan diprioritaskan ringkas dan jelas.

## 13.4 Responsiveness
Desktop-first, tetap nyaman di tablet, mobile hanya minimum readability.

## 13.5 Empty state

- Belum ada data siswa
- Belum ada tagihan untuk filter ini
- Belum ada transaksi pada periode ini
- Belum ada data laporan

## 13.6 Error state

- kredensial salah
- file import invalid
- invoice tidak memenuhi rule pembayaran
- user tidak berwenang mengedit posted transaction

## 13.7 Loading state

- skeleton dashboard
- progress generate/import
- loading table search

---

# 14. Security Requirements

## 14.1 Authentication

- Session-based login
- Password minimum 8 karakter
- Password hash aman
- Session timeout otomatis

## 14.2 Authorization

- RBAC per role dan permission
- Kepala Madrasah dan Waka default read-only
- Edit posted transaction hanya untuk role berwenang

## 14.3 Data protection

- HTTPS wajib
- Backup terjadwal
- Lampiran file dibatasi aksesnya

## 14.4 Input validation

- Validasi server-side wajib
- Sanitasi input
- Validasi file upload

## 14.5 Rate limiting

- Login rate limiting
- Proteksi endpoint sensitif bila diperlukan

## 14.6 Audit logging

- login sukses/gagal
- tambah/edit/nonaktif master data
- generate tagihan
- create/edit pembayaran
- create/edit kas keluar
- export laporan tertentu jika perlu

## 14.7 Session management

- Regenerate session setelah login
- Revoke session saat akun dinonaktifkan atau password di-reset
- CSRF protection aktif

---

# 15. Edge Cases

- Siswa berubah dari reguler ke boarding di tengah periode
- Siswa nonaktif di tengah bulan namun histori tetap harus ada
- Tarif SPP berubah untuk periode berikutnya
- Generate tagihan dijalankan dua kali untuk periode sama
- Payment dibuat untuk invoice yang sudah lunas
- SPP dicoba dibayar parsial
- Satu transaksi mencakup beberapa invoice dengan kombinasi status berbeda
- Referensi transfer kosong atau sama dengan transaksi lain
- Payment posted diedit berkali-kali oleh petugas berbeda
- Import mengandung NIS/NISN duplikat atau kosong
- Laporan diminta untuk rentang tanggal sangat besar
- Akun kas/bank dinonaktifkan padahal punya histori transaksi
- Kategori pengeluaran dihapus padahal sudah dipakai
- Dua user mengedit transaksi yang sama hampir bersamaan

---

# 16. Dependencies

## 16.1 Teknologi

- Laravel 11
- PHP 8.3
- MySQL 8
- Tailwind CSS
- Livewire
- Laravel Excel
- PDF generator
- Spatie Permission

## 16.2 Tim

- Product owner / BA
- Backend engineer
- Fullstack engineer Laravel
- QA
- Perwakilan UAT dari tim keuangan

## 16.3 Vendor / pihak ketiga

- Hosting / VPS provider
- Email service opsional

## 16.4 Data / API eksternal

- File Excel siswa
- Rekap transfer manual dari bank
- Format laporan existing BKU dari sekolah

---

# 17. Risks and Mitigations

| Risiko | Dampak | Kemungkinan | Mitigasi |
|---|---|---:|---|
| Aturan bisnis berubah saat development | Tinggi | Sedang | gunakan konfigurasi tarif dan rule modular |
| Kualitas data siswa buruk | Tinggi | Tinggi | template import, preview validation, log error |
| Edit posted transaction disalahgunakan | Tinggi | Sedang | reason wajib, role restriction, before/after audit |
| Generate tagihan lambat | Sedang | Sedang | queue job, indexing, batching |
| Laporan tidak sesuai kebutuhan audit | Tinggi | Sedang | jadikan workbook BKU existing sebagai baseline UAT |
| Duplikasi pencatatan transfer | Tinggi | Sedang | validasi invoice status dan referensi transfer |
| User enggan berpindah dari Excel | Sedang | Tinggi | UI sederhana, export familiar, pelatihan singkat |
| Data historis siswa hilang saat nonaktif | Tinggi | Rendah | soft deactivation, no hard delete pada histori |

---

# 18. Success Metrics / KPI

## 18.1 Product metrics

- Persentase tagihan bulanan yang berhasil digenerate tanpa error
- Waktu rata-rata pencatatan pembayaran
- Jumlah error import per batch
- Persentase transaksi dengan bukti/referensi lengkap

## 18.2 Business metrics

- Penurunan waktu pembuatan laporan bulanan
- Penurunan temuan mismatch saat audit
- Persentase pembayaran yang tercatat di hari yang sama
- Kecepatan penelusuran histori pembayaran per siswa

## 18.3 Technical metrics

- Error rate aplikasi
- Response time endpoint utama
- Job success rate untuk import/generate/export
- Backup success rate

---

# 19. Acceptance Criteria

## 19.1 Login

- User aktif dengan kredensial benar dapat login.
- User nonaktif tidak dapat login.
- User hanya melihat menu sesuai role.

## 19.2 Master siswa

- Admin dapat menambah dan mengedit siswa dengan field wajib lengkap.
- Sistem menolak NIS/NISN duplikat.
- Siswa nonaktif tidak menerima tagihan baru tetapi tetap muncul di histori.

## 19.3 Import siswa

- Sistem menerima file sesuai template.
- Sistem menampilkan hasil validasi sebelum commit.
- Sistem hanya mengimpor row valid.
- Sistem menyimpan log import.

## 19.4 Master tarif

- Admin dapat membuat tarif SPP per angkatan.
- Admin dapat membuat biaya kegiatan dengan cicilan.
- Admin dapat membuat biaya makan boarding bulanan.
- Sistem menolak overlap tarif aktif pada periode sama.

## 19.5 Generate tagihan

- Sistem dapat menghasilkan SPP bulanan untuk siswa aktif.
- Sistem hanya menghasilkan uang makan untuk siswa boarding aktif.
- Sistem mencegah duplikasi invoice.
- Sistem menampilkan jumlah data generated dan skipped.

## 19.6 Pembayaran

- Sistem menolak pembayaran parsial untuk SPP.
- Sistem menerima pembayaran parsial untuk uang kegiatan.
- Sistem menerima satu payment untuk beberapa invoice.
- Sistem menolak nominal melebihi outstanding.
- Sistem membuat nomor bukti unik.

## 19.7 Edit posted transaction

- Hanya role berwenang yang bisa edit transaksi posted.
- Alasan edit wajib diisi.
- Before/after perubahan tercatat di audit log.
- Laporan menggunakan data terbaru tanpa kehilangan histori revisi.

## 19.8 Kas dan ledger

- Pembayaran posted otomatis membentuk kas masuk.
- Kas keluar wajib punya kategori.
- Ledger dapat difilter per akun cash/bank.
- Laporan baseline BKU konsisten dengan ledger sumber.

## 19.9 Laporan

- Laporan harian, bulanan, tahunan, dan per siswa dapat diexport.
- Laporan BKU baseline dapat dihasilkan dari sistem.
- Kepala Madrasah dan Waka hanya dapat melihat dan mengekspor laporan, tidak mengedit data.

---

# 20. Testing Strategy

## 20.1 Unit test
Fokus pada rule SPP, cicilan uang kegiatan, uang makan boarding, anti-duplikat generate, distribusi payment item, dan audit log edit posted.

## 20.2 Integration test

- login + authorization
- import siswa
- setup tarif
- generate tagihan
- create payment multi-item
- edit posted payment
- kas keluar dan ledger
- export laporan

## 20.3 End-to-end test

1. Import siswa.
2. Setup tarif.
3. Generate tagihan bulan berjalan.
4. Bayar SPP satu siswa.
5. Bayar cicilan uang kegiatan.
6. Catat transfer bank manual.
7. Edit transaksi posted dengan alasan.
8. Cetak kwitansi.
9. Lihat laporan BKU.

## 20.4 UAT
Melibatkan Bendahara, Admin Keuangan, dan perwakilan pimpinan untuk dashboard/laporan.

## 20.5 Security test
Uji brute force login, authorization bypass, CSRF, sanitasi input, dan upload abuse dasar.

---

# 21. Development Phases / Roadmap

## 21.1 MVP

- Login dan role
- Master siswa
- Import Excel
- Master tarif
- Generate tagihan
- Pembayaran cash
- Transfer manual sederhana
- Kas dan ledger dasar
- Laporan utama
- Cetak tagihan dan kwitansi
- Audit log dasar

## 21.2 Phase 2

- Refinement laporan baseline BKU
- Filter dan dashboard lebih kaya
- Lampiran bukti transaksi lebih rapi
- Penguncian periode / period closing bila dibutuhkan audit yang lebih ketat
- Template dokumen resmi sekolah

## 21.3 Future enhancements

- Portal siswa/orang tua
- Integrasi notifikasi
- Rekonsiliasi bank semi-otomatis
- Payment gateway jika diperlukan di masa depan

---

# 22. Estimasi Kompleksitas

| Modul | Kompleksitas | Alasan singkat |
|---|---|---|
| Login dan RBAC | Medium | role cukup banyak namun pola standar |
| Master siswa | Medium | CRUD + relasi dan histori |
| Import Excel | High | validasi massal dan error handling detail |
| Master tarif | Medium | perlu rule per fee type dan periode |
| Generate tagihan | High | batch processing dan anti-duplikat |
| Pembayaran | High | multi-item, business rule ketat, edit posted |
| Kas dan ledger | High | kebutuhan laporan baseline BKU dan konsistensi saldo |
| Dashboard | Medium | agregasi dan filter |
| Laporan | High | banyak format dan kebutuhan audit |
| Audit log | Medium | lintas entitas dan before/after snapshot |
| PDF dokumen | Medium | template tagihan/kwitansi/laporan |

---

# 23. Rekomendasi Struktur Project

```text
app/
  Domain/
    Auth/
    Students/
    Fees/
    Billing/
    Payments/
    CashLedger/
    Reports/
    Audit/

  Http/
    Controllers/
      Web/
      Api/
    Requests/
    Middleware/

  Livewire/
    Dashboard/
    Students/
    Fees/
    Billing/
    Payments/
    Reports/

  Models/
  Policies/
  Providers/

config/
database/
  migrations/
  seeders/

resources/
  views/
    layouts/
    auth/
    dashboard/
    students/
    fees/
    billing/
    payments/
    reports/
    components/
  css/
  js/

routes/
  web.php
  api.php

storage/
tests/
  Feature/
  Unit/
```

---

# 24. Pertanyaan Terbuka

Dokumen ini sudah siap dipakai sebagai dokumen kerja awal. Namun ada beberapa hal yang masih sebaiknya dikonfirmasi sebelum implementation sprint dimulai:

1. Tanggal jatuh tempo standar setiap bulan ditetapkan tanggal berapa?
2. Apakah sekolah ingin fitur period closing untuk mengunci bulan tertentu agar transaksi lama tidak lagi bisa diedit?
3. Daftar kategori pengeluaran kas keluar apa saja yang ingin dijadikan master awal?
4. Apakah bukti transfer wajib diupload file, atau cukup nomor referensi dan catatan manual?
5. Pola penomoran resmi untuk invoice, kwitansi, dan bukti kas ingin mengikuti format tertentu atau cukup format sistem standar?
6. Apakah laporan perlu langsung mencantumkan tanda tangan pejabat tertentu di template PDF?

---

# Lampiran A. Matriks Prioritas Fitur

| Prioritas | Modul / fitur |
|---|---|
| P0 | login, RBAC, master siswa, import siswa, master tarif, generate tagihan, pembayaran, edit posted transaction, kas dan ledger, laporan utama, cetak tagihan/kwitansi, audit log, user management |
| P1 | transfer bank manual refinement, lampiran bukti, penyempurnaan template laporan baseline, period closing |
| P2 | notifikasi, portal eksternal, integrasi pembayaran, rekonsiliasi bank |

---

# Lampiran B. Baseline Laporan Existing yang Harus Menjadi Acuan

Berdasarkan workbook existing yang diberikan, sistem minimal harus mampu menghasilkan struktur laporan yang kompatibel dengan pola berikut:

1. Cover / identitas Buku Kas Umum Komite.
2. BKU (Buku Kas Umum) dengan pemisahan debit dan kredit yang mencakup bank, cash, admin bank, reguler/fullday, dan boarding school.
3. Buku Kas Tunai.
4. Buku Pembantu Penerimaan Uang Komite - Cash.
5. Buku Pembantu Penerimaan Uang - Bank.
6. Buku Pembantu Penerimaan Uang Komite - Cash dan Bank.

Format visual final dapat disesuaikan lagi pada fase desain laporan, tetapi struktur data dan keluaran sistem harus mampu merepresentasikan kebutuhan tersebut.

---

# Lampiran C. Rekomendasi Eksekusi Bertahap di VS Code / Codex

## Tahap 1 - Fondasi backend
Fokus:

- setup Laravel 11
- auth + RBAC
- migration master data
- seed role
- student module
- fee module
- billing cycle module

Deliverable:

- login jalan
- CRUD siswa jalan
- CRUD tarif jalan
- skema database stabil

## Tahap 2 - Engine tagihan
Fokus:

- generate invoice SPP
- generate invoice boarding meal
- fee kegiatan installment-ready
- anti-duplicate billing

Deliverable:

- tagihan periodik bisa dibangkitkan
- outstanding invoice benar

## Tahap 3 - Pembayaran dan ledger
Fokus:

- payment multi-item
- validasi SPP full payment only
- payment item allocation
- cash/bank ledger entries
- edit posted transaction + audit

Deliverable:

- pembayaran tercatat
- ledger otomatis terbentuk
- histori edit aman

## Tahap 4 - Laporan dan PDF
Fokus:

- laporan harian/bulanan/tahunan/per siswa
- tunggakan
- BKU baseline
- kwitansi
- invoice print

Deliverable:

- laporan siap UAT
- PDF siap dicetak

## Tahap 5 - Refinement integrasi internal
Fokus:

- import Excel final
- kategori pengeluaran
- export Excel/PDF
- dashboard pimpinan
- hardening security

Deliverable:

- sistem siap pilot
- siap UAT final dan audit review
