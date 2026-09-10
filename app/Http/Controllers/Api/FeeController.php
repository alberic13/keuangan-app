<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\FeeScheme;
use App\Models\FeeType;
use App\Services\AuditLogService;
use App\Services\FeeService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FeeController extends Controller
{
    use ApiResponses;

    public function __construct(
        protected FeeService $feeService,
        protected AuditLogService $auditLogs,
    ) {
    }

    public function feeTypesIndex()
    {
        return $this->success(FeeType::query()->orderBy('name')->get());
    }

    public function storeFeeType(Request $request)
    {
        $this->ensureAnyRole(['admin_keuangan']);
        $data = $this->validatedFeeType($request);
        $feeType = FeeType::query()->create($this->feeService->normalizeFeeType($data, $request->boolean('installment_allowed')));
        $this->auditLogs->log('fee_type.created', $feeType, null, $feeType->toArray(), null, $request->user());

        return $this->success($feeType, 'Success', 201);
    }

    public function updateFeeType(Request $request, FeeType $feeType)
    {
        $this->ensureAnyRole(['admin_keuangan']);
        $before = $feeType->toArray();
        $feeType->update($this->feeService->normalizeFeeType($this->validatedFeeType($request, $feeType), $request->boolean('installment_allowed')));
        $this->auditLogs->log('fee_type.updated', $feeType, $before, $feeType->fresh()->toArray(), null, $request->user());

        return $this->success($feeType);
    }

    public function feeSchemesIndex(Request $request)
    {
        $schemes = FeeScheme::query()
            ->with('feeType')
            ->where('is_active', true)
            ->when($request->filled('fee_type_id'), fn ($q) => $q->where('fee_type_id', $request->integer('fee_type_id')))
            ->when($request->filled('batch_id'), fn ($q) => $q->where('batch_id', $request->integer('batch_id')))
            ->latest('effective_start')
            ->get();

        return $this->success($schemes);
    }

    public function storeFeeScheme(Request $request)
    {
        $this->ensureAnyRole(['admin_keuangan']);
        $data = $this->validatedFeeScheme($request);
        $this->feeService->ensureSchemeDoesNotOverlap($data);
        $scheme = FeeScheme::query()->create($data);
        $this->auditLogs->log('fee_scheme.created', $scheme, null, $scheme->toArray(), null, $request->user());

        return $this->success($scheme, 'Success', 201);
    }

    public function updateFeeScheme(Request $request, FeeScheme $feeScheme)
    {
        $this->ensureAnyRole(['admin_keuangan']);
        $data = $this->validatedFeeScheme($request);
        $this->feeService->ensureSchemeDoesNotOverlap($data, $feeScheme);
        $before = $feeScheme->toArray();
        $feeScheme->update($data);
        $this->auditLogs->log('fee_scheme.updated', $feeScheme, $before, $feeScheme->fresh()->toArray(), null, $request->user());

        return $this->success($feeScheme);
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
