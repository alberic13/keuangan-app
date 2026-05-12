# ERD - E-Keuangan MAN 2 Surakarta

**Versi:** 1.0  
**Tanggal:** 15 April 2026  
**Tujuan:** Dokumentasi entitas, relasi, aturan data, dan panduan implementasi database untuk backend Laravel/MySQL.

---

## 1. Prinsip Desain Data

ERD ini dirancang untuk memenuhi kebutuhan berikut:
- master data siswa dengan struktur akademik sederhana,
- tagihan berbeda per jenis biaya,
- pembayaran multi-item,
- ledger cash/bank,
- histori audit,
- pelaporan berbasis BKU existing.

### Aturan desain utama
- Hindari hard delete untuk data transaksional penting.
- Simpan histori perubahan pada audit log.
- Pisahkan entitas master, tagihan, pembayaran, dan ledger.
- Pastikan siswa nonaktif tetap punya histori transaksi.
- Gunakan `effective_start` / `effective_end` untuk tarif yang berubah antar periode.

---

## 2. Daftar Entitas Inti

### 2.1 users
- id (PK)
- name
- username
- email
- password
- is_active
- last_login_at
- created_at
- updated_at

### 2.2 roles
- id (PK)
- name
- description
- created_at
- updated_at

### 2.3 batches
- id
- year_label
- academic_year
- is_active
- created_at
- updated_at

### 2.4 classes
- id
- name
- level
- is_active
- created_at
- updated_at

### 2.5 majors
- id
- code
- name
- is_active
- created_at
- updated_at

### 2.6 students
- id
- nis
- nisn
- full_name
- class_id (FK -> classes.id)
- major_id (FK -> majors.id)
- batch_id (FK -> batches.id)
- student_type (`regular`, `full_day`, `boarding`)
- is_active
- enrollment_date
- exit_date
- created_at
- updated_at

### 2.7 fee_types
- id
- code
- name
- category (`spp`, `activity`, `meal`, `other`)
- installment_allowed
- billing_frequency (`monthly`, `one_time`, `custom`)
- applies_to (`all`, `regular`, `full_day`, `boarding`)
- is_active
- created_at
- updated_at

### 2.8 fee_schemes
- id
- fee_type_id (FK -> fee_types.id)
- batch_id (FK -> batches.id, nullable)
- nominal
- effective_start
- effective_end
- is_active
- created_at
- updated_at

### 2.9 billing_cycles
- id
- month
- year
- period_label
- due_date
- status (`open`, `closed`)
- created_at
- updated_at

### 2.10 invoices
- id
- invoice_no
- student_id (FK -> students.id)
- fee_type_id (FK -> fee_types.id)
- billing_cycle_id (FK -> billing_cycles.id, nullable)
- reference_name (nullable)
- total_amount
- paid_amount
- outstanding_amount
- status (`draft`, `unpaid`, `partial`, `paid`, `void`)
- published_at
- created_by
- updated_by
- created_at
- updated_at

### 2.11 payments
- id
- payment_no
- student_id (FK -> students.id)
- payment_date
- method (`cash`, `bank_transfer`)
- cash_account_id (FK -> cash_accounts.id)
- total_amount
- bank_reference
- notes
- status (`posted`, `edited`, `void`)
- created_by
- edited_by
- edited_reason
- created_at
- updated_at

### 2.12 payment_items
- id
- payment_id (FK -> payments.id)
- invoice_id (FK -> invoices.id)
- amount
- created_at
- updated_at

### 2.13 cash_accounts
- id
- name
- type (`cash`, `bank`)
- account_number
- account_holder
- is_active
- created_at
- updated_at

### 2.14 expense_categories
- id
- code
- name
- is_active
- created_at
- updated_at

### 2.15 expenses
- id
- expense_no
- transaction_date
- category_id (FK -> expense_categories.id)
- payment_account_id (FK -> cash_accounts.id)
- amount
- description
- attachment_path
- status (`posted`, `edited`, `void`)
- created_by
- updated_by
- created_at
- updated_at

### 2.16 cash_ledger_entries
- id
- entry_no
- transaction_date
- account_id (FK -> cash_accounts.id)
- direction (`in`, `out`)
- source_type (`payment`, `expense`, `adjustment`)
- source_id
- amount
- description
- status (`posted`, `void`)
- created_by
- created_at
- updated_at

### 2.17 import_logs
- id
- type (`students_import`)
- file_name
- total_rows
- success_rows
- failed_rows
- imported_by (FK -> users.id)
- metadata_json
- created_at
- updated_at

### 2.18 import_log_rows
- id
- import_log_id (FK -> import_logs.id)
- row_number
- payload_json
- status (`success`, `failed`)
- error_message
- created_at
- updated_at

### 2.19 audit_logs
- id
- actor_id (FK -> users.id)
- entity_type
- entity_id
- action
- reason
- before_json
- after_json
- ip_address
- user_agent
- created_at

---

## 3. Relasi Antar Entitas

### Relasi master
- `students.batch_id -> batches.id`
- `students.class_id -> classes.id`
- `students.major_id -> majors.id`
- `fee_schemes.fee_type_id -> fee_types.id`
- `fee_schemes.batch_id -> batches.id`

### Relasi billing
- `invoices.student_id -> students.id`
- `invoices.fee_type_id -> fee_types.id`
- `invoices.billing_cycle_id -> billing_cycles.id`

### Relasi payment
- `payments.student_id -> students.id`
- `payments.cash_account_id -> cash_accounts.id`
- `payment_items.payment_id -> payments.id`
- `payment_items.invoice_id -> invoices.id`

### Relasi cash
- `expenses.category_id -> expense_categories.id`
- `expenses.payment_account_id -> cash_accounts.id`
- `cash_ledger_entries.account_id -> cash_accounts.id`

### Relasi logging
- `import_logs.imported_by -> users.id`
- `import_log_rows.import_log_id -> import_logs.id`
- `audit_logs.actor_id -> users.id`

---

## 4. Diagram Relasi Teks

```text
users ---< audit_logs
users ---< import_logs
users ---< payments
users ---< expenses

roles >--- users (via pivot / package RBAC)

batches ---< students
classes ---< students
majors  ---< students

fee_types ---< fee_schemes
batches   ---< fee_schemes

students       ---< invoices >--- fee_types
billing_cycles ---< invoices

students ---< payments
cash_accounts ---< payments
payments ---< payment_items >--- invoices

expense_categories ---< expenses
cash_accounts ---< expenses

cash_accounts ---< cash_ledger_entries
payments ------> cash_ledger_entries (source_type=payment)
expenses ------> cash_ledger_entries (source_type=expense)

import_logs ---< import_log_rows
```

---

## 5. Aturan Data Penting

### students
- Jangan hard delete siswa yang sudah punya transaksi.
- Gunakan `is_active = false` untuk nonaktif/alumni.

### invoices
- `outstanding_amount = total_amount - paid_amount`
- status invoice:
  - `unpaid` jika paid_amount = 0
  - `partial` jika 0 < paid_amount < total_amount
  - `paid` jika paid_amount = total_amount

### payments
- Total payment harus sama dengan penjumlahan seluruh payment items.
- Semua payment item harus milik siswa yang sama dengan payment header.
- Jika payment di-edit, invoice terkait harus dihitung ulang.

### fee_schemes
- Perubahan tarif disarankan melalui insert baris baru dengan periode efektif baru, bukan overwrite histori.

### cash_ledger_entries
- Semua laporan BKU diturunkan dari tabel ini.
- Jangan ubah manual tanpa proses bisnis resmi.

---

## 6. Enum yang Direkomendasikan

### student_type
- `regular`
- `boarding`

### fee category
- `spp`
- `activity`
- `meal`
- `other`

### billing_frequency
- `monthly`
- `one_time`
- `custom`

### invoice status
- `draft`
- `unpaid`
- `partial`
- `paid`
- `void`

### payment method
- `cash`
- `bank_transfer`

### payment / expense / ledger status
- `posted`
- `edited`
- `void`

### ledger direction
- `in`
- `out`

### billing cycle status
- `open`
- `closed`

---

## 7. Index yang Penting
- `students(nis)`
- `students(nisn)`
- `students(full_name)`
- `students(batch_id, class_id, major_id, student_type, is_active)`
- `invoices(student_id, status)`
- `invoices(fee_type_id, billing_cycle_id)`
- `payments(student_id, payment_date)`
- `payment_items(invoice_id)`
- `cash_ledger_entries(account_id, transaction_date, direction)`
- `audit_logs(entity_type, entity_id)`

---

## 8. Rekomendasi Urutan Migration
1. users
2. roles/permissions
3. batches
4. classes
5. majors
6. students
7. fee_types
8. fee_schemes
9. billing_cycles
10. invoices
11. cash_accounts
12. payments
13. payment_items
14. expense_categories
15. expenses
16. cash_ledger_entries
17. import_logs
18. import_log_rows
19. audit_logs

---

## 9. Contoh Skema Ringkas JSON

### student
```json
{
  "id": 1,
  "nis": "2026001",
  "nisn": "1234567890",
  "full_name": "Ahmad Fulan",
  "batch_id": 3,
  "class_id": 5,
  "major_id": 2,
  "student_type": "boarding",
  "is_active": true
}
```

### invoice
```json
{
  "invoice_no": "INV-2026-04-000123",
  "student_id": 1,
  "fee_type_id": 1,
  "billing_cycle_id": 10,
  "total_amount": 350000,
  "paid_amount": 0,
  "outstanding_amount": 350000,
  "status": "unpaid"
}
```

### payment
```json
{
  "payment_no": "PAY-2026-04-0010",
  "student_id": 1,
  "payment_date": "2026-04-15",
  "method": "cash",
  "cash_account_id": 1,
  "total_amount": 500000,
  "status": "posted"
}
```

