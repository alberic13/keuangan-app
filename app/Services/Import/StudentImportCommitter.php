<?php

namespace App\Services\Import;

use App\Models\AcademicClass;
use App\Models\Batch;
use App\Models\ImportLog;
use App\Models\ImportLogRow;
use App\Models\Student;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class StudentImportCommitter
{
    public function commit(array $preview, string $previewPath, User $actor, AuditLogService $auditLogs, array $studentTypeMap): array
    {
        $batchMap = Batch::query()->get()->flatMap(fn ($b) => [
            Str::lower(trim((string) $b->year_label)) => $b->id,
            Str::lower(trim((string) $b->academic_year)) => $b->id,
        ])->all();
        $classMap = AcademicClass::query()->pluck('id', 'name')->mapWithKeys(fn ($id, $name) => [Str::lower(trim((string) $name)) => $id])->all();

        return DB::transaction(function () use ($preview, $previewPath, $actor, $auditLogs, $batchMap, $classMap, $studentTypeMap) {
            $importLog = ImportLog::query()->create([
                'type' => 'students_import',
                'file_name' => $preview['file_name'],
                'total_rows' => count($preview['rows']),
                'success_rows' => 0,
                'failed_rows' => 0,
                'imported_by' => $actor->id,
                'metadata_json' => Arr::only($preview, ['summary', 'generated_at']),
            ]);

            $success = 0;
            $failed = 0;

            foreach ($preview['rows'] as $row) {
                if ($row['status'] !== 'valid') {
                    ImportLogRow::query()->create([
                        'import_log_id' => $importLog->id,
                        'row_number' => $row['row_number'],
                        'payload_json' => $row['payload'],
                        'status' => 'failed',
                        'error_message' => implode('; ', array_values($row['errors'])),
                    ]);
                    $failed++;
                    continue;
                }

                $p = $row['payload'];
                $student = $this->matchStudent($p);
                $attrs = [
                    'nis' => $p['nis'] ?: null,
                    'nisn' => $p['nisn'] ?: null,
                    'full_name' => $p['full_name'],
                    'class_id' => $classMap[Str::lower($p['class'])],
                    'batch_id' => $batchMap[Str::lower($p['batch'])],
                    'student_type' => $studentTypeMap[Str::lower($p['student_type'])],
                    'is_active' => $p['is_active'],
                ];

                if ($student) {
                    $before = $student->toArray();
                    $student->update($attrs);
                    $auditLogs->log('student.import_updated', $student, $before, $student->fresh()->toArray(), 'Import siswa', $actor);
                } else {
                    $student = Student::query()->create($attrs);
                    $auditLogs->log('student.import_created', $student, null, $student->toArray(), 'Import siswa', $actor);
                }

                ImportLogRow::query()->create([
                    'import_log_id' => $importLog->id,
                    'row_number' => $row['row_number'],
                    'payload_json' => $p,
                    'status' => 'success',
                ]);
                $success++;
            }

            $importLog->update(['success_rows' => $success, 'failed_rows' => $failed]);
            $auditLogs->log('students.import_committed', 'StudentImport', null, $importLog->toArray(), 'Commit import siswa', $actor);
            File::delete($previewPath);

            return [
                'import_log_id' => $importLog->id,
                'total_rows' => $importLog->total_rows,
                'success_rows' => $success,
                'failed_rows' => $failed,
            ];
        });
    }

    protected function matchStudent(array $payload): ?Student
    {
        return Student::query()
            ->when($payload['nis'] !== '', fn ($query) => $query->orWhere('nis', $payload['nis']))
            ->when($payload['nisn'] !== '', fn ($query) => $query->orWhere('nisn', $payload['nisn']))
            ->first();
    }
}
