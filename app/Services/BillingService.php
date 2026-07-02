<?php

namespace App\Services;

use App\Models\BillingCycle;
use App\Models\FeeScheme;
use App\Models\FeeType;
use App\Models\Invoice;
use App\Models\Student;
use App\Models\User;
use App\Support\DocumentNumber;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BillingService
{
    public function __construct(
        protected AuditLogService $auditLogs,
    ) {
    }

    public function generate(array $attributes, User $actor): array
    {
        $feeType = FeeType::query()->findOrFail($attributes['fee_type_id']);
        $billingCycle = BillingCycle::query()->findOrFail($attributes['billing_cycle_id']);

        if ($billingCycle->status !== 'open') {
            throw ValidationException::withMessages([
                'billing_cycle_id' => 'Billing cycle sudah ditutup.',
            ]);
        }

        $referenceName = $attributes['reference_name'] ?: null;
        $students = $this->studentQuery($feeType, $attributes['filters'] ?? [])->get();

        $result = [
            'generated' => 0,
            'skipped' => 0,
            'failed' => 0,
            'items' => [],
        ];

        DB::transaction(function () use ($students, $feeType, $billingCycle, $referenceName, $actor, &$result) {
            foreach ($students as $student) {
                $scheme = $this->findSchemeForStudent($student, $feeType, $billingCycle->due_date);

                if (! $scheme) {
                    $result['failed']++;
                    $result['items'][] = [
                        'student_id' => $student->id,
                        'student_name' => $student->full_name,
                        'status' => 'failed',
                        'message' => 'Tarif aktif tidak ditemukan.',
                    ];

                    continue;
                }

                if ($this->invoiceExists($student, $feeType, $billingCycle, $referenceName)) {
                    $result['skipped']++;
                    $result['items'][] = [
                        'student_id' => $student->id,
                        'student_name' => $student->full_name,
                        'status' => 'skipped',
                        'message' => 'Invoice sudah ada untuk periode ini.',
                    ];

                    continue;
                }

                $invoice = Invoice::query()->create([
                    'invoice_no' => DocumentNumber::next('INV', Invoice::class, 'invoice_no', $billingCycle->due_date),
                    'student_id' => $student->id,
                    'fee_type_id' => $feeType->id,
                    'billing_cycle_id' => $billingCycle->id,
                    'reference_name' => $referenceName,
                    'total_amount' => $scheme->nominal,
                    'paid_amount' => 0,
                    'outstanding_amount' => $scheme->nominal,
                    'status' => 'unpaid',
                    'published_at' => now(),
                    'created_by' => $actor->id,
                    'updated_by' => $actor->id,
                ]);

                $this->auditLogs->log('invoice.created', $invoice, null, $invoice->toArray(), null, $actor);

                $result['generated']++;
                $result['items'][] = [
                    'student_id' => $student->id,
                    'student_name' => $student->full_name,
                    'invoice_id' => $invoice->id,
                    'invoice_no' => $invoice->invoice_no,
                    'status' => 'generated',
                ];
            }
        });

        return $result;
    }

    public function recalculateInvoice(Invoice $invoice): Invoice
    {
        $invoice->loadMissing('feeType');

        if ($invoice->status === 'void') {
            return $invoice;
        }

        $paidAmount = (int) $invoice->paymentItems()->sum('amount');
        $outstandingAmount = max((int) $invoice->total_amount - $paidAmount, 0);

        $status = match (true) {
            $paidAmount <= 0 => 'unpaid',
            $outstandingAmount <= 0 => 'paid',
            default => 'partial',
        };

        $invoice->forceFill([
            'paid_amount' => $paidAmount,
            'outstanding_amount' => $outstandingAmount,
            'status' => $status,
        ])->save();

        return $invoice->refresh();
    }

    public function voidInvoice(Invoice $invoice, User $actor): Invoice
    {
        if ($invoice->paymentItems()->exists()) {
            throw ValidationException::withMessages([
                'invoice' => 'Invoice yang sudah memiliki pembayaran tidak bisa di-void.',
            ]);
        }

        $before = $invoice->toArray();
        $invoice->update([
            'status' => 'void',
            'updated_by' => $actor->id,
        ]);

        $this->auditLogs->log('invoice.voided', $invoice, $before, $invoice->fresh()->toArray(), 'Void invoice', $actor);

        return $invoice->refresh();
    }

    public function findSchemeForStudent(Student $student, FeeType $feeType, CarbonInterface|string $date): ?FeeScheme
    {
        $query = FeeScheme::query()
            ->where('fee_type_id', $feeType->id)
            ->where('is_active', true)
            ->whereDate('effective_start', '<=', $date)
            ->where(function (Builder $builder) use ($date) {
                $builder->whereNull('effective_end')
                    ->orWhereDate('effective_end', '>=', $date);
            })
            ->orderByDesc('effective_start');

        return (clone $query)->where('batch_id', $student->batch_id)->first()
            ?? (clone $query)->whereNull('batch_id')->first();
    }

    protected function studentQuery(FeeType $feeType, array $filters): Builder
    {
        $query = Student::query()
            ->where('is_active', true)
            ->with(['batch', 'classRoom']);

        if (! empty($filters['batch_id'])) {
            $query->where('batch_id', $filters['batch_id']);
        }

        if (! empty($filters['class_id'])) {
            $query->where('class_id', $filters['class_id']);
        }



        if (($filters['student_type'] ?? 'all') !== 'all') {
            $query->where('student_type', $filters['student_type']);
        }

        if ($feeType->applies_to !== 'all') {
            $query->where('student_type', $feeType->applies_to);
        }

        if ($feeType->category === 'meal') {
            $query->where('student_type', 'boarding');
        }

        return $query;
    }

    protected function invoiceExists(Student $student, FeeType $feeType, BillingCycle $billingCycle, ?string $referenceName): bool
    {
        return Invoice::query()
            ->where('student_id', $student->id)
            ->where('fee_type_id', $feeType->id)
            ->where('billing_cycle_id', $billingCycle->id)
            ->when($referenceName, fn (Builder $query) => $query->where('reference_name', $referenceName), fn (Builder $query) => $query->whereNull('reference_name'))
            ->exists();
    }
}
