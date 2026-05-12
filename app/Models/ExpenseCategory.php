<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ExpenseCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public static function generateCode(string $name, ?int $ignoreId = null): string
    {
        $baseCode = Str::upper(Str::slug($name, '_'));
        $baseCode = Str::of($baseCode)->replaceMatches('/[^A-Z0-9_]/', '')->trim('_')->toString();
        $baseCode = $baseCode !== '' ? $baseCode : 'KATEGORI';

        $code = $baseCode;
        $suffix = 1;

        while (static::query()
            ->when($ignoreId !== null, fn ($query) => $query->whereKeyNot($ignoreId))
            ->where('code', $code)
            ->exists()) {
            $code = $baseCode.'_'.$suffix;
            $suffix++;
        }

        return $code;
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class, 'category_id');
    }
}
