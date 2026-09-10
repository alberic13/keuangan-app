<?php

namespace App\Services;

use App\Models\FeeScheme;
use App\Models\FeeType;
use App\Models\Invoice;
use Illuminate\Validation\ValidationException;

class FeeService
{
    public function normalizeFeeType(array $data, bool $installmentAllowed = false): array
    {
        return match ($data['category']) {
            'spp' => array_merge($data, [
                'installment_allowed' => false,
                'billing_frequency' => 'monthly',
                'applies_to' => 'all',
                'is_active' => true,
            ]),
            'meal' => array_merge($data, [
                'installment_allowed' => false,
                'billing_frequency' => 'monthly',
                'applies_to' => 'boarding',
                'is_active' => true,
            ]),
            'activity' => array_merge($data, [
                'installment_allowed' => true,
                'billing_frequency' => 'one_time',
                'is_active' => true,
            ]),
            default => array_merge($data, [
                'installment_allowed' => $installmentAllowed,
                'is_active' => true,
            ]),
        };
    }

    public function ensureSchemeDoesNotOverlap(array $data, ?FeeScheme $feeScheme = null): void
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

    public function deleteFeeType(FeeType $feeType): void
    {
        if ($feeType->schemes()->exists() || $feeType->invoices()->exists()) {
            throw ValidationException::withMessages([
                'error' => 'Tidak dapat menghapus jenis biaya karena memiliki tarif atau invoice aktif.',
            ]);
        }

        $feeType->delete();
    }

    public function deleteFeeScheme(FeeScheme $feeScheme): void
    {
        $hasInvoices = Invoice::query()
            ->where('fee_type_id', $feeScheme->fee_type_id)
            ->when($feeScheme->batch_id, fn ($q) => $q->whereHas('student', fn ($sq) => $sq->where('batch_id', $feeScheme->batch_id)))
            ->exists();

        if ($hasInvoices) {
            throw ValidationException::withMessages([
                'error' => 'Tidak dapat menghapus tarif karena memiliki tagihan/invoice aktif.',
            ]);
        }

        $feeScheme->delete();
    }
}
