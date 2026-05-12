<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\BillingCycle;
use App\Models\Invoice;
use App\Models\StudentType;
use App\Services\AuditLogService;
use App\Services\BillingService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BillingManagementController extends Controller
{
    public function __construct(
        protected BillingService $billingService,
        protected AuditLogService $auditLogs,
    ) {
    }

    public function storeCycle(Request $request): RedirectResponse
    {
        $this->ensureAnyRole(['admin_keuangan']);

        $data = $request->validate([
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'min:2020'],
            'period_label' => ['required', 'string', 'max:255'],
            'due_date' => ['nullable', 'date'],
        ]);

        $billingCycle = BillingCycle::query()->create([
            'month' => $data['month'],
            'year' => $data['year'],
            'period_label' => $data['period_label'],
            'due_date' => $data['due_date'] ?? sprintf('%s-%02d-10', $data['year'], $data['month']),
            'status' => 'open',
        ]);

        $this->auditLogs->log('billing_cycle.created', $billingCycle, null, $billingCycle->toArray(), null, $request->user());

        return $this->redirectBackWithMessage($request, 'Billing cycle berhasil dibuat.');
    }

    public function updateCycle(Request $request, BillingCycle $billingCycle): RedirectResponse
    {
        $this->ensureAnyRole(['admin_keuangan']);

        $data = $request->validate([
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'min:2020'],
            'period_label' => ['required', 'string', 'max:255'],
            'due_date' => ['required', 'date'],
            'status' => ['required', Rule::in(['open', 'closed'])],
        ]);

        $before = $billingCycle->toArray();
        $billingCycle->update($data);
        $this->auditLogs->log('billing_cycle.updated', $billingCycle, $before, $billingCycle->fresh()->toArray(), null, $request->user());

        return $this->redirectBackWithMessage($request, 'Billing cycle berhasil diperbarui.');
    }

    public function closeCycle(Request $request, BillingCycle $billingCycle): RedirectResponse
    {
        $this->ensureAnyRole(['admin_keuangan']);

        $before = $billingCycle->toArray();
        $billingCycle->update(['status' => 'closed']);
        $this->auditLogs->log('billing_cycle.closed', $billingCycle, $before, $billingCycle->fresh()->toArray(), 'Close cycle', $request->user());

        return $this->redirectBackWithMessage($request, 'Billing cycle berhasil ditutup.');
    }

    public function generate(Request $request): RedirectResponse
    {
        $this->ensureAnyRole(['admin_keuangan']);

        $data = $request->validate([
            'fee_type_id' => ['required', 'exists:fee_types,id'],
            'billing_cycle_id' => ['required', 'exists:billing_cycles,id'],
            'reference_name' => ['nullable', 'string', 'max:255'],
            'filters.batch_id' => ['nullable', 'exists:batches,id'],
            'filters.class_id' => ['nullable', 'exists:classes,id'],
            'filters.major_id' => ['nullable', Rule::exists('majors', 'id')->where(fn (Builder $query) => $query->where('is_active', true))],
            'filters.student_type' => ['nullable', Rule::in(array_merge(['all'], StudentType::activeSlugs()))],
        ]);

        $result = $this->billingService->generate($data, $request->user());

        return back()->with('status', sprintf(
            'Generate selesai. Generated: %d, Skipped: %d, Failed: %d.',
            $result['generated'],
            $result['skipped'],
            $result['failed'],
        ));
    }

    public function voidInvoice(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->ensureAnyRole(['admin_keuangan']);
        $this->billingService->voidInvoice($invoice, $request->user());

        return $this->redirectBackWithMessage($request, 'Invoice berhasil di-void.');
    }

    public function printInvoice(Invoice $invoice)
    {
        $invoice->load([
            'student.batch',
            'student.classRoom',
            'student.major',
            'feeType',
            'billingCycle',
            'paymentItems.payment',
        ]);

        return Pdf::loadView('prints.invoice', [
            'invoice' => $invoice,
        ])->stream($invoice->invoice_no.'.pdf');
    }
}
