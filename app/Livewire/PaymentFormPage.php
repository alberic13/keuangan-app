<?php

namespace App\Livewire;

use App\Models\CashAccount;
use App\Models\Invoice;
use App\Models\Student;
use Illuminate\Support\Collection;
use Livewire\Component;

class PaymentFormPage extends Component
{
    public function render()
    {
        $selectedStudent = request('student_id')
            ? Student::query()
                ->with(['batch', 'classRoom'])
                ->find(request('student_id'))
            : null;

        return view('livewire.payment-form-page', [
            'selectedStudent' => $selectedStudent,
            'invoiceOptions' => $this->buildInvoiceOptions($selectedStudent),
            'accounts' => CashAccount::query()->where('is_active', true)->orderBy('name')->get(),
        ])->layout('layouts.app', [
            'pageTitle' => 'Form Pembayaran',
            'pageHeading' => 'Form Pembayaran',
            'activeNav' => 'payments',
            'searchPlaceholder' => 'Cari siswa, nomor bukti, akun, atau catatan...',
        ]);
    }

    protected function buildInvoiceOptions(?Student $selectedStudent): Collection
    {
        if (! $selectedStudent) {
            return collect();
        }

        return $selectedStudent->invoices()
            ->with(['feeType', 'billingCycle'])
            ->whereIn('status', ['unpaid', 'partial'])
            ->get()
            ->sortByDesc(fn (Invoice $invoice) => [
                $invoice->billingCycle?->due_date?->timestamp ?? 0,
                $invoice->id,
            ])
            ->values();
    }
}
