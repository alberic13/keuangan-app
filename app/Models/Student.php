<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'nis',
        'nisn',
        'full_name',
        'class_id',
        'major_id',
        'batch_id',
        'student_type',
        'is_active',
        'enrollment_date',
        'exit_date',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'enrollment_date' => 'date',
            'exit_date' => 'date',
        ];
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(Batch::class);
    }

    public function classRoom(): BelongsTo
    {
        return $this->belongsTo(AcademicClass::class, 'class_id');
    }

    public function major(): BelongsTo
    {
        return $this->belongsTo(Major::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
