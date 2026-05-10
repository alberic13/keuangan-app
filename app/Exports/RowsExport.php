<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RowsExport implements FromArray, WithHeadings, ShouldAutoSize
{
    public function __construct(
        protected array $rows,
    ) {
    }

    public function array(): array
    {
        return array_map(fn (array $row) => array_values($row), $this->rows);
    }

    public function headings(): array
    {
        return array_keys($this->rows[0] ?? []);
    }
}
