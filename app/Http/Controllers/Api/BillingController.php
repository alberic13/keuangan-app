<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\BillingCycle;
use App\Models\Invoice;
use App\Models\Student;
use App\Models\StudentType;
use App\Services\AuditLogService;
use App\Services\BillingService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BillingController extends Controller
{
    use ApiResponses;

    public function __construct(
        protected BillingService $billingService,
        protected AuditLogService $auditLogs,
    ) {
    }

    public function cyclesIndex()
    {
        return $this->success(BillingCycle::query()->latest('year')->latest('month')->get());
    }

    public function storeCycle(Request $request)
    {
        $this->ensureAnyRole(['admin_keuangan']);
        $data = $this->validateCycle($request, false);
        $data['due_date'] = $data['due_date'] ?? sprintf('%s-%02d-10', $data['year'], $data['month']);
        $billingCycle = BillingCycle::query()->create($data + ['status' => 'open']);
        $this->auditLogs->log('billing_cycle.created', $billingCycle, null, $billingCycle->toArray(), null, $request->user());

        return $this->success($billingCycle, 'Success', 201);
    }

    public function updateCycle(Request $request, BillingCycle $billingCycle)
    {
        $this->ensureAnyRole(['admin_keuangan']);
        $data = $this->validateCycle($request, true);
        $before = $billingCycle->toArray();
        $billingCycle->update($data);
        $this->auditLogs->log('billing_cycle.updated', $billingCycle, $before, $billingCycle->fresh()->toArray(), null, $request->user());

        return $this->success($billingCycle);
    }

    public function closeCycle(Request $request, BillingCycle $billingCycle)
    {
        $this->ensureAnyRole(['admin_keuangan']);
        $before = $billingCycle->toArray();
        $billingCycle->update(['status' => 'closed']);
        $this->auditLogs->log('billing_cycle.closed', $billingCycle, $before, $billingCycle->fresh()->toArray(), 'Close cycle', $request->user());

        return $this->success($billingCycle);
    }

    public function generate(Request $request)
    {
        $this->ensureAnyRole(['admin_keuangan']);
        $data = $request->validate([
            'fee_type_id' => ['required', 'exists:fee_types,id'],
            'billing_cycle_id' => ['required', 'exists:billing_cycles,id'],
            'reference_name' => ['nullable', 'string', 'max:255'],
            'filters.batch_id' => ['nullable', 'exists:batches,id'],
            'filters.class_id' => ['nullable', 'exists:classes,id'],
            'filters.student_type' => ['nullable', Rule::in(array_merge(['all'], StudentType::activeSlugs()))],
        ]);

        return $this->success($this->billingService->generate($data, $request->user()), 'Success');
    }

    public function invoicesIndex(Request $request)
    {
        $invoices = Invoice::query()->with(['student.batch', 'student.classRoom', 'feeType', 'billingCycle'])
            ->when($request->filled('student_id'), fn ($q) => $q->where('student_id', $request->integer('student_id')))
            ->when($request->filled('fee_type_id'), fn ($q) => $q->where('fee_type_id', $request->integer('fee_type_id')))
            ->when($request->filled('billing_cycle_id'), fn ($q) => $q->where('billing_cycle_id', $request->integer('billing_cycle_id')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest()->paginate($request->integer('per_page', 15));

        return $this->success($invoices);
    }

    public function showInvoice(Invoice $invoice)
    {
        return $this->success($invoice->load(['student.batch', 'student.classRoom', 'feeType', 'billingCycle', 'paymentItems.payment']));
    }

    public function openByStudent(Student $student)
    {
        return $this->success($student->invoices()->with(['feeType', 'billingCycle'])
            ->whereIn('status', ['unpaid', 'partial'])->orderBy('billing_cycle_id')->get());
    }

    public function voidInvoice(Request $request, Invoice $invoice)
    {
        $this->ensureAnyRole(['admin_keuangan']);

        return $this->success($this->billingService->voidInvoice($invoice, $request->user()));
    }

    protected function validateCycle(Request $request, bool $isUpdate): array
    {
        return $request->validate([
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'min:2020'],
            'period_label' => ['required', 'string', 'max:255'],
            'due_date' => [$isUpdate ? 'required' : 'nullable', 'date'],
            'status' => [$isUpdate ? 'required' : 'nullable', Rule::in(['open', 'closed'])],
        ]);
    }
}
