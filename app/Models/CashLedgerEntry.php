<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class CashLedgerEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'entry_no',
        'transaction_date',
        'account_id',
        'direction',
        'source_type',
        'source_id',
        'amount',
        'description',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(CashAccount::class, 'account_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
