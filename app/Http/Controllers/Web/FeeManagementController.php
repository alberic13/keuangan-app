<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\FeeScheme;
use App\Models\FeeType;
use App\Services\AuditLogService;
use App\Services\FeeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FeeManagementController extends Controller
{
    public function __construct(
        protected FeeService $feeService,
        protected AuditLogService $auditLogs,
    ) {
    }

    public function storeFeeType(Request $request): RedirectResponse
    {
        $this->ensureAnyRole(['admin_keuangan']);
        $data = $this->validatedFeeType($request);
        $feeType = FeeType::query()->create($this->feeService->normalizeFeeType($data, $request->boolean('installment_allowed')));
        $this->auditLogs->log('fee_type.created', $feeType, null, $feeType->toArray(), null, $request->user());

        return $this->redirectBackWithMessage($request, 'Jenis biaya berhasil ditambahkan.');
    }

    public function updateFeeType(Request $request, FeeType $feeType): RedirectResponse
    {
        $this->ensureAnyRole(['admin_keuangan']);
        $data = $this->validatedFeeType($request, $feeType);
        $before = $feeType->toArray();
        $feeType->update($this->feeService->normalizeFeeType($data, $request->boolean('installment_allowed')));
        $this->auditLogs->log('fee_type.updated', $feeType, $before, $feeType->fresh()->toArray(), null, $request->user());

        return $this->redirectBackWithMessage($request, 'Jenis biaya berhasil diperbarui.');
    }

    public function storeFeeScheme(Request $request): RedirectResponse
    {
        $this->ensureAnyRole(['admin_keuangan']);
        $data = $this->validatedFeeScheme($request);
        $this->feeService->ensureSchemeDoesNotOverlap($data);
        $scheme = FeeScheme::query()->create($data);
        $this->auditLogs->log('fee_scheme.created', $scheme, null, $scheme->toArray(), null, $request->user());

        return $this->redirectBackWithMessage($request, 'Tarif berhasil ditambahkan.');
    }

    public function updateFeeScheme(Request $request, FeeScheme $feeScheme): RedirectResponse
    {
        $this->ensureAnyRole(['admin_keuangan']);
        $data = $this->validatedFeeScheme($request);
        $this->feeService->ensureSchemeDoesNotOverlap($data, $feeScheme);
        $before = $feeScheme->toArray();
        $feeScheme->update($data);
        $this->auditLogs->log('fee_scheme.updated', $feeScheme, $before, $feeScheme->fresh()->toArray(), null, $request->user());

        return $this->redirectBackWithMessage($request, 'Tarif berhasil diperbarui.');
    }

    public function destroyFeeType(Request $request, FeeType $feeType): RedirectResponse
    {
        $this->ensureAnyRole(['admin_keuangan']);
        $before = $feeType->toArray();
        $this->feeService->deleteFeeType($feeType);
        $this->auditLogs->log('fee_type.deleted', $feeType, $before, null, null, $request->user());

        return $this->redirectBackWithMessage($request, 'Jenis biaya berhasil dihapus.');
    }

    public function destroyFeeScheme(Request $request, FeeScheme $feeScheme): RedirectResponse
    {
        $this->ensureAnyRole(['admin_keuangan']);
        $before = $feeScheme->toArray();
        $this->feeService->deleteFeeScheme($feeScheme);
        $this->auditLogs->log('fee_scheme.deleted', $feeScheme, $before, null, null, $request->user());

        return $this->redirectBackWithMessage($request, 'Tarif berhasil dihapus.');
    }

    protected function validatedFeeType(Request $request, ?FeeType $feeType = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:30', Rule::unique('fee_types', 'code')->ignore($feeType?->id)],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::in(['spp', 'activity', 'meal', 'other'])],
            'billing_frequency' => ['required', Rule::in(['monthly', 'one_time', 'custom'])],
            'applies_to' => ['required', Rule::in(['all', 'regular', 'full_day', 'boarding'])],
        ]);
    }

    protected function validatedFeeScheme(Request $request): array
    {
        return $request->validate([
            'fee_type_id' => ['required', 'exists:fee_types,id'],
            'batch_id' => ['nullable', 'exists:batches,id'],
            'nominal' => ['required', 'integer', 'min:1'],
            'effective_start' => ['required', 'date'],
            'effective_end' => ['nullable', 'date', 'after_or_equal:effective_start'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }
}
