<?php

namespace App\Services\Import;

use App\Models\AcademicClass;
use App\Models\Batch;
use App\Models\StudentType;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class StudentImportTemplateGenerator
{
    public function filename(): string
    {
        return $this->format() === 'csv' ? 'template_import_siswa.csv' : 'template_import_siswa.xlsx';
    }

    public function contentType(): string
    {
        return $this->format() === 'csv'
            ? 'text/csv; charset=UTF-8'
            : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
    }

    public function format(): string
    {
        return class_exists(\ZipArchive::class) ? 'xlsx' : 'csv';
    }

    public function write(string $target = 'php://output'): void
    {
        $previousErrorReporting = error_reporting(E_ALL & ~E_DEPRECATED);

        try {
            if ($this->format() === 'csv') {
                $this->writeCsv($target);
                return;
            }

            $headers = ['nis', 'nisn', 'nama_lengkap', 'kelas', 'angkatan', 'tipe_siswa', 'aktif'];
            $classes = AcademicClass::query()->orderBy('level')->orderBy('name')->pluck('name')->values()->all();
            $batches = Batch::query()->orderByDesc('academic_year')->pluck('academic_year')->values()->all();
            $studentTypes = StudentType::query()->where('is_active', true)->orderBy('slug')->pluck('label')->values()->all();
            $activeOptions = ['aktif', 'nonaktif'];

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Import Siswa');
            $sheet->fromArray($headers, null, 'A1');

            $headerStyle = [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '00422F']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ];

            $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);
            $sheet->freezePane('A2');
            $sheet->setAutoFilter('A1:G1001');

            foreach (['A' => 14, 'B' => 16, 'C' => 28, 'D' => 16, 'E' => 14, 'F' => 16, 'G' => 12] as $col => $width) {
                $sheet->getColumnDimension($col)->setWidth($width);
            }

            $refSheet = $spreadsheet->createSheet();
            $refSheet->setTitle('Referensi');
            $refSheet->fromArray(['kelas', 'angkatan', 'tipe_siswa', 'aktif'], null, 'A1');

            $references = [$classes, $batches, $studentTypes, $activeOptions];
            $maxRows = max(array_map('count', $references) ?: [1]);

            for ($i = 0; $i < $maxRows; $i++) {
                $refSheet->fromArray([$classes[$i] ?? null, $batches[$i] ?? null, $studentTypes[$i] ?? null, $activeOptions[$i] ?? null], null, 'A'.($i + 2));
            }

            $refSheet->getStyle('A1:D1')->applyFromArray($headerStyle);
            foreach (['A', 'B', 'C', 'D'] as $col) {
                $refSheet->getColumnDimension($col)->setWidth(18);
            }

            $validations = [
                'D' => ['A', max(count($classes), 1)],
                'E' => ['B', max(count($batches), 1)],
                'F' => ['C', max(count($studentTypes), 1)],
                'G' => ['D', count($activeOptions)],
            ];

            foreach ($validations as $col => [$refCol, $count]) {
                for ($row = 2; $row <= 1001; $row++) {
                    $validation = $sheet->getCell($col.$row)->getDataValidation();
                    $validation->setType(DataValidation::TYPE_LIST);
                    $validation->setErrorStyle(DataValidation::STYLE_STOP);
                    $validation->setAllowBlank(false);
                    $validation->setShowDropDown(true);
                    $validation->setShowErrorMessage(true);
                    $validation->setErrorTitle('Input tidak valid');
                    $validation->setError('Pilih nilai dari daftar referensi.');
                    $validation->setFormula1(sprintf("'Referensi'!\$%s\$2:\$%s\$%d", $refCol, $refCol, $count + 1));
                }
            }

            $spreadsheet->setActiveSheetIndex(0);
            (new Xlsx($spreadsheet))->save($target);
        } finally {
            error_reporting($previousErrorReporting);
        }
    }

    protected function writeCsv(string $target): void
    {
        $handle = fopen($target, 'wb');
        if ($handle === false) {
            throw ValidationException::withMessages(['file' => 'Template import tidak bisa dibuat karena storage target tidak dapat ditulis.']);
        }

        try {
            fputcsv($handle, ['nis', 'nisn', 'nama_lengkap', 'kelas', 'angkatan', 'tipe_siswa', 'aktif']);
        } finally {
            fclose($handle);
        }
    }
}
