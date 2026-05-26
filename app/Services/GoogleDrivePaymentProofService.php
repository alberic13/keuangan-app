<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
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

        try {
            $response = Http::asForm()
                ->timeout(60)
                ->withOptions([
                    'verify' => $this->caBundle(),
                ])
                ->post($this->config('upload_url'), [
                    'file' => base64_encode(file_get_contents($file->getRealPath())),
                    'filename' => $filename,
                    'subfolder' => $this->config('subfolder', 'Bukti Pembayaran'),
                    'mime_type' => $mimeType,
                ]);
        } catch (ConnectionException $exception) {
            return $this->fallbackOrFail(
                $file,
                $paymentNo,
                $studentName,
                'Koneksi ke Google Apps Script gagal: '.$exception->getMessage()
            );
        }

        if ($response->failed()) {
            $responseMessage = $this->extractResponseMessage($response->json(), $response->body());

            if ($this->shouldFallbackToLocalUpload($response->status(), $responseMessage)) {
                return $this->uploadLocally($file, $paymentNo, $studentName);
            }

            throw ValidationException::withMessages([
                'payment_proof' => $responseMessage ?: 'Upload bukti pembayaran ke Google Apps Script gagal. Status: '.$response->status(),
            ]);
        }

        $payload = $response->json();

        if (! is_array($payload) || ! ($payload['success'] ?? false)) {
            $responseMessage = $this->extractResponseMessage($payload, $response->body());

            if ($this->shouldFallbackToLocalUpload(null, $responseMessage)) {
                return $this->uploadLocally($file, $paymentNo, $studentName);
            }

            throw ValidationException::withMessages([
                'payment_proof' => $responseMessage ?: 'Google Apps Script tidak mengembalikan respons upload yang valid.',
            ]);
        }

        if (blank($payload['fileId'] ?? null) && blank($payload['url'] ?? null)) {
            throw ValidationException::withMessages([
                'payment_proof' => 'Google Apps Script berhasil dipanggil, tetapi ID/URL file tidak dikembalikan.',
            ]);
        }

        $fileId = filled($payload['fileId'] ?? null) ? trim((string) $payload['fileId']) : null;
        $url = $fileId
            ? 'https://drive.google.com/file/d/'.rawurlencode($fileId).'/view'
            : trim((string) $payload['url']);

        return [
            'payment_proof_drive_id' => $fileId,
            'payment_proof_name' => $payload['name'] ?? $filename,
            'payment_proof_mime_type' => $payload['mimeType'] ?? $mimeType,
            'payment_proof_url' => $url,
        ];
    }

    protected function fallbackOrFail(UploadedFile $file, string $paymentNo, ?string $studentName, string $message): array
    {
        if ($this->shouldFallbackToLocalUpload(null, $message)) {
            return $this->uploadLocally($file, $paymentNo, $studentName);
        }

        throw ValidationException::withMessages([
            'payment_proof' => $message,
        ]);
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

    protected function shouldFallbackToLocalUpload(?int $status, ?string $message): bool
    {
        if (! app()->environment(['local', 'testing'])) {
            return false;
        }

        if ($status !== null && in_array($status, [401, 403, 404, 500, 502, 503], true)) {
            return true;
        }

        $message = Str::lower((string) $message);

        return str_contains($message, 'driveapp')
            || str_contains($message, 'referenceerror')
            || str_contains($message, 'is not defined')
            || str_contains($message, 'access denied')
            || str_contains($message, 'permission denied')
            || str_contains($message, 'not authorized')
            || str_contains($message, 'unauthorized')
            || str_contains($message, 'forbidden');
    }

    protected function extractResponseMessage(mixed $payload, ?string $rawBody = null): ?string
    {
        if (is_array($payload)) {
            foreach (['error', 'message', 'details'] as $key) {
                if (! blank($payload[$key] ?? null)) {
                    return trim((string) $payload[$key]);
                }
            }
        }

        if (is_string($rawBody) && trim($rawBody) !== '') {
            return trim(strip_tags($rawBody));
        }

        return null;
    }

    protected function config(string $key, mixed $default = null): mixed
    {
        $value = data_get(config('filesystems.apps_script', []), $key, $default);

        return is_string($value) ? trim($value) : $value;
    }

    protected function caBundle(): string|bool
    {
        $configured = $this->config('ca_bundle');

        if (is_string($configured) && $configured !== '' && is_file($configured)) {
            return $configured;
        }

        foreach ([
            ini_get('curl.cainfo'),
            ini_get('openssl.cafile'),
            '/usr/local/etc/ca-certificates/cert.pem',
            '/etc/ssl/cert.pem',
            '/Applications/XAMPP/xamppfiles/phpmyadmin/vendor/composer/ca-bundle/res/cacert.pem',
        ] as $path) {
            if (is_string($path) && $path !== '' && is_file($path)) {
                return $path;
            }
        }

        return true;
    }
}
