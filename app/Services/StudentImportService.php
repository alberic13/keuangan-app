<?php

namespace App\Services;

use App\Models\AcademicClass;
use App\Models\Batch;
use App\Models\User;
use App\Services\Import\StudentImportCommitter;
use App\Services\Import\StudentImportRowParser;
use App\Services\Import\StudentImportTemplateGenerator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StudentImportService
{
    public function __construct(
        protected AuditLogService $auditLogs,
        protected StudentImportRowParser $parser = new StudentImportRowParser(),
        protected StudentImportTemplateGenerator $templateGenerator = new StudentImportTemplateGenerator(),
        protected StudentImportCommitter $committer = new StudentImportCommitter(),
    ) {
    }

    public function preview(UploadedFile $file): array
    {
        $rows = $this->parser->readRows($file);
        if ($rows === [] || count($rows) < 2) {
            throw ValidationException::withMessages(['file' => 'File import kosong atau tidak memiliki data.']);
        }

        $headers = $this->parser->translateHeaders($this->parser->normalizeHeaders(array_shift($rows)));
        $requiredHeaders = ['nis', 'nisn', 'full_name', 'class', 'batch', 'student_type'];
        if (array_diff($requiredHeaders, $headers) !== []) {
            $idHeaders = ['nis', 'nisn', 'nama_lengkap', 'kelas', 'angkatan', 'tipe_siswa'];
            throw ValidationException::withMessages(['file' => 'Header template tidak sesuai. Header wajib: '.implode(', ', $idHeaders)]);
        }

        $batches = Batch::query()->get()->flatMap(fn ($b) => [
            Str::lower(trim((string) $b->year_label)) => $b->id,
            Str::lower(trim((string) $b->academic_year)) => $b->id,
        ]);
        $classes = AcademicClass::query()->pluck('id', 'name')->mapWithKeys(fn ($id, $name) => [Str::lower(trim((string) $name)) => $id]);
        $studentTypes = $this->parser->studentTypeMap();

        $seenNis = [];
        $seenNisn = [];
        $previewRows = [];
        $errors = [];
        $validRows = 0;

        foreach ($rows as $index => $row) {
            if ($this->parser->rowIsEmpty($row)) {
                continue;
            }

            $rowNumber = $index + 2;
            $payload = $this->parser->rowToPayload($headers, $row);
            $rowErrors = $this->parser->validateRow($payload, $batches->all(), $classes->all(), $studentTypes, $seenNis, $seenNisn);

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
                    $errors[] = ['row_number' => $rowNumber, 'field' => $field, 'message' => $message];
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
        return $this->templateGenerator->filename();
    }

    public function templateContentType(): string
    {
        return $this->templateGenerator->contentType();
    }

    public function writeTemplate(string $target = 'php://output'): void
    {
        $this->templateGenerator->write($target);
    }

    public function commit(string $previewToken, User $actor): array
    {
        $previewPath = $this->previewPath($previewToken);
        if (! File::exists($previewPath)) {
            throw ValidationException::withMessages(['preview_token' => 'Preview token import tidak valid atau kedaluwarsa.']);
        }

        $preview = json_decode(File::get($previewPath), true);
        return $this->committer->commit($preview, $previewPath, $actor, $this->auditLogs, $this->parser->studentTypeMap());
    }

    public function previewPayload(string $previewToken): array
    {
        $previewPath = $this->previewPath($previewToken);
        if (! File::exists($previewPath)) {
            throw ValidationException::withMessages(['preview_token' => 'Preview token import tidak valid atau kedaluwarsa.']);
        }

        return json_decode(File::get($previewPath), true);
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
