<?php

namespace App\Services\Payments;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentItem;
use App\Models\Student;
use Illuminate\Validation\ValidationException;

class PaymentItemProcessor
{
    /**
     * @param array<int, array{invoice_id: int, amount: int}> $items
     * @return array{0: int, 1: array<int, int>}
     */
    public function sync(Payment $payment, Student $student, array $items): array
    {
        if ($items === []) {
            throw ValidationException::withMessages([
                'items' => 'Pilih minimal satu invoice.',
            ]);
        }

        $total = 0;
        $invoiceIds = [];

        foreach ($items as $item) {
            $invoice = Invoice::query()
                ->with('feeType')
                ->lockForUpdate()
                ->findOrFail($item['invoice_id']);

            $this->validateInvoice($invoice, $student, (int) ($item['amount'] ?? 0));

            $amount = (int) $item['amount'];
            PaymentItem::query()->create([
                'payment_id' => $payment->id,
                'invoice_id' => $invoice->id,
                'amount' => $amount,
            ]);

            $total += $amount;
            $invoiceIds[] = $invoice->id;
        }

        return [$total, $invoiceIds];
    }

    protected function validateInvoice(Invoice $invoice, Student $student, int $amount): void
    {
        if ((int) $invoice->student_id !== (int) $student->id) {
            throw ValidationException::withMessages(['items' => 'Invoice tidak sesuai dengan siswa yang dipilih.']);
        }

        if (in_array($invoice->status, ['paid', 'void'], true) || $invoice->outstanding_amount <= 0) {
            throw ValidationException::withMessages(['items' => 'Invoice sudah lunas atau tidak valid.']);
        }

        if ($amount <= 0) {
            throw ValidationException::withMessages(['items' => 'Nominal harus lebih dari nol.']);
        }

        if ($amount > (int) $invoice->outstanding_amount) {
            throw ValidationException::withMessages(['items' => 'Nominal melebihi outstanding.']);
        }

        if (! $invoice->feeType->installment_allowed && $amount !== (int) $invoice->outstanding_amount) {
            $message = match ($invoice->feeType->category) {
                'spp' => 'SPP tidak boleh dibayar parsial.',
                'meal' => 'Uang makan harus dibayar penuh per invoice.',
                default => 'Invoice ini harus dibayar penuh.',
            };

            throw ValidationException::withMessages(['items' => $message]);
        }
    }
}
