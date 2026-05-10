# API Spec - E-Keuangan MAN 2 Surakarta

**Versi:** 1.0  
**Tanggal:** 15 April 2026  
**Gaya API:** REST-style JSON endpoints untuk integrasi frontend internal Laravel / Livewire / komponen async.

---

## 1. Konvensi Umum

### Base path
```text
/api
```

### Format response sukses
```json
{
  "message": "Success",
  "data": {}
}
```

### Format response error
```json
{
  "message": "Validation failed",
  "errors": {
    "field": ["error message"]
  }
}
```

### Auth
- Session-based auth untuk aplikasi web.
- Untuk endpoint async internal, tetap gunakan middleware auth + authorization policy.

### Header umum
```http
Accept: application/json
Content-Type: application/json
```

Untuk upload file:
```http
Content-Type: multipart/form-data
```

### Kode status umum
- `200 OK`
- `201 Created`
- `204 No Content`
- `400 Bad Request`
- `401 Unauthorized`
- `403 Forbidden`
- `404 Not Found`
- `409 Conflict`
- `422 Unprocessable Entity`
- `500 Internal Server Error`

---

## 2. Modul Auth

### POST `/api/auth/login`
**Request**
```json
{
  "login": "bendahara",
  "password": "secret123"
}
```

**Response 200**
```json
{
  "message": "Login berhasil",
  "data": {
    "user": {
      "id": 1,
      "name": "Bendahara",
      "role": "bendahara"
    }
  }
}
```

### POST `/api/auth/logout`
**Response 200**
```json
{ "message": "Logout berhasil" }
```

### GET `/api/auth/me`
Ambil profil user aktif.

---

## 3. Modul Master Referensi

### GET `/api/batches`
### POST `/api/batches`

**Request**
```json
{
  "year_label": "2026",
  "academic_year": "2026/2027",
  "is_active": true
}
```

### GET `/api/classes`
### POST `/api/classes`
### GET `/api/majors`
### POST `/api/majors`

---

## 4. Modul Students

### GET `/api/students`
**Query params**
- `search`
- `batch_id`
- `class_id`
- `major_id`
- `student_type`
- `is_active`
- `page`
- `per_page`

### POST `/api/students`
**Request**
```json
{
  "nis": "2026001",
  "nisn": "1234567890",
  "full_name": "Ahmad Fulan",
  "class_id": 5,
  "major_id": 2,
  "batch_id": 3,
  "student_type": "boarding",
  "is_active": true
}
```

### GET `/api/students/{id}`
### PUT `/api/students/{id}`
### PATCH `/api/students/{id}/deactivate`

---

## 5. Modul Import Siswa / Workflow Upload

### GET `/api/imports/students/template`
Download template import siswa.

### POST `/api/imports/students/preview`
Upload file dan preview hasil validasi tanpa commit final.

**Request**
- multipart/form-data
- `file` = file xlsx/csv

**Response 200**
```json
{
  "message": "Preview import berhasil",
  "data": {
    "preview_token": "imp_prev_abc123",
    "summary": {
      "total_rows": 100,
      "valid_rows": 92,
      "invalid_rows": 8
    },
    "errors": [
      {
        "row_number": 4,
        "field": "student_type",
        "message": "student_type harus regular atau boarding"
      }
    ]
  }
}
```

### POST `/api/imports/students/commit`
Commit hasil import berdasarkan preview token.

**Request**
```json
{
  "preview_token": "imp_prev_abc123"
}
```

**Response 201**
```json
{
  "message": "Import siswa berhasil diproses",
  "data": {
    "import_log_id": 10,
    "total_rows": 100,
    "success_rows": 92,
    "failed_rows": 8
  }
}
```

### GET `/api/imports/students/logs`
### GET `/api/imports/students/logs/{id}`

---

## 6. Modul Fee Types & Fee Schemes

### GET `/api/fee-types`
### POST `/api/fee-types`

**Request**
```json
{
  "code": "SPP",
  "name": "SPP",
  "category": "spp",
  "installment_allowed": false,
  "billing_frequency": "monthly",
  "applies_to": "all",
  "is_active": true
}
```

### PUT `/api/fee-types/{id}`
### GET `/api/fee-schemes`

**Query params**
- `fee_type_id`
- `batch_id`
- `active_only`

### POST `/api/fee-schemes`
**Request**
```json
{
  "fee_type_id": 1,
  "batch_id": 3,
  "nominal": 350000,
  "effective_start": "2026-07-01",
  "effective_end": null,
  "is_active": true
}
```

### PUT `/api/fee-schemes/{id}`

---

## 7. Modul Billing Cycle

### GET `/api/billing-cycles`
### POST `/api/billing-cycles`

**Request**
```json
{
  "month": 4,
  "year": 2026,
  "period_label": "April 2026",
  "due_date": "2026-04-10",
  "status": "open"
}
```

### PUT `/api/billing-cycles/{id}`
### POST `/api/billing-cycles/{id}/close`

---

## 8. Modul Billing / Invoices

### POST `/api/billing/generate`
**Request**
```json
{
  "fee_type_id": 1,
  "billing_cycle_id": 10,
  "filters": {
    "batch_id": 3,
    "class_id": null,
    "major_id": null,
    "student_type": "all"
  },
  "reference_name": null
}
```

### GET `/api/invoices`
### GET `/api/invoices/{id}`
### GET `/api/students/{id}/invoices/open`
### POST `/api/invoices/{id}/void`

---

## 9. Modul Payments

### POST `/api/payments`
**Request**
```json
{
  "student_id": 1,
  "payment_date": "2026-04-15",
  "method": "cash",
  "cash_account_id": 1,
  "bank_reference": null,
  "notes": "Pembayaran April",
  "items": [
    {
      "invoice_id": 101,
      "amount": 350000
    },
    {
      "invoice_id": 205,
      "amount": 150000
    }
  ]
}
```

### GET `/api/payments`
### GET `/api/payments/{id}`

### PUT `/api/payments/{id}`
**Request**
```json
{
  "payment_date": "2026-04-16",
  "cash_account_id": 2,
  "notes": "Koreksi akun kas",
  "edited_reason": "Salah pilih akun kas",
  "items": [
    {
      "invoice_id": 101,
      "amount": 350000
    }
  ]
}
```

### POST `/api/payments/{id}/print-receipt`

---

## 10. Modul Cash Accounts

### GET `/api/cash-accounts`
### POST `/api/cash-accounts`

**Request**
```json
{
  "name": "Kas Utama",
  "type": "cash",
  "account_number": null,
  "is_active": true
}
```

### PUT `/api/cash-accounts/{id}`

---

## 11. Modul Expense Categories & Expenses

### GET `/api/expense-categories`
### POST `/api/expense-categories`
### GET `/api/expenses`

### POST `/api/expenses`
**Request**
```json
{
  "transaction_date": "2026-04-15",
  "category_id": 2,
  "payment_account_id": 1,
  "amount": 500000,
  "description": "Pembelian ATK"
}
```

### PUT `/api/expenses/{id}`

---

## 12. Modul Ledger

### GET `/api/cash-ledger`
**Query params**
- `account_id`
- `direction`
- `date_from`
- `date_to`
- `source_type`

---

## 13. Modul Reports

### GET `/api/reports/daily-cash`
### GET `/api/reports/monthly-summary`
### GET `/api/reports/yearly-summary`
### GET `/api/reports/student-ledger/{studentId}`
### GET `/api/reports/arrears`
### GET `/api/reports/bku`
### GET `/api/reports/cash-book`
### GET `/api/reports/cash-receipt-book`
### GET `/api/reports/bank-receipt-book`
### GET `/api/reports/cash-bank-receipt-book`

### GET `/api/reports/export`
**Query params**
- `type`
- `format` (`pdf`, `xlsx`)

---

## 14. Modul Dashboard

### GET `/api/dashboard/summary`
### GET `/api/dashboard/payment-trend`
### GET `/api/dashboard/recent-payments`

---

## 15. Modul Audit Logs

### GET `/api/audit-logs`
**Query params**
- `actor_id`
- `entity_type`
- `entity_id`
- `action`
- `date_from`
- `date_to`

### GET `/api/audit-logs/{id}`

---

## 16. Matrix Auth per Endpoint (Ringkas)

| Endpoint Group | Admin Keuangan | Bendahara | Kepala Madrasah | Waka | Admin TU |
|---|---:|---:|---:|---:|---:|
| Auth/me | ✅ | ✅ | ✅ | ✅ | ✅ |
| Master siswa | ✅ | ⚠️ baca/terbatas | ❌ | ❌ | ⚠️ baca |
| Import siswa | ✅ | ❌ | ❌ | ❌ | ❌ |
| Fee types/schemes | ✅ | ⚠️ baca | ❌ | ❌ | ❌ |
| Billing generate | ✅ | ⚠️ terbatas | ❌ | ❌ | ❌ |
| Payments | ✅ | ✅ | ❌ | ❌ | ❌ |
| Edit posted payment | ✅ | ✅ | ❌ | ❌ | ❌ |
| Expense | ✅ | ✅ | ❌ | ❌ | ❌ |
| Reports | ✅ | ✅ | ✅ read-only | ✅ read-only | ⚠️ sesuai kebutuhan |
| Dashboard | ✅ | ✅ | ✅ read-only | ✅ read-only | ⚠️ terbatas |
| Audit logs | ✅ | ⚠️ terbatas | ❌ | ❌ | ❌ |

---

## 17. Error Rules yang Harus Diingat Frontend

- `SPP tidak boleh dibayar parsial`
- `Nominal melebihi outstanding`
- `Invoice sudah lunas`
- `Tarif aktif tidak ditemukan`
- `File import tidak sesuai template`
- `Preview token import tidak valid atau kedaluwarsa`
- `Alasan edit wajib diisi`
- `User tidak berwenang`

---

## 18. Urutan Implementasi API yang Disarankan

### Tahap backend awal
1. Auth
2. Master referensi: batches, classes, majors
3. Students
4. Import preview + commit
5. Fee types & schemes

### Tahap transaksi inti
6. Billing cycles
7. Billing generate
8. Invoices list/detail
9. Payments create/list/detail
10. Payments update/edit posted
11. Cash accounts
12. Expense categories & expenses
13. Ledger

### Tahap laporan
14. Dashboard endpoints
15. Reports core
16. BKU baseline reports
17. Export endpoints
18. Audit logs

