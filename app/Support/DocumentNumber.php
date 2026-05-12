<?php

namespace App\Support;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;

class DocumentNumber
{
    public static function next(string $prefix, string $modelClass, string $column, CarbonInterface|string|null $date = null): string
    {
        $date = $date instanceof CarbonInterface ? $date : Carbon::parse($date);
        $base = sprintf('%s-%s', strtoupper($prefix), $date->format('Ym'));
        $latestNumber = $modelClass::query()
            ->where($column, 'like', $base.'-%')
            ->max($column);

        $sequence = 1;

        if (is_string($latestNumber) && preg_match('/-(\d+)$/', $latestNumber, $matches)) {
            $sequence = ((int) $matches[1]) + 1;
        }

        return sprintf('%s-%06d', $base, $sequence);
    }

    public static function currentKey(Model|string $entity): string
    {
        return is_string($entity) ? $entity : class_basename($entity);
    }
}
