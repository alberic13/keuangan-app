<?php

namespace App\Services;

use App\Models\AcademicClass;
use App\Models\Batch;
use App\Models\ImportLog;
use App\Models\ImportLogRow;
use App\Models\StudentType;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class StudentImportService
{
    public function __construct(
        protected AuditLogService $auditLogs,
    ) {
    }

    public function preview(UploadedFile $file): array
    {
        $previousErrorReporting = error_reporting(E_ALL & ~E_DEPRECATED);
        $extension = strtolower($file->getClientOriginalExtension());

        try {
            if ($extension === 'csv') {
                $rows = $this->readCsvRows($file->getRealPath());
            } else {
                $this->ensureZipArchiveAvailable();

                $spreadsheet = IOFactory::load($file->getRealPath());
                $rows = $spreadsheet->getActiveSheet()->toArray(null, false, false, false);
            }
        } finally {
            error_reporting($previousErrorReporting);
        }

        if ($rows === [] || count($rows) < 2) {
            throw ValidationException::withMessages([
                'file' => 'File import kosong atau tidak memiliki data.',
            ]);
        }

        $headers = $this->translateHeaders($this->normalizeHeaders(array_shift($rows)));
        $requiredHeaders = ['nis', 'nisn', 'full_name', 'class', 'batch', 'student_type'];
        $missingHeaders = array_values(array_diff($requiredHeaders, $headers));

        if ($missingHeaders !== []) {
            $indonesianRequiredHeaders = ['nis', 'nisn', 'nama_lengkap', 'kelas', 'angkatan', 'tipe_siswa'];
            throw ValidationException::withMessages([
                'file' => 'Header template tidak sesuai. Header wajib: '.implode(', ', $indonesianRequiredHeaders),
            ]);
        }

        $batches = Batch::query()
            ->get()
            ->flatMap(fn ($b) => [
                Str::lower(trim((string) $b->year_label)) => $b->id,
                Str::lower(trim((string) $b->academic_year)) => $b->id,
            ]);
        $classes = AcademicClass::query()->pluck('id', 'name')->mapWithKeys(fn ($id, $name) => [Str::lower(trim((string) $name)) => $id]);
        $studentTypes = $this->studentTypeMap();

        $seenNis = [];
        $seenNisn = [];
        $previewRows = [];
        $errors = [];
        $validRows = 0;

        foreach ($rows as $index => $row) {
            if ($this->rowIsEmpty($row)) {
                continue;
            }

            $rowNumber = $index + 2;
            $payload = $this->rowToPayload($headers, $row);
            $rowErrors = $this->validateRow($payload, $batches->all(), $classes->all(), $studentTypes, $seenNis, $seenNisn);

            $previewRows[] = [
                'row_number' => $rowNumber,
                'payload' => $payload,
                'status' => $rowErrors === [] ? 'valid' : 'invalid',
                'errors' => $rowErrors,
            ];

            if ($rowErrors === []) {
                $validRows++;
            } else {
                foreach ($rowErrors as $field => $message) {
                    $errors[] = [
                        'row_number' => $rowNumber,
                        'field' => $field,
                        'message' => $message,
                    ];
                }
            }
        }

        $token = 'imp_prev_'.Str::lower(Str::random(24));
        $previewPayload = [
            'file_name' => $file->getClientOriginalName(),
            'rows' => $previewRows,
            'summary' => [
                'total_rows' => count($previewRows),
                'valid_rows' => $validRows,
                'invalid_rows' => count($previewRows) - $validRows,
            ],
            'generated_at' => now()->toIso8601String(),
        ];

        File::ensureDirectoryExists($this->previewDirectory());
        File::put($this->previewPath($token), json_encode($previewPayload, JSON_PRETTY_PRINT));

        return [
            'preview_token' => $token,
            'summary' => $previewPayload['summary'],
            'errors' => $errors,
            'rows' => $previewRows,
        ];
    }

    public function templateFilename(): string
    {
        return $this->templateFormat() === 'csv'
            ? 'template_import_siswa.csv'
            : 'template_import_siswa.xlsx';
    }

    public function templateContentType(): string
    {
        return $this->templateFormat() === 'csv'
            ? 'text/csv; charset=UTF-8'
            : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
    }

    public function writeTemplate(string $target = 'php://output'): void
    {
        $previousErrorReporting = error_reporting(E_ALL & ~E_DEPRECATED);

        try {
            if ($this->templateFormat() === 'csv') {
                $this->writeCsvTemplate($target);

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

            foreach ([
                'A' => 14,
                'B' => 16,
                'C' => 28,
                'D' => 16,
                'E' => 14,
                'F' => 16,
                'G' => 12,
            ] as $column => $width) {
                $sheet->getColumnDimension($column)->setWidth($width);
            }

            $referenceSheet = $spreadsheet->createSheet();
            $referenceSheet->setTitle('Referensi');
            $referenceSheet->fromArray(['kelas', 'angkatan', 'tipe_siswa', 'aktif'], null, 'A1');

            $references = [$classes, $batches, $studentTypes, $activeOptions];
            $maxReferenceRows = max(array_map('count', $references) ?: [1]);

            for ($i = 0; $i < $maxReferenceRows; $i++) {
                $referenceSheet->fromArray([
                    $classes[$i] ?? null,
                    $batches[$i] ?? null,
                    $studentTypes[$i] ?? null,
                    $activeOptions[$i] ?? null,
                ], null, 'A'.($i + 2));
            }

            $referenceSheet->getStyle('A1:D1')->applyFromArray($headerStyle);

            foreach (['A', 'B', 'C', 'D'] as $column) {
                $referenceSheet->getColumnDimension($column)->setWidth(18);
            }

            $validationColumns = [
                'D' => ['A', max(count($classes), 1)],
                'E' => ['B', max(count($batches), 1)],
                'F' => ['C', max(count($studentTypes), 1)],
                'G' => ['D', count($activeOptions)],
            ];

            foreach ($validationColumns as $inputColumn => [$referenceColumn, $count]) {
                for ($row = 2; $row <= 1001; $row++) {
                    $validation = $sheet->getCell($inputColumn.$row)->getDataValidation();
                    $validation->setType(DataValidation::TYPE_LIST);
                    $validation->setErrorStyle(DataValidation::STYLE_STOP);
                    $validation->setAllowBlank(false);
                    $validation->setShowDropDown(true);
                    $validation->setShowErrorMessage(true);
                    $validation->setErrorTitle('Input tidak valid');
                    $validation->setError('Pilih nilai dari daftar referensi.');
                    $validation->setFormula1(sprintf("'Referensi'!\$%s\$2:\$%s\$%d", $referenceColumn, $referenceColumn, $count + 1));
                }
            }

            $spreadsheet->setActiveSheetIndex(0);

            (new Xlsx($spreadsheet))->save($target);
        } finally {
            error_reporting($previousErrorReporting);
        }
    }

    protected function writeCsvTemplate(string $target): void
    {
        $handle = fopen($target, 'wb');

        if ($handle === false) {
            throw ValidationException::withMessages([
                'file' => 'Template import tidak bisa dibuat karena storage target tidak dapat ditulis.',
            ]);
        }

        try {
            fputcsv($handle, ['nis', 'nisn', 'nama_lengkap', 'kelas', 'angkatan', 'tipe_siswa', 'aktif']);
        } finally {
            fclose($handle);
        }
    }

    public function commit(string $previewToken, User $actor): array
    {
        $previewPath = $this->previewPath($previewToken);

        if (! File::exists($previewPath)) {
            throw ValidationException::withMessages([
                'preview_token' => 'Preview token import tidak valid atau kedaluwarsa.',
            ]);
        }

        $preview = json_decode(File::get($previewPath), true);
        $batchMap = Batch::query()
            ->get()
            ->flatMap(fn ($b) => [
                Str::lower(trim((string) $b->year_label)) => $b->id,
                Str::lower(trim((string) $b->academic_year)) => $b->id,
            ])
            ->all();
        $classMap = AcademicClass::query()->pluck('id', 'name')->mapWithKeys(fn ($id, $name) => [Str::lower(trim((string) $name)) => $id])->all();
        $studentTypeMap = $this->studentTypeMap();

        return DB::transaction(function () use ($preview, $previewPath, $actor, $batchMap, $classMap, $studentTypeMap) {
            $importLog = ImportLog::query()->create([
                'type' => 'students_import',
                'file_name' => $preview['file_name'],
                'total_rows' => count($preview['rows']),
                'success_rows' => 0,
                'failed_rows' => 0,
                'imported_by' => $actor->id,
                'metadata_json' => Arr::only($preview, ['summary', 'generated_at']),
            ]);

            $successRows = 0;
            $failedRows = 0;

            foreach ($preview['rows'] as $previewRow) {
                if ($previewRow['status'] !== 'valid') {
                    ImportLogRow::query()->create([
                        'import_log_id' => $importLog->id,
                        'row_number' => $previewRow['row_number'],
                        'payload_json' => $previewRow['payload'],
                        'status' => 'failed',
                        'error_message' => implode('; ', array_values($previewRow['errors'])),
                    ]);
                    $failedRows++;

                    continue;
                }

                $payload = $previewRow['payload'];
                $student = $this->matchStudent($payload);

                $attributes = [
                    'nis' => $payload['nis'] ?: null,
                    'nisn' => $payload['nisn'] ?: null,
                    'full_name' => $payload['full_name'],
                    'class_id' => $classMap[Str::lower($payload['class'])],
                    'batch_id' => $batchMap[Str::lower($payload['batch'])],
                    'student_type' => $studentTypeMap[Str::lower($payload['student_type'])],
                    'is_active' => $payload['is_active'],
                ];

                if ($student) {
                    $before = $student->toArray();
                    $student->update($attributes);
                    $this->auditLogs->log('student.import_updated', $student, $before, $student->fresh()->toArray(), 'Import siswa', $actor);
                } else {
                    $student = Student::query()->create($attributes);
                    $this->auditLogs->log('student.import_created', $student, null, $student->toArray(), 'Import siswa', $actor);
                }

                ImportLogRow::query()->create([
                    'import_log_id' => $importLog->id,
                    'row_number' => $previewRow['row_number'],
                    'payload_json' => $payload,
                    'status' => 'success',
                ]);
                $successRows++;
            }

            $importLog->update([
                'success_rows' => $successRows,
                'failed_rows' => $failedRows,
            ]);

            $this->auditLogs->log('students.import_committed', 'StudentImport', null, $importLog->toArray(), 'Commit import siswa', $actor);
            File::delete($previewPath);

            return [
                'import_log_id' => $importLog->id,
                'total_rows' => $importLog->total_rows,
                'success_rows' => $successRows,
                'failed_rows' => $failedRows,
            ];
        });
    }

    public function previewPayload(string $previewToken): array
    {
        $previewPath = $this->previewPath($previewToken);

        if (! File::exists($previewPath)) {
            throw ValidationException::withMessages([
                'preview_token' => 'Preview token import tidak valid atau kedaluwarsa.',
            ]);
        }

        return json_decode(File::get($previewPath), true);
    }

    protected function rowToPayload(array $headers, array $row): array
    {
        $mapped = [];
        foreach ($headers as $index => $header) {
            $mapped[$header] = trim((string) ($row[$index] ?? ''));
        }

        return [
            'nis' => $mapped['nis'] ?? '',
            'nisn' => $mapped['nisn'] ?? '',
            'full_name' => $mapped['full_name'] ?? '',
            'class' => $mapped['class'] ?? '',
            'batch' => $mapped['batch'] ?? '',
            'student_type' => Str::lower($mapped['student_type'] ?? ''),
            'is_active' => $this->parseBoolean($mapped['aktif'] ?? $mapped['status_aktif'] ?? $mapped['is_active'] ?? ''),
        ];
    }

    protected function rowIsEmpty(array $row): bool
    {
        foreach ($row as $cell) {
            if (trim((string) $cell) !== '') {
                return false;
            }
        }

        return true;
    }

    protected function normalizeHeaders(array $headers): array
    {
        return array_map(function ($header) {
            $header = preg_replace('/^\xEF\xBB\xBF/', '', (string) $header);
            $header = Str::lower(trim($header));

            return str_replace([' ', '-'], '_', $header);
        }, $headers);
    }

    protected function translateHeaders(array $headers): array
    {
        $translation = [
            'nama_lengkap' => 'full_name',
            'kelas' => 'class',
            'angkatan' => 'batch',
            'tipe_siswa' => 'student_type',
            'aktif' => 'is_active',
            'status_aktif' => 'is_active',
        ];

        return array_map(fn ($h) => $translation[$h] ?? $h, $headers);
    }

    protected function readCsvRows(string $path): array
    {
        $handle = fopen($path, 'rb');

        if ($handle === false) {
            throw ValidationException::withMessages([
                'file' => 'File CSV tidak dapat dibaca.',
            ]);
        }

        $rows = [];

        try {
            while (($row = fgetcsv($handle)) !== false) {
                $rows[] = $row;
            }
        } finally {
            fclose($handle);
        }

        return $rows;
    }

    protected function templateFormat(): string
    {
        return $this->zipArchiveAvailable() ? 'xlsx' : 'csv';
    }

    protected function ensureZipArchiveAvailable(): void
    {
        if (! $this->zipArchiveAvailable()) {
            throw ValidationException::withMessages([
                'file' => 'File XLSX memerlukan ekstensi PHP zip. Aktifkan extension zip di XAMPP atau unggah file CSV dari template yang disediakan aplikasi.',
            ]);
        }
    }

    protected function zipArchiveAvailable(): bool
    {
        return class_exists(\ZipArchive::class);
    }

    protected function validateRow(array $payload, array $batches, array $classes, array $studentTypes, array &$seenNis, array &$seenNisn): array
    {
        $errors = [];

        if ($payload['nis'] === '' && $payload['nisn'] === '') {
            $errors['nis'] = 'NIS atau NISN wajib ada minimal salah satu.';
        }

        if ($payload['full_name'] === '') {
            $errors['full_name'] = 'Nama wajib terisi.';
        }

        if ($payload['class'] === '' || ! array_key_exists(Str::lower($payload['class']), $classes)) {
            $errors['class'] = 'Kelas tidak ditemukan.';
        }

        if ($payload['batch'] === '' || ! array_key_exists(Str::lower($payload['batch']), $batches)) {
            $errors['batch'] = 'Angkatan tidak ditemukan.';
        }

        if (! array_key_exists(Str::lower($payload['student_type']), $studentTypes)) {
            $errors['student_type'] = 'tipe_siswa harus '.implode(' atau ', StudentType::query()->where('is_active', true)->orderBy('slug')->pluck('label')->toArray()).'.';
        }

        if ($payload['nis'] !== '') {
            if (in_array($payload['nis'], $seenNis, true)) {
                $errors['nis'] = 'NIS duplikat di dalam file.';
            }

            $seenNis[] = $payload['nis'];
        }

        if ($payload['nisn'] !== '') {
            if (in_array($payload['nisn'], $seenNisn, true)) {
                $errors['nisn'] = 'NISN duplikat di dalam file.';
            }

            $seenNisn[] = $payload['nisn'];
        }

        return $errors;
    }

    protected function matchStudent(array $payload): ?Student
    {
        return Student::query()
            ->when($payload['nis'] !== '', fn ($query) => $query->orWhere('nis', $payload['nis']))
            ->when($payload['nisn'] !== '', fn ($query) => $query->orWhere('nisn', $payload['nisn']))
            ->first();
    }

    protected function parseBoolean(string $value): bool
    {
        return ! in_array(Str::lower(trim($value)), ['0', 'false', 'tidak', 'inactive', 'nonaktif'], true);
    }

    protected function studentTypeMap(): array
    {
        return StudentType::query()
            ->where('is_active', true)
            ->get(['slug', 'label'])
            ->flatMap(fn (StudentType $studentType) => [
                Str::lower(trim($studentType->slug)) => $studentType->slug,
                Str::lower(trim($studentType->label)) => $studentType->slug,
            ])
            ->all();
    }

    protected function previewDirectory(): string
    {
        return storage_path('app/private/import-previews');
    }

    protected function previewPath(string $previewToken): string
    {
        return $this->previewDirectory().DIRECTORY_SEPARATOR.$previewToken.'.json';
    }
}
