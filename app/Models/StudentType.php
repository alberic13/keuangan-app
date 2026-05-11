<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentType extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'label',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'student_type', 'slug');
    }

    public static function activeSlugs(): array
    {
        return static::query()
            ->where('is_active', true)
            ->orderBy('label')
            ->pluck('slug')
            ->all();
    }
}