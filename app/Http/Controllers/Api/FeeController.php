<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ApiResponses;
use App\Http\Controllers\Controller;
use App\Models\FeeScheme;
use App\Models\FeeType;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class FeeController extends Controller
{
    use ApiResponses;

    public function __construct(
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
        $feeType = FeeType::query()->create($this->normalizeFeeType($data, $request));
        $this->auditLogs->log('fee_type.created', $feeType, null, $feeType->toArray(), null, $request->user());

        return $this->success($feeType, 'Success', 201);
    }

    public function updateFeeType(Request $request, FeeType $feeType)
    {
        $this->ensureAnyRole(['admin_keuangan']);
        $before = $feeType->toArray();
        $feeType->update($this->normalizeFeeType($this->validatedFeeType($request, $feeType), $request));
        $this->auditLogs->log('fee_type.updated', $feeType, $before, $feeType->fresh()->toArray(), null, $request->user());

        return $this->success($feeType);
    }

    public function feeSchemesIndex(Request $request)
    {
        $schemes = FeeScheme::query()
            ->with(['feeType', 'batch'])
            ->when($request->filled('fee_type_id'), fn ($query) => $query->where('fee_type_id', $request->integer('fee_type_id')))
            ->when($request->filled('batch_id'), fn ($query) => $query->where('batch_id', $request->integer('batch_id')))
            ->when($request->boolean('active_only'), fn ($query) => $query->where('is_active', true))
            ->latest('effective_start')
            ->get();

        return $this->success($schemes);
    }

    public function storeFeeScheme(Request $request)
    {
        $this->ensureAnyRole(['admin_keuangan']);
        $data = $this->validatedFeeScheme($request);
        $this->ensureSchemeDoesNotOverlap($data);
        $scheme = FeeScheme::query()->create($data);
        $this->auditLogs->log('fee_scheme.created', $scheme, null, $scheme->toArray(), null, $request->user());

        return $this->success($scheme, 'Success', 201);
    }

    public function updateFeeScheme(Request $request, FeeScheme $feeScheme)
    {
        $this->ensureAnyRole(['admin_keuangan']);
        $data = $this->validatedFeeScheme($request);
        $this->ensureSchemeDoesNotOverlap($data, $feeScheme);
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
            'applies_to' => ['required', Rule::in(['all', 'regular', 'boarding'])],
        ]);
    }

    protected function normalizeFeeType(array $data, Request $request): array
    {
        return match ($data['category']) {
            'spp' => array_merge($data, ['installment_allowed' => false, 'billing_frequency' => 'monthly', 'applies_to' => 'all', 'is_active' => true]),
            'meal' => array_merge($data, ['installment_allowed' => false, 'billing_frequency' => 'monthly', 'applies_to' => 'boarding', 'is_active' => true]),
            'activity' => array_merge($data, ['installment_allowed' => true, 'is_active' => true]),
            default => array_merge($data, ['installment_allowed' => $request->boolean('installment_allowed'), 'is_active' => true]),
        };
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

    protected function ensureSchemeDoesNotOverlap(array $data, ?FeeScheme $feeScheme = null): void
    {
        $overlapExists = FeeScheme::query()
            ->where('fee_type_id', $data['fee_type_id'])
            ->where('batch_id', $data['batch_id'] ?? null)
            ->when($feeScheme, fn ($query) => $query->whereKeyNot($feeScheme->id))
            ->where(function ($query) use ($data) {
                $query->whereNull('effective_end')
                    ->orWhereDate('effective_end', '>=', $data['effective_start']);
            })
            ->where(function ($query) use ($data) {
                if (! empty($data['effective_end'])) {
                    $query->whereDate('effective_start', '<=', $data['effective_end']);
                }
            })
            ->exists();

        if ($overlapExists) {
            throw ValidationException::withMessages([
                'effective_start' => 'Tarif aktif overlap dengan periode yang sudah ada.',
            ]);
        }
    }
}
