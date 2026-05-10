<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class FeeType extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'category',
        'installment_allowed',
        'billing_frequency',
        'applies_to',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'installment_allowed' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function schemes(): HasMany
    {
        return $this->hasMany(FeeScheme::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
