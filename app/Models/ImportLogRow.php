<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class ImportLogRow extends Model
{
    use HasFactory;

    protected $fillable = [
        'import_log_id',
        'row_number',
        'payload_json',
        'status',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'payload_json' => 'array',
        ];
    }

    public function importLog(): BelongsTo
    {
        return $this->belongsTo(ImportLog::class);
    }
}
