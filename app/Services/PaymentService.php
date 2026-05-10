<?php

namespace App\Services;

use App\Models\CashAccount;
use App\Models\CashLedgerEntry;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PaymentItem;
use App\Models\Student;
use App\Models\User;
use App\Support\DocumentNumber;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public function __construct(
        protected BillingService $billingService,
        protected AuditLogService $auditLogs,
        protected GoogleDrivePaymentProofService $paymentProofs,
    ) {
    }

    public function create(array $attributes, User $actor): Payment
    {
        $uploadedProof = null;

        try {
            return DB::transaction(function () use ($attributes, $actor, &$uploadedProof) {
                $student = Student::query()->findOrFail($attributes['student_id']);
                $account = $this->activeAccount($attributes['cash_account_id']);

                $payment = Payment::query()->create([
                    'payment_no' => DocumentNumber::next('PAY', Payment::class, 'payment_no', $attributes['payment_date']),
                    'student_id' => $student->id,
                    'payment_date' => $attributes['payment_date'],
                    'method' => $attributes['method'],
                    'cash_account_id' => $account->id,
                    'total_amount' => 0,
                    'bank_reference' => $attributes['bank_reference'] ?? null,
                    'notes' => $attributes['notes'] ?? null,
                    'status' => 'posted',
                    'created_by' => $actor->id,
                ]);

                [$total, $invoiceIds] = $this->syncItems($payment, $student, $attributes['items'] ?? []);

                if (! empty($attributes['payment_proof'])) {
                    $uploadedProof = $this->paymentProofs->upload(
                        $attributes['payment_proof'],
                        $payment->payment_no,
                        $student->full_name
                    );
                }

                $payment->update(array_merge([
                    'total_amount' => $total,
                ], $uploadedProof ?? []));

                $this->recalculateInvoices($invoiceIds);
                $this->syncLedger($payment, $actor);
                $this->auditLogs->log('payment.created', $payment, null, $payment->load('items')->toArray(), null, $actor);

                return $payment->fresh(['student', 'cashAccount', 'items.invoice']);
            });
        } catch (\Throwable $exception) {
            if ($uploadedProof) {
                $this->paymentProofs->delete($uploadedProof['payment_proof_drive_id'] ?? null);
            }

            throw $exception;
        }
    }

    public function update(Payment $payment, array $attributes, User $actor): Payment
    {
        if (empty($attributes['edited_reason'])) {
            throw ValidationException::withMessages([
                'edited_reason' => 'Alasan edit wajib diisi.',
            ]);
        }

        $uploadedProof = null;
        $previousProofId = $payment->payment_proof_drive_id;

        try {
            $updatedPayment = DB::transaction(function () use ($payment, $attributes, $actor, &$uploadedProof) {
                $before = $payment->load('items.invoice')->toArray();
                $student = Student::query()->findOrFail($attributes['student_id'] ?? $payment->student_id);
                $account = $this->activeAccount($attributes['cash_account_id'] ?? $payment->cash_account_id);
                $oldInvoiceIds = $payment->items()->pluck('invoice_id')->all();

                $payment->items()->delete();
                $this->recalculateInvoices($oldInvoiceIds);

                [$total, $newInvoiceIds] = $this->syncItems($payment, $student, $attributes['items'] ?? []);

                if (! empty($attributes['payment_proof'])) {
                    $uploadedProof = $this->paymentProofs->upload(
                        $attributes['payment_proof'],
                        $payment->payment_no,
                        $student->full_name
                    );
                }

                $payment->update(array_merge([
                    'student_id' => $student->id,
                    'payment_date' => $attributes['payment_date'] ?? $payment->payment_date,
                    'method' => $attributes['method'] ?? $payment->method,
                    'cash_account_id' => $account->id,
                    'bank_reference' => $attributes['bank_reference'] ?? $payment->bank_reference,
                    'notes' => $attributes['notes'] ?? null,
                    'status' => 'edited',
                    'edited_by' => $actor->id,
                    'edited_reason' => $attributes['edited_reason'],
                    'total_amount' => $total,
                ], $uploadedProof ?? []));

                $this->recalculateInvoices(array_values(array_unique([...$oldInvoiceIds, ...$newInvoiceIds])));
                $this->syncLedger($payment, $actor);
                $this->auditLogs->log(
                    'payment.updated',
                    $payment,
                    $before,
                    $payment->fresh(['items.invoice'])->toArray(),
                    $attributes['edited_reason'],
                    $actor,
                );

                return $payment->fresh(['student', 'cashAccount', 'items.invoice']);
            });

            if ($uploadedProof && $previousProofId) {
                $this->paymentProofs->delete($previousProofId);
            }

            return $updatedPayment;
        } catch (\Throwable $exception) {
            if ($uploadedProof) {
                $this->paymentProofs->delete($uploadedProof['payment_proof_drive_id'] ?? null);
            }

            throw $exception;
        }
    }

    protected function syncItems(Payment $payment, Student $student, array $items): array
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

            if ((int) $invoice->student_id !== (int) $student->id) {
                throw ValidationException::withMessages([
                    'items' => 'Invoice tidak sesuai dengan siswa yang dipilih.',
                ]);
            }

            if (in_array($invoice->status, ['paid', 'void'], true) || $invoice->outstanding_amount <= 0) {
                throw ValidationException::withMessages([
                    'items' => 'Invoice sudah lunas atau tidak valid.',
                ]);
            }

            $amount = (int) ($item['amount'] ?? 0);
            if ($amount <= 0) {
                throw ValidationException::withMessages([
                    'items' => 'Nominal harus lebih dari nol.',
                ]);
            }

            if ($amount > (int) $invoice->outstanding_amount) {
                throw ValidationException::withMessages([
                    'items' => 'Nominal melebihi outstanding.',
                ]);
            }

            if (! $invoice->feeType->installment_allowed && $amount !== (int) $invoice->outstanding_amount) {
                $message = $invoice->feeType->category === 'spp'
                    ? 'SPP tidak boleh dibayar parsial.'
                    : ($invoice->feeType->category === 'meal'
                        ? 'Uang makan harus dibayar penuh per invoice.'
                        : 'Invoice ini harus dibayar penuh.');

                throw ValidationException::withMessages([
                    'items' => $message,
                ]);
            }

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

    protected function syncLedger(Payment $payment, User $actor): void
    {
        CashLedgerEntry::query()
            ->where('source_type', 'payment')
            ->where('source_id', $payment->id)
            ->delete();

        CashLedgerEntry::query()->create([
            'entry_no' => DocumentNumber::next('LED', CashLedgerEntry::class, 'entry_no', $payment->payment_date),
            'transaction_date' => $payment->payment_date,
            'account_id' => $payment->cash_account_id,
            'direction' => 'in',
            'source_type' => 'payment',
            'source_id' => $payment->id,
            'amount' => $payment->total_amount,
            'description' => 'Penerimaan '.$payment->payment_no.' - '.$payment->student->full_name,
            'status' => 'posted',
            'created_by' => $actor->id,
        ]);
    }

    protected function recalculateInvoices(array $invoiceIds): void
    {
        foreach (array_unique($invoiceIds) as $invoiceId) {
            $invoice = Invoice::query()->find($invoiceId);
            if ($invoice) {
                $this->billingService->recalculateInvoice($invoice);
            }
        }
    }

    protected function activeAccount(int $accountId): CashAccount
    {
        $account = CashAccount::query()->findOrFail($accountId);

        if (! $account->is_active) {
            throw ValidationException::withMessages([
                'cash_account_id' => 'Akun kas/bank tidak aktif.',
            ]);
        }

        return $account;
    }
}
