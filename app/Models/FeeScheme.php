<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class FeeScheme extends Model
{
    use HasFactory;

    protected $fillable = [
        'fee_type_id',
        'batch_id',
        'nominal',
        'effective_start',
        'effective_end',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'effective_start' => 'date',
            'effective_end' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function feeType(): BelongsTo
    {
        return $this->belongsTo(FeeType::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }
}
