<?php

namespace App\Services;

use App\Models\AcademicClass;
use App\Models\Batch;
use App\Models\ImportLog;
use App\Models\ImportLogRow;
use App\Models\Major;
use App\Models\StudentType;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;

class StudentImportService
{
    public function __construct(
        protected AuditLogService $auditLogs,
    ) {
    }

    public function preview(UploadedFile $file): array
    {
        $spreadsheet = IOFactory::load($file->getRealPath());
        $rows = $spreadsheet->getActiveSheet()->toArray(null, false, false, false);

        if ($rows === [] || count($rows) < 2) {
            throw ValidationException::withMessages([
                'file' => 'File import kosong atau tidak memiliki data.',
            ]);
        }

        $headers = $this->normalizeHeaders(array_shift($rows));
        $requiredHeaders = ['nis', 'nisn', 'full_name', 'class', 'major', 'batch', 'student_type'];
        $missingHeaders = array_values(array_diff($requiredHeaders, $headers));

        if ($missingHeaders !== []) {
            throw ValidationException::withMessages([
                'file' => 'Header template tidak sesuai. Header wajib: '.implode(', ', $requiredHeaders),
            ]);
        }

        $batches = Batch::query()->pluck('id', 'year_label')->mapWithKeys(fn ($id, $label) => [Str::lower(trim((string) $label)) => $id]);
        $classes = AcademicClass::query()->pluck('id', 'name')->mapWithKeys(fn ($id, $name) => [Str::lower(trim((string) $name)) => $id]);
        $majors = Major::query()
            ->get(['id', 'name', 'code'])
            ->flatMap(fn (Major $major) => [
                Str::lower(trim($major->name)) => $major->id,
                Str::lower(trim($major->code)) => $major->id,
            ]);
        $studentTypes = $this->studentTypeMap();

        $seenNis = [];
        $seenNisn = [];
        $previewRows = [];
        $errors = [];
        $validRows = 0;

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;
            $payload = $this->rowToPayload($headers, $row);
            $rowErrors = $this->validateRow($payload, $batches->all(), $classes->all(), $majors->all(), $studentTypes, $seenNis, $seenNisn);

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

    public function commit(string $previewToken, User $actor): array
    {
        $previewPath = $this->previewPath($previewToken);

        if (! File::exists($previewPath)) {
            throw ValidationException::withMessages([
                'preview_token' => 'Preview token import tidak valid atau kedaluwarsa.',
            ]);
        }

        $preview = json_decode(File::get($previewPath), true);
        $batchMap = Batch::query()->pluck('id', 'year_label')->mapWithKeys(fn ($id, $label) => [Str::lower(trim((string) $label)) => $id])->all();
        $classMap = AcademicClass::query()->pluck('id', 'name')->mapWithKeys(fn ($id, $name) => [Str::lower(trim((string) $name)) => $id])->all();
        $majorMap = Major::query()
            ->get(['id', 'name', 'code'])
            ->flatMap(fn (Major $major) => [
                Str::lower(trim($major->name)) => $major->id,
                Str::lower(trim($major->code)) => $major->id,
            ])->all();
        $studentTypeMap = $this->studentTypeMap();

        return DB::transaction(function () use ($preview, $previewPath, $actor, $batchMap, $classMap, $majorMap, $studentTypeMap) {
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
                    'major_id' => $majorMap[Str::lower($payload['major'])],
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
            'major' => $mapped['major'] ?? '',
            'batch' => $mapped['batch'] ?? '',
            'student_type' => Str::lower($mapped['student_type'] ?? ''),
            'is_active' => $this->parseBoolean($mapped['is_active'] ?? ''),
        ];
    }

    protected function normalizeHeaders(array $headers): array
    {
        return array_map(function ($header) {
            $header = Str::lower(trim((string) $header));

            return str_replace([' ', '-'], '_', $header);
        }, $headers);
    }

    protected function validateRow(array $payload, array $batches, array $classes, array $majors, array $studentTypes, array &$seenNis, array &$seenNisn): array
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

        if ($payload['major'] === '' || ! array_key_exists(Str::lower($payload['major']), $majors)) {
            $errors['major'] = 'Jurusan tidak ditemukan.';
        }

        if ($payload['batch'] === '' || ! array_key_exists(Str::lower($payload['batch']), $batches)) {
            $errors['batch'] = 'Angkatan tidak ditemukan.';
        }

        if (! array_key_exists(Str::lower($payload['student_type']), $studentTypes)) {
            $errors['student_type'] = 'student_type harus '.implode(' atau ', array_unique(array_values($studentTypes))).'.';
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
