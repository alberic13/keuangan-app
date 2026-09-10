<?php

namespace App\Services\Import;

use App\Models\StudentType;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;

class StudentImportRowParser
{
    public function readRows(UploadedFile $file): array
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $previousErrorReporting = error_reporting(E_ALL & ~E_DEPRECATED);

        try {
            if ($extension === 'csv') {
                return $this->readCsvRows($file->getRealPath());
            }

            if (! class_exists(\ZipArchive::class)) {
                throw ValidationException::withMessages([
                    'file' => 'File XLSX memerlukan ekstensi PHP zip. Aktifkan extension zip di XAMPP atau unggah file CSV.',
                ]);
            }

            $spreadsheet = IOFactory::load($file->getRealPath());
            return $spreadsheet->getActiveSheet()->toArray(null, false, false, false);
        } finally {
            error_reporting($previousErrorReporting);
        }
    }

    public function rowToPayload(array $headers, array $row): array
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

    public function rowIsEmpty(array $row): bool
    {
        foreach ($row as $cell) {
            if (trim((string) $cell) !== '') {
                return false;
            }
        }
        return true;
    }

    public function normalizeHeaders(array $headers): array
    {
        return array_map(function ($header) {
            $header = preg_replace('/^\xEF\xBB\xBF/', '', (string) $header);
            $header = Str::lower(trim($header));
            return str_replace([' ', '-'], '_', $header);
        }, $headers);
    }

    public function translateHeaders(array $headers): array
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

    public function validateRow(array $payload, array $batches, array $classes, array $studentTypes, array &$seenNis, array &$seenNisn): array
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
            $validTypes = StudentType::query()->where('is_active', true)->orderBy('slug')->pluck('label')->toArray();
            $errors['student_type'] = 'tipe_siswa harus '.implode(' atau ', $validTypes).'.';
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

    public function studentTypeMap(): array
    {
        return StudentType::query()
            ->where('is_active', true)
            ->get(['slug', 'label'])
            ->flatMap(fn (StudentType $st) => [
                Str::lower(trim($st->slug)) => $st->slug,
                Str::lower(trim($st->label)) => $st->slug,
            ])
            ->all();
    }

    protected function parseBoolean(string $value): bool
    {
        return ! in_array(Str::lower(trim($value)), ['0', 'false', 'tidak', 'inactive', 'nonaktif'], true);
    }

    protected function readCsvRows(string $path): array
    {
        $handle = fopen($path, 'rb');
        if ($handle === false) {
            throw ValidationException::withMessages(['file' => 'File CSV tidak dapat dibaca.']);
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
}
