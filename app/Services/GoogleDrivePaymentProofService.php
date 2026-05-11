<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class GoogleDrivePaymentProofService
{
    public function upload(UploadedFile $file, string $paymentNo, ?string $studentName = null): array
    {
        if (! $this->isConfigured()) {
            return $this->uploadLocally($file, $paymentNo, $studentName);
        }

        $safeStudentName = Str::slug($studentName ?: 'siswa');
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'bin');
        $filename = sprintf(
            '%s-%s-%s.%s',
            $paymentNo,
            $safeStudentName,
            now()->format('YmdHis'),
            $extension
        );

        $mimeType = $file->getMimeType() ?: 'application/octet-stream';
        $response = Http::asForm()
            ->timeout(60)
            ->post($this->config('upload_url'), [
                'file' => base64_encode(file_get_contents($file->getRealPath())),
                'filename' => $filename,
                'subfolder' => $this->config('subfolder', 'Bukti Pembayaran'),
                'mime_type' => $mimeType,
            ]);

        if ($response->failed()) {
            throw ValidationException::withMessages([
                'payment_proof' => 'Upload bukti pembayaran ke Google Apps Script gagal. Status: '.$response->status(),
            ]);
        }

        $payload = $response->json();

        if (! is_array($payload) || ! ($payload['success'] ?? false)) {
            throw ValidationException::withMessages([
                'payment_proof' => $payload['error'] ?? 'Google Apps Script tidak mengembalikan respons upload yang valid.',
            ]);
        }

        if (blank($payload['url'] ?? null)) {
            throw ValidationException::withMessages([
                'payment_proof' => 'Google Apps Script berhasil dipanggil, tetapi URL file tidak dikembalikan.',
            ]);
        }

        return [
            'payment_proof_drive_id' => $payload['fileId'] ?? null,
            'payment_proof_name' => $payload['name'] ?? $filename,
            'payment_proof_mime_type' => $payload['mimeType'] ?? $mimeType,
            'payment_proof_url' => $payload['url'],
        ];
    }

    protected function uploadLocally(UploadedFile $file, string $paymentNo, ?string $studentName = null): array
    {
        $safeStudentName = Str::slug($studentName ?: 'siswa');
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'bin');
        $filename = sprintf(
            '%s-%s-%s.%s',
            $paymentNo,
            $safeStudentName,
            now()->format('YmdHis'),
            $extension
        );

        $path = $file->storeAs('payment-proofs', $filename, 'public');

        return [
            'payment_proof_drive_id' => 'local:'.$path,
            'payment_proof_name' => $filename,
            'payment_proof_mime_type' => $file->getMimeType() ?: 'application/octet-stream',
            'payment_proof_url' => Storage::disk('public')->url($path),
        ];
    }

    public function delete(?string $driveFileId): void
    {
        if (blank($driveFileId)) {
            return;
        }

        if (str_starts_with($driveFileId, 'local:')) {
            Storage::disk('public')->delete(Str::after($driveFileId, 'local:'));
        }

        // Apps Script upload mode does not expose a delete endpoint yet.
    }

    public function isConfigured(): bool
    {
        return filled($this->config('upload_url'));
    }

    protected function config(string $key, mixed $default = null): mixed
    {
        return data_get(config('filesystems.apps_script', []), $key, $default);
    }
}
