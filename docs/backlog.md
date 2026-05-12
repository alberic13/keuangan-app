# Backlog - E-Keuangan MAN 2 Surakarta

**Versi:** 1.0  
**Tanggal:** 15 April 2026  
**Tujuan:** Breakdown pekerjaan implementasi untuk frontend, backend, database, testing, dan integrasi berdasarkan `prd.md`, `workflow.md`, `erd.md`, dan `api-spec.md`.

---

## 1. Cara Pakai Dokumen Ini

Dokumen ini dipakai untuk:
- menyusun sprint,
- membagi kerja frontend / backend / integrasi,
- menentukan dependency antar task,
- menjadi checklist eksekusi di VS Code / Codex.

### Status task yang direkomendasikan
- `todo`
- `in_progress`
- `review`
- `done`
- `blocked`

### Prioritas
- `P0` = wajib untuk sistem inti
- `P1` = penting setelah inti stabil
- `P2` = enhancement lanjutan

### Label domain
- `backend`
- `frontend`
- `database`
- `integration`
- `qa`
- `devops`
- `docs`

---

## 2. Epic Summary

| Epic | Nama | Prioritas | Tujuan |
|---|---|---:|---|
| EPIC-01 | Project Foundation | P0 | Menyiapkan fondasi project Laravel, auth, dan struktur dasar |
| EPIC-02 | Master Data Akademik & Siswa | P0 | Menyiapkan data inti siswa dan referensi akademik |
| EPIC-03 | Import Data Siswa | P0 | Upload, preview, dan commit import Excel |
| EPIC-04 | Fee Types & Fee Schemes | P0 | Menetapkan struktur biaya dan tarif |
| EPIC-05 | Billing Cycle & Generate Tagihan | P0 | Membuat siklus billing dan invoice siswa |
| EPIC-06 | Payments Multi-Item | P0 | Mencatat pembayaran cash/transfer manual |
| EPIC-07 | Edit Posted Transaction | P0 | Koreksi transaksi posted dengan audit trail |
| EPIC-08 | Cash Accounts, Expenses & Ledger | P0 | Mengelola kas masuk/keluar dan ledger |
| EPIC-09 | Dashboard & Reports | P0 | Dashboard dan laporan operasional/audit |
| EPIC-10 | BKU Baseline Reports | P1 | Menyesuaikan laporan dengan workbook existing |
| EPIC-11 | Audit Log & Security Hardening | P0 | Menjaga auditability dan keamanan aplikasi |
| EPIC-12 | UX Refinement & Print | P1 | Menyempurnakan flow UI dan dokumen cetak |
| EPIC-13 | QA, UAT, Release Prep | P0 | Menyiapkan testing, UAT, dan go-live |

---

## 3. Phase Plan

## Phase 1 - Foundation
Target:
- auth,
- role & permission,
- master referensi,
- master siswa,
- import siswa.

## Phase 2 - Transaksi Inti
Target:
- fee types,
- fee schemes,
- billing cycles,
- generate invoice,
- pembayaran,
- edit posted,
- ledger.

## Phase 3 - Laporan dan Audit
Target:
- dashboard,
- laporan dasar,
- BKU baseline reports,
- export PDF/Excel,
- audit log.

## Phase 4 - Stabilization
Target:
- QA,
- UAT,
- hardening,
- performance,
- deployment prep.

---

## 4. Detailed Backlog

## EPIC-01 Project Foundation

### TASK-001 Inisialisasi project Laravel
- **Prioritas:** P0
- **Label:** backend, devops
- **Deskripsi:** Buat project Laravel, setup environment, konfigurasi database, queue, dan struktur folder domain.
- **Acceptance criteria:**
  - project bisa running local,
  - koneksi database aktif,
  - environment `.env.example` rapi,
  - struktur folder domain tersedia.
- **Dependency:** none
- **Estimasi:** M

### TASK-002 Setup autentikasi dasar
- **Prioritas:** P0
- **Label:** backend
- **Deskripsi:** Implement session-based authentication.
- **Acceptance criteria:**
  - login/logout bekerja,
  - middleware auth aktif,
  - session aman.
- **Dependency:** TASK-001
- **Estimasi:** M

### TASK-003 Setup role & permission
- **Prioritas:** P0
- **Label:** backend, database
- **Deskripsi:** Tambahkan RBAC menggunakan Spatie Permission.
- **Acceptance criteria:**
  - role admin keuangan, bendahara, kepala madrasah, waka, admin TU tersedia,
  - hak akses menu bisa dibatasi,
  - kepala madrasah & waka read-only.
- **Dependency:** TASK-002
- **Estimasi:** M

### TASK-004 Seed user dan role awal
- **Prioritas:** P0
- **Label:** database
- **Deskripsi:** Buat seeder role dan user sample.
- **Acceptance criteria:**
  - seeder bisa dijalankan,
  - akun awal tersedia untuk testing.
- **Dependency:** TASK-003
- **Estimasi:** S

---

## EPIC-02 Master Data Akademik & Siswa

### TASK-005 Migration batches, classes, majors
- **Prioritas:** P0
- **Label:** database
- **Deskripsi:** Buat migration tabel referensi akademik.
- **Acceptance criteria:**
  - tabel terbentuk,
  - FK siap dipakai students.
- **Dependency:** TASK-001
- **Estimasi:** S

### TASK-006 Migration students
- **Prioritas:** P0
- **Label:** database
- **Deskripsi:** Buat migration students sesuai ERD.
- **Acceptance criteria:**
  - field nis, nisn, batch, class, major, student_type tersedia,
  - index penting dibuat,
  - soft deactivation didukung.
- **Dependency:** TASK-005
- **Estimasi:** S

### TASK-007 API CRUD master siswa
- **Prioritas:** P0
- **Label:** backend
- **Deskripsi:** Implement endpoint list/detail/create/update/deactivate siswa.
- **Acceptance criteria:**
  - validasi NIS/NISN unik,
  - deactivate tidak menghapus histori,
  - filter siswa tersedia.
- **Dependency:** TASK-006
- **Estimasi:** M

### TASK-008 UI master siswa
- **Prioritas:** P0
- **Label:** frontend
- **Deskripsi:** Buat halaman list, filter, create/edit form, deactivate.
- **Acceptance criteria:**
  - tabel siswa tampil,
  - form validasi tampil jelas,
  - filter batch/class/major/student_type bekerja.
- **Dependency:** TASK-007
- **Estimasi:** M

---

## EPIC-03 Import Data Siswa

### TASK-009 Endpoint download template import
- **Prioritas:** P0
- **Label:** backend
- **Deskripsi:** Sediakan endpoint/template file import siswa.
- **Acceptance criteria:**
  - template bisa diunduh,
  - header sesuai workflow upload.
- **Dependency:** TASK-007
- **Estimasi:** S

### TASK-010 Migration import_logs dan import_log_rows
- **Prioritas:** P0
- **Label:** database
- **Deskripsi:** Buat tabel log import dan detail row error.
- **Acceptance criteria:**
  - log header dan detail row tersimpan.
- **Dependency:** TASK-001
- **Estimasi:** S

### TASK-011 Service preview import siswa
- **Prioritas:** P0
- **Label:** backend
- **Deskripsi:** Parse file, validasi per row, hasilkan preview token.
- **Acceptance criteria:**
  - preview total/valid/invalid rows tampil,
  - error per row tersedia,
  - belum commit ke students.
- **Dependency:** TASK-010, TASK-007
- **Estimasi:** H

### TASK-012 Service commit import siswa
- **Prioritas:** P0
- **Label:** backend, integration
- **Deskripsi:** Commit row valid menjadi insert/update siswa.
- **Acceptance criteria:**
  - insert/update by NIS/NISN,
  - log import tersimpan,
  - row invalid tidak ikut tersimpan.
- **Dependency:** TASK-011
- **Estimasi:** H

### TASK-013 UI import siswa dengan preview
- **Prioritas:** P0
- **Label:** frontend
- **Deskripsi:** Halaman upload, preview, commit import.
- **Acceptance criteria:**
  - upload file berjalan,
  - preview tabel valid/invalid tampil,
  - commit import butuh aksi eksplisit.
- **Dependency:** TASK-011, TASK-012
- **Estimasi:** M

---

## EPIC-04 Fee Types & Fee Schemes

### TASK-014 Migration fee_types dan fee_schemes
- **Prioritas:** P0
- **Label:** database
- **Deskripsi:** Buat struktur jenis biaya dan tarif.
- **Acceptance criteria:**
  - field installment_allowed, billing_frequency, applies_to tersedia.
- **Dependency:** TASK-001
- **Estimasi:** S

### TASK-015 API fee types & fee schemes
- **Prioritas:** P0
- **Label:** backend
- **Deskripsi:** CRUD fee type dan fee scheme.
- **Acceptance criteria:**
  - rule overlap tarif divalidasi,
  - kategori SPP/activity/meal tersedia.
- **Dependency:** TASK-014
- **Estimasi:** M

### TASK-016 UI master tarif
- **Prioritas:** P0
- **Label:** frontend
- **Deskripsi:** Halaman jenis biaya dan tarif.
- **Acceptance criteria:**
  - admin bisa tambah/edit tarif,
  - konflik tarif ditampilkan jelas.
- **Dependency:** TASK-015
- **Estimasi:** M

---

## EPIC-05 Billing Cycle & Generate Tagihan

### TASK-017 Migration billing_cycles dan invoices
- **Prioritas:** P0
- **Label:** database
- **Deskripsi:** Buat tabel billing cycle dan invoice.
- **Acceptance criteria:**
  - status invoice dan cycle tersedia,
  - unique key anti-duplikat dapat diterapkan.
- **Dependency:** TASK-014, TASK-006
- **Estimasi:** M

### TASK-018 API billing cycles
- **Prioritas:** P0
- **Label:** backend
- **Deskripsi:** CRUD cycle dan close cycle.
- **Acceptance criteria:**
  - cycle open/closed dapat dikelola.
- **Dependency:** TASK-017
- **Estimasi:** S

### TASK-019 Service generate invoice SPP
- **Prioritas:** P0
- **Label:** backend
- **Deskripsi:** Generate SPP bulanan dengan nominal sama untuk semua angkatan.
- **Acceptance criteria:**
  - siswa aktif saja,
  - tidak ada invoice duplikat,
  - SPP selalu full outstanding.
- **Dependency:** TASK-018, TASK-015, TASK-007
- **Estimasi:** H

### TASK-020 Service generate invoice uang makan boarding
- **Prioritas:** P0
- **Label:** backend
- **Deskripsi:** Generate invoice uang makan hanya untuk boarding.
- **Acceptance criteria:**
  - hanya student_type boarding,
  - periode bulanan.
- **Dependency:** TASK-019
- **Estimasi:** M

### TASK-021 Service generate invoice uang kegiatan
- **Prioritas:** P0
- **Label:** backend
- **Deskripsi:** Generate invoice uang kegiatan berbasis event/program.
- **Acceptance criteria:**
  - invoice bisa dicicil,
  - reference_name tersimpan.
- **Dependency:** TASK-019
- **Estimasi:** M

### TASK-022 UI generate tagihan
- **Prioritas:** P0
- **Label:** frontend
- **Deskripsi:** Form generate berdasarkan fee type, periode, filter target.
- **Acceptance criteria:**
  - hasil generated/skipped/failed ditampilkan,
  - user bisa review hasil generate.
- **Dependency:** TASK-019, TASK-020, TASK-021
- **Estimasi:** M

---

## EPIC-06 Payments Multi-Item

### TASK-023 Migration payments dan payment_items
- **Prioritas:** P0
- **Label:** database
- **Deskripsi:** Buat tabel payment header dan detail items.
- **Acceptance criteria:**
  - relasi ke invoices dan students tersedia,
  - field edited_reason tersedia.
- **Dependency:** TASK-017
- **Estimasi:** S

### TASK-024 Migration cash_accounts
- **Prioritas:** P0
- **Label:** database
- **Deskripsi:** Buat tabel akun cash/bank.
- **Acceptance criteria:**
  - type cash/bank tersedia,
  - status aktif tersedia.
- **Dependency:** TASK-001
- **Estimasi:** S

### TASK-025 API cash accounts
- **Prioritas:** P0
- **Label:** backend
- **Deskripsi:** CRUD akun kas/bank.
- **Acceptance criteria:**
  - list akun aktif tersedia untuk pembayaran.
- **Dependency:** TASK-024
- **Estimasi:** S

### TASK-026 Service create payment multi-item
- **Prioritas:** P0
- **Label:** backend
- **Deskripsi:** Buat pembayaran satu header dengan banyak invoice item.
- **Acceptance criteria:**
  - SPP tidak bisa parsial,
  - uang kegiatan bisa parsial,
  - overpayment ditolak,
  - invoice paid/partial ter-update benar.
- **Dependency:** TASK-023, TASK-025
- **Estimasi:** H

### TASK-027 UI pembayaran multi-item
- **Prioritas:** P0
- **Label:** frontend
- **Deskripsi:** Halaman cari siswa, pilih invoice, isi nominal, pilih metode bayar.
- **Acceptance criteria:**
  - multi-select invoice bekerja,
  - validasi rule tampil real-time,
  - receipt action tersedia.
- **Dependency:** TASK-026
- **Estimasi:** H

### TASK-028 Dukungan transfer bank manual
- **Prioritas:** P1
- **Label:** backend, frontend
- **Deskripsi:** Input reference transfer dan akun bank pada flow payment.
- **Acceptance criteria:**
  - payment method bank_transfer berjalan,
  - ledger akun bank bertambah.
- **Dependency:** TASK-026, TASK-027
- **Estimasi:** M

---

## EPIC-07 Edit Posted Transaction

### TASK-029 Service edit posted payment
- **Prioritas:** P0
- **Label:** backend
- **Deskripsi:** Ubah payment posted dengan audit dan recalculation invoice.
- **Acceptance criteria:**
  - alasan edit wajib,
  - before/after tersimpan,
  - outstanding invoice dihitung ulang.
- **Dependency:** TASK-026
- **Estimasi:** H

### TASK-030 UI edit posted payment
- **Prioritas:** P0
- **Label:** frontend
- **Deskripsi:** Form edit payment dengan reason wajib.
- **Acceptance criteria:**
  - hanya role berwenang dapat akses,
  - edited_reason wajib,
  - user mendapat pesan bila melanggar rule.
- **Dependency:** TASK-029
- **Estimasi:** M

---

## EPIC-08 Cash Accounts, Expenses & Ledger

### TASK-031 Migration expense_categories, expenses, cash_ledger_entries
- **Prioritas:** P0
- **Label:** database
- **Deskripsi:** Buat struktur kas keluar, kategori, dan ledger.
- **Acceptance criteria:**
  - category master tersedia,
  - ledger bisa menampung source payment/expense.
- **Dependency:** TASK-024
- **Estimasi:** M

### TASK-032 API expense categories
- **Prioritas:** P0
- **Label:** backend
- **Deskripsi:** CRUD kategori pengeluaran.
- **Acceptance criteria:**
  - kategori bisa dipakai saat input kas keluar.
- **Dependency:** TASK-031
- **Estimasi:** S

### TASK-033 Service posting ledger dari payment
- **Prioritas:** P0
- **Label:** backend, integration
- **Deskripsi:** Otomatis membuat cash_ledger_entry dari payment.
- **Acceptance criteria:**
  - cash masuk tercatat sesuai account dan amount,
  - source_type payment benar.
- **Dependency:** TASK-026, TASK-031
- **Estimasi:** M

### TASK-034 API expenses dan posting kas keluar
- **Prioritas:** P0
- **Label:** backend
- **Deskripsi:** Input expense dan ledger out.
- **Acceptance criteria:**
  - category wajib,
  - ledger out terbentuk,
  - akun harus aktif.
- **Dependency:** TASK-032, TASK-031
- **Estimasi:** M

### TASK-035 UI kas keluar dan ledger
- **Prioritas:** P0
- **Label:** frontend
- **Deskripsi:** Halaman input kas keluar dan halaman ledger.
- **Acceptance criteria:**
  - filter akun dan tanggal tersedia,
  - list ledger tampil jelas cash vs bank.
- **Dependency:** TASK-034, TASK-033
- **Estimasi:** M

---

## EPIC-09 Dashboard & Reports

### TASK-036 Service summary dashboard
- **Prioritas:** P0
- **Label:** backend
- **Deskripsi:** Siapkan endpoint KPI dashboard.
- **Acceptance criteria:**
  - total tagihan, pembayaran, tunggakan, kas masuk tersedia.
- **Dependency:** TASK-026, TASK-033
- **Estimasi:** M

### TASK-037 UI dashboard
- **Prioritas:** P0
- **Label:** frontend
- **Deskripsi:** Tampilkan KPI card, tren, recent payment.
- **Acceptance criteria:**
  - pimpinan read-only bisa mengakses,
  - dashboard responsive desktop/tablet.
- **Dependency:** TASK-036
- **Estimasi:** M

### TASK-038 Report kas harian/bulanan/tahunan
- **Prioritas:** P0
- **Label:** backend
- **Deskripsi:** Endpoint laporan periode berbasis ledger.
- **Acceptance criteria:**
  - filter tanggal bekerja,
  - angka konsisten dengan source ledger.
- **Dependency:** TASK-033, TASK-034
- **Estimasi:** H

### TASK-039 Report per siswa dan tunggakan
- **Prioritas:** P0
- **Label:** backend
- **Deskripsi:** Endpoint laporan student ledger dan arrears.
- **Acceptance criteria:**
  - histori siswa muncul,
  - siswa nonaktif tetap bisa dilaporkan.
- **Dependency:** TASK-026
- **Estimasi:** M

### TASK-040 UI modul laporan dasar
- **Prioritas:** P0
- **Label:** frontend
- **Deskripsi:** Halaman filter dan hasil laporan.
- **Acceptance criteria:**
  - pilih jenis laporan,
  - tampil tabel,
  - export action tersedia.
- **Dependency:** TASK-038, TASK-039
- **Estimasi:** M

---

## EPIC-10 BKU Baseline Reports

### TASK-041 Analisis mapping ledger ke format BKU existing
- **Prioritas:** P1
- **Label:** docs, backend
- **Deskripsi:** Pemetaan struktur ledger ke format workbook existing.
- **Acceptance criteria:**
  - mapping field laporan terdokumentasi,
  - gap format dicatat.
- **Dependency:** TASK-033, TASK-034
- **Estimasi:** M

### TASK-042 Endpoint laporan BKU
- **Prioritas:** P1
- **Label:** backend
- **Deskripsi:** Generate Buku Kas Umum.
- **Acceptance criteria:**
  - debit/kredit sesuai sumber,
  - filter periode tersedia.
- **Dependency:** TASK-041
- **Estimasi:** H

### TASK-043 Endpoint Buku Kas Tunai
- **Prioritas:** P1
- **Label:** backend
- **Deskripsi:** Generate laporan kas tunai.
- **Acceptance criteria:**
  - hanya akun type cash.
- **Dependency:** TASK-041
- **Estimasi:** M

### TASK-044 Endpoint Buku Pembantu Penerimaan Cash
- **Prioritas:** P1
- **Label:** backend
- **Deskripsi:** Laporan penerimaan cash.
- **Acceptance criteria:**
  - sumber payment cash terfilter benar.
- **Dependency:** TASK-041
- **Estimasi:** M

### TASK-045 Endpoint Buku Pembantu Penerimaan Bank
- **Prioritas:** P1
- **Label:** backend
- **Deskripsi:** Laporan penerimaan bank.
- **Acceptance criteria:**
  - sumber payment bank_transfer terfilter benar.
- **Dependency:** TASK-041
- **Estimasi:** M

### TASK-046 Endpoint Buku Pembantu Penerimaan Cash+Bank
- **Prioritas:** P1
- **Label:** backend
- **Deskripsi:** Laporan gabungan cash dan bank.
- **Acceptance criteria:**
  - gabungan tampil konsisten dengan dua laporan sumber.
- **Dependency:** TASK-041
- **Estimasi:** M

### TASK-047 UI laporan baseline BKU
- **Prioritas:** P1
- **Label:** frontend
- **Deskripsi:** Menu dan filter khusus laporan BKU baseline.
- **Acceptance criteria:**
  - user bisa pilih jenis laporan baseline,
  - preview dan export tersedia.
- **Dependency:** TASK-042 sampai TASK-046
- **Estimasi:** M

---

## EPIC-11 Audit Log & Security Hardening

### TASK-048 Migration audit_logs
- **Prioritas:** P0
- **Label:** database
- **Deskripsi:** Buat tabel audit log.
- **Acceptance criteria:**
  - before/after dan reason bisa disimpan.
- **Dependency:** TASK-001
- **Estimasi:** S

### TASK-049 Service audit logging global
- **Prioritas:** P0
- **Label:** backend
- **Deskripsi:** Log aksi penting ke audit_logs.
- **Acceptance criteria:**
  - create/update siswa,
  - import,
  - fee scheme,
  - generate invoice,
  - create/edit payment,
  - create expense tercatat.
- **Dependency:** TASK-048
- **Estimasi:** H

### TASK-050 UI audit logs
- **Prioritas:** P1
- **Label:** frontend
- **Deskripsi:** Halaman daftar audit log dengan filter.
- **Acceptance criteria:**
  - actor/entity/action filter tersedia,
  - detail before/after dapat dibuka.
- **Dependency:** TASK-049
- **Estimasi:** M

### TASK-051 Security hardening
- **Prioritas:** P0
- **Label:** backend, devops
- **Deskripsi:** Rate limit login, CSRF, validation hardening, secure headers.
- **Acceptance criteria:**
  - brute force dasar terproteksi,
  - endpoint sensitif terjaga.
- **Dependency:** TASK-002
- **Estimasi:** M

---

## EPIC-12 UX Refinement & Print

### TASK-052 Generate PDF kwitansi
- **Prioritas:** P0
- **Label:** backend, frontend
- **Deskripsi:** Cetak kwitansi dari payment.
- **Acceptance criteria:**
  - nomor bukti unik,
  - layout A4 rapi.
- **Dependency:** TASK-026
- **Estimasi:** M

### TASK-053 Generate PDF tagihan
- **Prioritas:** P0
- **Label:** backend, frontend
- **Deskripsi:** Cetak invoice/tagihan siswa.
- **Acceptance criteria:**
  - identitas siswa, rincian, outstanding tampil benar.
- **Dependency:** TASK-019, TASK-020, TASK-021
- **Estimasi:** M

### TASK-054 UX polish loading, empty, error states
- **Prioritas:** P1
- **Label:** frontend
- **Deskripsi:** Rapikan skeleton, no data, dan form feedback.
- **Acceptance criteria:**
  - seluruh halaman inti punya state yang jelas.
- **Dependency:** TASK-008, TASK-013, TASK-022, TASK-027, TASK-035, TASK-040
- **Estimasi:** M

---

## EPIC-13 QA, UAT, Release Prep

### TASK-055 Unit test business rules
- **Prioritas:** P0
- **Label:** qa, backend
- **Deskripsi:** Test SPP non-installment, activity installment, boarding meal, anti-duplikat billing.
- **Acceptance criteria:**
  - test otomatis berjalan hijau.
- **Dependency:** TASK-026, TASK-019, TASK-020, TASK-021
- **Estimasi:** H

### TASK-056 Integration test workflow utama
- **Prioritas:** P0
- **Label:** qa
- **Deskripsi:** Test import -> billing -> payment -> ledger -> report.
- **Acceptance criteria:**
  - happy path dan error path utama ter-cover.
- **Dependency:** TASK-013, TASK-022, TASK-027, TASK-035, TASK-040
- **Estimasi:** H

### TASK-057 UAT checklist dengan tim sekolah
- **Prioritas:** P0
- **Label:** qa, docs
- **Deskripsi:** Siapkan checklist UAT berdasarkan PRD dan workflow.
- **Acceptance criteria:**
  - checklist siap dipakai bendahara/admin/pimpinan.
- **Dependency:** mayoritas modul inti selesai
- **Estimasi:** M

### TASK-058 Seed data demo dan skenario demo
- **Prioritas:** P1
- **Label:** backend, docs
- **Deskripsi:** Buat data demo realistis.
- **Acceptance criteria:**
  - ada siswa regular & boarding,
  - ada invoice, payment, expense, report sample.
- **Dependency:** modul inti selesai
- **Estimasi:** M

### TASK-059 Deployment staging
- **Prioritas:** P0
- **Label:** devops
- **Deskripsi:** Deploy ke staging untuk UAT.
- **Acceptance criteria:**
  - environment staging aktif,
  - queue dan storage berjalan.
- **Dependency:** TASK-001, TASK-051, modul inti minimum
- **Estimasi:** M

---

## 5. Suggested Sprint Breakdown

## Sprint 1
- TASK-001 s.d. TASK-008
- Outcome: login, role, master siswa dasar

## Sprint 2
- TASK-009 s.d. TASK-016
- Outcome: import siswa + master tarif

## Sprint 3
- TASK-017 s.d. TASK-022
- Outcome: billing cycle + generate tagihan

## Sprint 4
- TASK-023 s.d. TASK-030
- Outcome: pembayaran + edit posted

## Sprint 5
- TASK-031 s.d. TASK-040
- Outcome: kas/ledger + dashboard/laporan dasar

## Sprint 6
- TASK-041 s.d. TASK-059
- Outcome: BKU baseline + audit + QA + staging

---

## 6. Definition of Done

Sebuah task dianggap selesai jika:
- requirement sesuai PRD/workflow,
- validasi backend tersedia,
- UI state minimal tersedia jika task frontend,
- test dasar dibuat bila task business-critical,
- audit/logging terpenuhi bila task menyentuh data transaksi,
- code sudah review,
- dokumentasi endpoint atau catatan implementasi diperbarui.

---

## 7. Catatan untuk Codex

Saat mengeksekusi task dengan Codex, referensikan dokumen ini bersama:
- `docs/prd.md`
- `docs/workflow.md`
- `docs/erd.md`
- `docs/api-spec.md`

Contoh:
- “Kerjakan TASK-011 di `docs/backlog.md` sesuai `docs/workflow.md` bagian Workflow Data Upload / Import Siswa.”
- “Implement TASK-026 sesuai `docs/api-spec.md` modul Payments dan `docs/erd.md` tabel payments/payment_items.”
- “Kerjakan TASK-042 sampai TASK-046 untuk laporan baseline BKU.”
