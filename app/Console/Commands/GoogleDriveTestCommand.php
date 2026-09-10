<?php

namespace App\Console\Commands;

use App\Services\GoogleDrivePaymentProofService;
use Illuminate\Console\Command;
use Illuminate\Http\UploadedFile;

class GoogleDriveTestCommand extends Command
{
    protected $signature = 'google-drive:test';
    protected $description = 'Test upload bukti pembayaran via Google Apps Script';

    public function handle(GoogleDrivePaymentProofService $paymentProofs): int
    {
        $this->info('Memeriksa konfigurasi Google Apps Script...');

        $config = config('filesystems.apps_script', []);

        $this->line('Upload URL: ' . (filled($config['upload_url'] ?? null) ? 'terisi' : 'kosong'));
        $this->line('Root Folder: ' . ($config['root_folder'] ?? '-'));
        $this->line('Subfolder : ' . ($config['subfolder'] ?? '-'));

        if (! $paymentProofs->isConfigured()) {
            $this->error('Konfigurasi Google Apps Script belum lengkap. Isi GOOGLE_APPS_SCRIPT_UPLOAD_URL di file .env.');

            return self::FAILURE;
        }

        $path = tempnam(sys_get_temp_dir(), 'payment-proof-test-');
        file_put_contents($path, implode(PHP_EOL, [
            '%PDF-1.4',
            'Google Apps Script upload test',
            'App: E-Keuangan MAN 2 Surakarta',
            'Waktu: ' . now()->toDateTimeString(),
        ]));

        try {
            $result = $paymentProofs->upload(
                new UploadedFile($path, 'test-koneksi.pdf', 'application/pdf', null, true),
                'TEST',
                'Koneksi Apps Script'
            );

            $this->info('Upload via Google Apps Script berhasil.');
            $this->line('Nama file: ' . $result['payment_proof_name']);
            $this->line('URL file : ' . $result['payment_proof_url']);

            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $this->error('Upload via Google Apps Script gagal.');
            $this->error($exception->getMessage());

            return self::FAILURE;
        } finally {
            if (is_file($path)) {
                unlink($path);
            }
        }
    }
}
