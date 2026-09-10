<?php

namespace App\Services\Reports;

use App\Models\Invoice;
use App\Services\ReportService;
use Illuminate\Support\Collection;

class ReportRowNormalizer
{
    public function __construct(
        protected ReportService $reportService,
    ) {
    }

    public function normalizeCashflow(array $filters): array
    {
        $payments = $this->reportService->incomePayments($filters + ['limit' => 500]);
        $expenses = $this->reportService->expenseDetails($filters + ['limit' => 500]);
        $rows = [];

        foreach ($payments as $p) {
            $rows[] = [
                'tanggal' => $p->payment_date?->format('Y-m-d') ?? '-',
                'jenis' => 'uang_masuk',
                'no_bukti' => $p->payment_no,
                'sumber' => $p->method === 'bank_transfer' ? 'transfer' : 'tunai',
                'keterangan' => $p->student?->full_name ?? '-',
                'uang_masuk' => (int) $p->total_amount,
                'uang_keluar' => 0,
            ];
        }

        foreach ($expenses as $e) {
            $rows[] = [
                'tanggal' => $e->transaction_date?->format('Y-m-d') ?? '-',
                'jenis' => 'uang_keluar',
                'no_bukti' => $e->expense_no,
                'sumber' => $e->category?->name ?? '-',
                'keterangan' => $e->description,
                'uang_masuk' => 0,
                'uang_keluar' => (int) $e->amount,
            ];
        }

        return collect($rows)->sortBy('tanggal')->values()->all();
    }

    public function normalizeForSpreadsheet(string $type, mixed $rows): array
    {
        if ($type === 'cashflow') {
            $items = collect($rows instanceof Collection ? $rows->all() : $rows)->map(fn (array $r) => [
                'tanggal' => $r['tanggal'] ?? '-',
                'jenis' => $r['jenis'] ?? '-',
                'no_bukti' => $r['no_bukti'] ?? '-',
                'sumber' => $r['sumber'] ?? '-',
                'keterangan' => $r['keterangan'] ?? '-',
                'uang_masuk' => (int) ($r['uang_masuk'] ?? 0),
                'uang_keluar' => (int) ($r['uang_keluar'] ?? 0),
            ])->values();

            return $items->push([
                'tanggal' => 'Total Periode', 'jenis' => '', 'no_bukti' => '', 'sumber' => '',
                'keterangan' => 'Total periode',
                'uang_masuk' => (int) $items->sum('uang_masuk'),
                'uang_keluar' => (int) $items->sum('uang_keluar'),
            ])->all();
        }

        if ($type === 'student-ledger') {
            return $this->normalizeStudentLedger($rows);
        }

        if ($type === 'arrears') {
            return collect($rows instanceof Collection ? $rows->all() : $rows)->map(fn (Invoice $inv) => [
                'invoice_no' => $inv->invoice_no,
                'nama_siswa' => $inv->student->full_name,
                'kelas' => $inv->student->classRoom->name ?? '-',
                'jurusan' => '',
                'jenis_biaya' => $inv->feeType->name,
                'periode' => $inv->billingCycle?->period_label ?? '-',
                'status' => strtoupper($inv->status),
                'outstanding' => $inv->outstanding_amount,
            ])->all();
        }

        return collect($rows instanceof Collection ? $rows->all() : $rows)->map(function ($row) {
            if (is_array($row) || (is_object($row) && method_exists($row, 'toArray'))) {
                return collect($row)->map(fn ($v) => is_scalar($v) ? $v : json_encode($v, JSON_UNESCAPED_UNICODE))->all();
            }

            return ['data' => is_scalar($row) ? $row : json_encode($row, JSON_UNESCAPED_UNICODE)];
        })->values()->all();
    }

    public function normalizeStudentLedger(mixed $rows): array
    {
        if (! is_array($rows) || ! isset($rows['student'])) {
            return [];
        }

        $s = $rows['student'];
        $res = [[
            'bagian' => 'Profil', 'dokumen' => $s->full_name, 'tanggal' => $s->enrollment_date?->format('Y-m-d') ?? '-',
            'keterangan' => trim(($s->classRoom->name ?? '-').' / '.($s->batch->academic_year ?? '-')),
            'status' => ucfirst($s->student_type), 'nominal' => '', 'saldo' => '',
        ]];

        foreach ($rows['invoices'] as $inv) {
            $res[] = [
                'bagian' => 'Invoice', 'dokumen' => $inv->invoice_no, 'tanggal' => $inv->billingCycle?->due_date?->format('Y-m-d') ?? '-',
                'keterangan' => trim($inv->feeType->name.' '.($inv->reference_name ? '- '.$inv->reference_name : '')),
                'status' => strtoupper($inv->status), 'nominal' => $inv->total_amount, 'saldo' => $inv->outstanding_amount,
            ];
        }

        foreach ($rows['payments'] as $pay) {
            $res[] = [
                'bagian' => 'Pembayaran', 'dokumen' => $pay->payment_no, 'tanggal' => $pay->payment_date?->format('Y-m-d') ?? '-',
                'keterangan' => $pay->items->map(fn ($i) => $i->invoice?->invoice_no)->filter()->join(', '),
                'status' => strtoupper($pay->status), 'nominal' => $pay->total_amount, 'saldo' => 0,
            ];
        }

        return $res;
    }
}
