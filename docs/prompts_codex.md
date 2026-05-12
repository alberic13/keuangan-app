# Prompts Codex - E-Keuangan MAN 2 Surakarta

**Versi:** 1.0  
**Tanggal:** 15 April 2026  
**Tujuan:** Kumpulan prompt siap pakai untuk Codex/AI coding assistant di VS Code agar implementasi berjalan bertahap, konsisten, dan selaras dengan dokumen proyek.

---

## 1. Cara Pakai

Gunakan prompt ini dengan asumsi file berikut sudah ada di folder `docs/`:
- `docs/prd.md`
- `docs/workflow.md`
- `docs/erd.md`
- `docs/api-spec.md`
- `docs/backlog.md`

### Pola penggunaan yang disarankan
1. Buka satu task atau satu modul.
2. Minta Codex membaca dokumen referensi yang relevan.
3. Minta Codex mengerjakan satu scope kecil, bukan seluruh sistem sekaligus.
4. Setelah selesai, lanjutkan dengan prompt review/refactor/test.

### Template prompt dasar
```md
Baca dokumen berikut sebagai source of truth:
- docs/prd.md
- docs/workflow.md
- docs/erd.md
- docs/api-spec.md
- docs/backlog.md

Kerjakan [TASK-ID / nama modul] saja.
Jangan mengerjakan modul lain.
Gunakan stack Laravel 11 + MySQL + Blade/Livewire.
Ikuti business rules di PRD dan workflow.
Setelah coding, jelaskan file yang dibuat/diubah, keputusan teknis, dan langkah verifikasi manual.
```

---

## 2. Prompt Setup Awal Project

## Prompt 2.1 - Audit struktur project
```md
Baca:
- docs/prd.md
- docs/backlog.md

Tinjau struktur project Laravel ini dan beri rekomendasi struktur folder yang paling cocok untuk E-Keuangan MAN 2 Surakarta.
Fokus hanya pada:
- app/Domain
- app/Http
- app/Livewire
- resources/views
- routes
- tests

Jangan mengubah kode dulu. Berikan usulan struktur final yang ringkas dan realistis.
```

## Prompt 2.2 - Inisialisasi foundation
```md
Baca:
- docs/prd.md
- docs/backlog.md bagian EPIC-01

Implement TASK-001 sampai TASK-004:
- inisialisasi project foundation,
- setup auth dasar,
- setup RBAC dengan Spatie Permission,
- seed role dan user awal.

Batasan:
- gunakan Laravel 11,
- session-based auth,
- role: admin_keuangan, bendahara, kepala_madrasah, waka, admin_tu,
- kepala_madrasah dan waka default read-only.

Buat kode, migration, seeder, dan jelaskan langkah menjalankannya.
```

---

## 3. Prompt Master Data Siswa

## Prompt 3.1 - Migration referensi akademik dan siswa
```md
Baca:
- docs/erd.md
- docs/backlog.md bagian EPIC-02

Implement TASK-005 dan TASK-006.
Buat migration untuk:
- batches
- classes
- majors
- students

Ikuti field dan relasi di docs/erd.md.
Tambahkan index yang penting.
Jangan membuat controller atau UI dulu.
```

## Prompt 3.2 - API CRUD siswa
```md
Baca:
- docs/prd.md bagian Master Data Siswa
- docs/workflow.md bagian Workflow Master Data Siswa
- docs/erd.md tabel students
- docs/api-spec.md modul Students
- docs/backlog.md TASK-007

Implement backend CRUD students:
- list
- detail
- create
- update
- deactivate

Kebutuhan:
- validasi NIS/NISN unik,
- filter batch/class/major/student_type/is_active,
- siswa inactive tidak dihapus,
- gunakan Form Request, Policy, Service bila perlu.

Setelah selesai, tampilkan file yang dibuat dan contoh request/response.
```

## Prompt 3.3 - UI master siswa
```md
Baca:
- docs/prd.md
- docs/workflow.md
- docs/backlog.md TASK-008

Implement halaman master siswa dengan Blade/Livewire:
- tabel list siswa,
- filter,
- form create/edit,
- action deactivate.

Pastikan ada state:
- loading
- empty
- error validation

Jangan kerjakan import dulu.
```

---

## 4. Prompt Workflow Data Upload / Import

## Prompt 4.1 - Backend preview import siswa
```md
Baca:
- docs/workflow.md bagian Workflow Data Upload / Import Siswa
- docs/erd.md tabel import_logs dan import_log_rows
- docs/api-spec.md modul Import Siswa / Workflow Upload
- docs/backlog.md TASK-009 sampai TASK-011

Implement backend preview import siswa.
Kebutuhan:
- endpoint download template,
- endpoint preview upload,
- parsing xlsx,
- validasi per row,
- hasilkan preview token,
- simpan import_logs/import_log_rows bila perlu.

Business rule penting:
- import belum commit ke tabel students,
- tampilkan valid_rows, invalid_rows, error per row,
- student_type hanya regular/full_day/boarding,
- referensi batch/class/major harus valid.

Gunakan Laravel Excel jika cocok.
```

## Prompt 4.2 - Backend commit import siswa
```md
Baca:
- docs/workflow.md bagian Workflow Data Upload / Import Siswa
- docs/backlog.md TASK-012
- docs/api-spec.md endpoint commit import

Implement commit import siswa berdasarkan preview token.
Kebutuhan:
- insert jika siswa belum ada,
- update jika siswa sudah ada berdasarkan nis atau nisn,
- row invalid tidak ikut tersimpan,
- simpan log hasil import,
- pastikan histori transaksi lama aman.

Jelaskan juga strategy transaction/chunking yang dipakai.
```

## Prompt 4.3 - UI import siswa dengan preview
```md
Baca:
- docs/workflow.md bagian Workflow Data Upload / Import Siswa
- docs/backlog.md TASK-013

Implement UI import siswa:
- upload file,
- preview hasil validasi,
- tabel error per row,
- tombol commit import.

Kebutuhan UI:
- badge valid/invalid,
- summary total/valid/invalid,
- jangan auto commit saat upload,
- tampilkan feedback jelas jika file salah.
```

---

## 5. Prompt Fee Types & Fee Schemes

## Prompt 5.1 - Backend fee types dan fee schemes
```md
Baca:
- docs/prd.md bagian Master Jenis Biaya dan Tarif
- docs/workflow.md bagian Workflow Setup Tarif Biaya
- docs/erd.md tabel fee_types dan fee_schemes
- docs/api-spec.md modul Fee Types & Fee Schemes
- docs/backlog.md TASK-014 dan TASK-015

Implement migration dan backend CRUD fee types + fee schemes.

Rule wajib:
- SPP monthly, tidak bisa dicicil,
- uang kegiatan bisa dicicil,
- uang makan boarding only dan monthly,
- tarif tidak boleh overlap pada periode aktif yang sama.
```

## Prompt 5.2 - UI master tarif
```md
Baca:
- docs/backlog.md TASK-016
- docs/prd.md

Implement halaman master tarif dengan Livewire.
Kebutuhan:
- list fee types,
- list fee schemes,
- form create/edit,
- pesan konflik tarif jika overlap.
```

---

## 6. Prompt Billing & Generate Tagihan

## Prompt 6.1 - Billing cycles
```md
Baca:
- docs/erd.md tabel billing_cycles
- docs/api-spec.md modul Billing Cycle
- docs/backlog.md TASK-017 dan TASK-018

Implement migration dan API billing cycles.
Kebutuhan:
- create/list/update/close cycle,
- status open/closed,
- due_date tersedia.
```

## Prompt 6.2 - Service generate SPP
```md
Baca:
- docs/prd.md bagian Generate Tagihan
- docs/workflow.md bagian Workflow Generate Tagihan Bulanan
- docs/backlog.md TASK-019

Implement service generate invoice SPP bulanan.
Rule wajib:
- siswa active saja,
- pakai fee scheme SPP umum lintas angkatan,
- tidak boleh duplikat untuk kombinasi siswa + fee + periode,
- status awal unpaid.

Tolong buat juga unit test untuk rule anti-duplikat dan student active filter.
```

## Prompt 6.3 - Service generate uang makan boarding
```md
Baca:
- docs/workflow.md bagian generate uang makan boarding
- docs/backlog.md TASK-020

Implement service generate invoice uang makan.
Rule:
- hanya student_type boarding,
- bulanan,
- pakai fee scheme aktif.
```

## Prompt 6.4 - Service generate uang kegiatan
```md
Baca:
- docs/workflow.md bagian generate uang kegiatan
- docs/backlog.md TASK-021

Implement service generate invoice uang kegiatan.
Kebutuhan:
- support reference_name / nama program,
- invoice bisa dicicil,
- tidak boleh duplikat pada kombinasi yang sama.
```

## Prompt 6.5 - UI generate tagihan
```md
Baca:
- docs/backlog.md TASK-022
- docs/api-spec.md modul Billing / Invoices

Implement halaman generate tagihan:
- pilih fee type,
- pilih billing cycle,
- filter target,
- tampilkan hasil generated/skipped/failed.
```

---

## 7. Prompt Payments Multi-Item

## Prompt 7.1 - Migration payments dan payment_items
```md
Baca:
- docs/erd.md tabel payments dan payment_items
- docs/backlog.md TASK-023

Implement migration payments dan payment_items sesuai relasi dan field yang dibutuhkan.
```

## Prompt 7.2 - Backend create payment multi-item
```md
Baca:
- docs/prd.md bagian Pembayaran Tagihan
- docs/workflow.md bagian Workflow Pembayaran Tagihan
- docs/api-spec.md modul Payments
- docs/backlog.md TASK-026

Implement service dan endpoint create payment multi-item.

Rule wajib:
- satu payment boleh banyak invoice,
- semua invoice milik student yang sama,
- SPP tidak boleh parsial,
- uang kegiatan boleh parsial,
- uang makan dibayar penuh,
- overpayment ditolak,
- update paid_amount, outstanding_amount, status invoice otomatis.

Buat unit test untuk semua rule utama.
```

## Prompt 7.3 - UI pembayaran multi-item
```md
Baca:
- docs/backlog.md TASK-027
- docs/workflow.md bagian Workflow Pembayaran Tagihan

Implement UI pembayaran:
- cari siswa,
- tampilkan invoice open,
- pilih multi invoice,
- isi nominal per item,
- pilih metode cash atau bank_transfer,
- simpan payment,
- tampilkan error rule secara jelas.
```

## Prompt 7.4 - Dukungan transfer manual
```md
Baca:
- docs/prd.md bagian Transfer Bank Manual
- docs/workflow.md bagian pembayaran transfer manual
- docs/backlog.md TASK-028

Tambahkan dukungan transfer manual ke flow payment.
Kebutuhan:
- pilih akun bank,
- input bank_reference,
- ledger masuk ke akun bank,
- tidak perlu approval workflow.
```

---

## 8. Prompt Edit Posted Transaction

## Prompt 8.1 - Backend edit posted payment
```md
Baca:
- docs/prd.md bagian Edit Transaksi Posted
- docs/workflow.md bagian Workflow Edit Transaksi Posted
- docs/backlog.md TASK-029

Implement backend edit payment posted.
Rule wajib:
- hanya role berwenang,
- edited_reason wajib,
- before/after harus tercatat,
- invoice terkait dihitung ulang,
- payment status menjadi edited.

Berikan perhatian pada konsistensi data jika payment item berubah.
```

## Prompt 8.2 - UI edit posted payment
```md
Baca:
- docs/backlog.md TASK-030

Implement UI edit payment posted.
Kebutuhan:
- form edit aman,
- reason wajib,
- tampilkan warning bahwa transaksi akan direvisi,
- tampilkan error jika user tidak berwenang.
```

---

## 9. Prompt Kas, Pengeluaran, dan Ledger

## Prompt 9.1 - Expense categories dan expenses
```md
Baca:
- docs/erd.md tabel expense_categories dan expenses
- docs/api-spec.md modul Expense Categories & Expenses
- docs/backlog.md TASK-031, TASK-032, TASK-034

Implement migration dan backend untuk:
- expense categories,
- expenses,
- validasi kategori wajib,
- integrasi ke ledger out.
```

## Prompt 9.2 - Ledger posting dari payment dan expense
```md
Baca:
- docs/workflow.md bagian Kas Keluar dan Pembayaran
- docs/erd.md tabel cash_ledger_entries
- docs/backlog.md TASK-033 dan TASK-034

Implement service ledger posting.
Kebutuhan:
- payment membuat ledger in,
- expense membuat ledger out,
- source_type dan source_id benar,
- laporan nantinya bisa dibangun dari ledger ini.
```

## Prompt 9.3 - UI kas keluar dan ledger
```md
Baca:
- docs/backlog.md TASK-035

Implement halaman kas keluar dan ledger.
Kebutuhan:
- form kas keluar,
- filter ledger per akun dan tanggal,
- bedakan cash dan bank secara jelas di UI.
```

---

## 10. Prompt Dashboard & Reports

## Prompt 10.1 - Backend dashboard summary
```md
Baca:
- docs/backlog.md TASK-036
- docs/prd.md bagian Dashboard dan Laporan

Implement endpoint dashboard summary, payment trend, dan recent payments.
```

## Prompt 10.2 - UI dashboard
```md
Baca:
- docs/backlog.md TASK-037

Implement dashboard dengan:
- KPI cards,
- grafik tren penerimaan,
- recent payments,
- ringkasan tunggakan.

Pastikan kepala_madrasah dan waka bisa akses read-only.
```

## Prompt 10.3 - Report dasar
```md
Baca:
- docs/api-spec.md modul Reports
- docs/workflow.md bagian Dashboard dan Laporan
- docs/backlog.md TASK-038, TASK-039, TASK-040

Implement backend dan UI untuk laporan:
- kas harian,
- bulanan,
- tahunan,
- per siswa,
- tunggakan.

Pastikan filter berjalan dan hasil bisa diexport nanti.
```

---

## 11. Prompt BKU Baseline Reports

## Prompt 11.1 - Mapping BKU existing
```md
Baca:
- docs/prd.md Lampiran baseline laporan existing
- docs/workflow.md
- docs/backlog.md TASK-041

Buat dokumen/mapping internal bagaimana data dari cash_ledger_entries, payments, dan expenses diterjemahkan ke format:
- BKU
- Buku Kas Tunai
- Buku Pembantu Penerimaan Cash
- Buku Pembantu Penerimaan Bank
- Buku Pembantu Penerimaan Cash+Bank

Jangan coding dulu. Fokus pada mapping field dan query strategy.
```

## Prompt 11.2 - Implement BKU endpoints
```md
Baca:
- docs/backlog.md TASK-042 sampai TASK-046
- hasil mapping BKU yang sudah dibuat

Implement endpoint laporan baseline BKU secara bertahap.
Mulai dari BKU dulu, lalu cash book, lalu receipt books.
Pastikan konsistensi angka terhadap ledger source.
```

## Prompt 11.3 - UI laporan baseline BKU
```md
Baca:
- docs/backlog.md TASK-047

Implement menu laporan baseline BKU.
Kebutuhan:
- pilih jenis laporan,
- filter tanggal,
- preview tabel,
- tombol export.
```

---

## 12. Prompt Audit Log & Security

## Prompt 12.1 - Audit log backend
```md
Baca:
- docs/erd.md tabel audit_logs
- docs/workflow.md bagian Workflow Audit Trail
- docs/backlog.md TASK-048 dan TASK-049

Implement audit log untuk aksi penting:
- create/update/deactivate siswa,
- import siswa,
- create/update fee scheme,
- generate tagihan,
- create/edit payment,
- create expense.

Simpan actor, entity_type, entity_id, action, reason, before_json, after_json.
```

## Prompt 12.2 - Security hardening
```md
Baca:
- docs/prd.md bagian Security Requirements
- docs/backlog.md TASK-051

Implement security hardening minimal:
- rate limiting login,
- CSRF protection check,
- validasi request ketat,
- authorization policy review,
- secure headers bila relevan.

Jelaskan apa yang diubah dan kenapa.
```

---

## 13. Prompt PDF, Export, dan Print

## Prompt 13.1 - Kwitansi PDF
```md
Baca:
- docs/backlog.md TASK-052
- docs/prd.md bagian Cetak Tagihan dan Kwitansi

Implement generator PDF untuk kwitansi pembayaran.
Kebutuhan:
- nomor bukti unik,
- detail payment dan item invoice,
- layout A4 sederhana dan formal.
```

## Prompt 13.2 - Tagihan PDF
```md
Baca:
- docs/backlog.md TASK-053

Implement generator PDF tagihan siswa.
Kebutuhan:
- identitas siswa,
- rincian invoice,
- outstanding,
- periode.
```

## Prompt 13.3 - Export Excel/PDF laporan
```md
Baca:
- docs/api-spec.md modul Reports
- docs/backlog.md

Tambahkan fitur export PDF/Excel untuk laporan utama dan baseline BKU.
Jelaskan struktur class export/generator yang dipakai.
```

---

## 14. Prompt Testing

## Prompt 14.1 - Unit tests business rules
```md
Baca:
- docs/backlog.md TASK-055
- docs/prd.md bagian Acceptance Criteria

Buat unit tests untuk rule penting:
- SPP tidak boleh parsial,
- uang kegiatan boleh parsial,
- uang makan hanya boarding,
- anti-duplikat generate invoice,
- edit payment menghitung ulang invoice.
```

## Prompt 14.2 - Integration tests workflow utama
```md
Baca:
- docs/workflow.md
- docs/backlog.md TASK-056

Buat integration tests untuk alur:
- import siswa,
- setup tarif,
- generate tagihan,
- create payment multi-item,
- edit posted payment,
- create expense,
- cek ledger,
- akses laporan.
```

---

## 15. Prompt Review & Refactor

## Prompt 15.1 - Review modul yang baru dibuat
```md
Tinjau kode yang baru dibuat untuk modul ini.
Bandingkan dengan:
- docs/prd.md
- docs/workflow.md
- docs/erd.md
- docs/api-spec.md

Cari:
- mismatch business rules,
- query yang rawan bug,
- validasi yang kurang,
- peluang refactor service/repository/policy,
- test yang belum ada.

Jangan rewrite total. Fokus beri patch yang perlu saja.
```

## Prompt 15.2 - Review keamanan otorisasi
```md
Audit modul ini dari sisi authorization.
Pastikan:
- kepala_madrasah dan waka read-only,
- hanya admin_keuangan dan bendahara yang dapat edit posted payment,
- import siswa hanya admin_keuangan,
- audit log tidak bisa diubah user biasa.

Tunjukkan file yang perlu diperbaiki.
```

---

## 16. Prompt UAT & Demo

## Prompt 16.1 - Siapkan data demo
```md
Baca:
- docs/backlog.md TASK-058

Buat seeder data demo realistis untuk E-Keuangan:
- beberapa batch,
- siswa regular dan boarding,
- tarif SPP, uang kegiatan, uang makan,
- invoice sample,
- payment sample,
- expense sample.

Jangan mengubah rule bisnis.
```

## Prompt 16.2 - Siapkan checklist UAT
```md
Baca:
- docs/prd.md bagian Acceptance Criteria
- docs/workflow.md
- docs/backlog.md TASK-057

Buat file checklist UAT markdown yang bisa dipakai bendahara, admin keuangan, dan pimpinan.
Pisahkan test case per modul dan beri kolom pass/fail/catatan.
```

---

## 17. Strategi Pakai Prompt Secara Bertahap

### Sesi awal
- Prompt 2.2
- Prompt 3.1
- Prompt 3.2
- Prompt 3.3

### Sesi import data
- Prompt 4.1
- Prompt 4.2
- Prompt 4.3

### Sesi transaksi inti
- Prompt 5.1
- Prompt 5.2
- Prompt 6.1 sampai 6.5
- Prompt 7.1 sampai 7.4
- Prompt 8.1 sampai 8.2
- Prompt 9.1 sampai 9.3

### Sesi laporan
- Prompt 10.1 sampai 10.3
- Prompt 11.1 sampai 11.3
- Prompt 13.1 sampai 13.3

### Sesi stabilisasi
- Prompt 12.1
- Prompt 12.2
- Prompt 14.1
- Prompt 14.2
- Prompt 15.1
- Prompt 15.2

---

## 18. Tips Supaya Codex Lebih Akurat

- Selalu sebut dokumen acuan.
- Selalu batasi scope prompt ke satu task/modul.
- Minta Codex menampilkan file yang diubah.
- Setelah tiap modul selesai, pakai prompt review.
- Untuk bug, sertakan pesan error dan file terkait.
- Untuk refactor, minta patch minimal, bukan rewrite penuh.

---

## 19. Prompt Super Ringkas untuk Harian

### Backend
```md
Kerjakan TASK-026 di docs/backlog.md.
Ikuti docs/prd.md, docs/workflow.md, docs/erd.md, docs/api-spec.md.
Scope hanya backend.
Jangan sentuh UI.
Tampilkan file yang diubah dan alasan teknis.
```

### Frontend
```md
Kerjakan TASK-027 di docs/backlog.md.
Ikuti docs/prd.md, docs/workflow.md, docs/api-spec.md.
Scope hanya UI/UX halaman pembayaran multi-item.
Jangan ubah logic backend.
```

### Integrasi
```md
Tinjau integrasi modul pembayaran terhadap invoice dan ledger.
Bandingkan implementasi saat ini dengan docs/workflow.md dan docs/api-spec.md.
Perbaiki mismatch tanpa rewrite besar.
```

### Testing
```md
Buat test untuk rule bisnis modul ini berdasarkan docs/prd.md bagian Acceptance Criteria dan docs/backlog.md.
Fokus pada edge case yang paling berisiko.
```
