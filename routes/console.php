<?php

use App\Services\GoogleDrivePaymentProofService;
use App\Support\DocumentNumber;
use Illuminate\Foundation\Inspiring;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('google-drive:test', function (GoogleDrivePaymentProofService $paymentProofs) {
    $this->info('Memeriksa konfigurasi Google Apps Script...');

    $config = config('filesystems.apps_script', []);

    $this->line('Upload URL: '.(filled($config['upload_url'] ?? null) ? 'terisi' : 'kosong'));
    $this->line('Subfolder : '.($config['subfolder'] ?? '-'));

    if (! $paymentProofs->isConfigured()) {
        $this->error('Konfigurasi Google Apps Script belum lengkap. Isi GOOGLE_APPS_SCRIPT_UPLOAD_URL di file .env.');

        return self::FAILURE;
    }

    $path = tempnam(sys_get_temp_dir(), 'payment-proof-test-');
    file_put_contents($path, implode(PHP_EOL, [
        '%PDF-1.4',
        'Google Apps Script upload test',
        'App: E-Keuangan MAN 2 Surakarta',
        'Waktu: '.now()->toDateTimeString(),
    ]));

    try {
        $result = $paymentProofs->upload(
            new UploadedFile($path, 'test-koneksi.pdf', 'application/pdf', null, true),
            'TEST',
            'Koneksi Apps Script'
        );

        $this->info('Upload via Google Apps Script berhasil.');
        $this->line('Nama file: '.$result['payment_proof_name']);
        $this->line('URL file : '.$result['payment_proof_url']);

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
})->purpose('Test upload bukti pembayaran via Google Apps Script');

Artisan::command('legacy:import-transaksi {--account= : Nama cash account untuk sumber kas} {--dry-run : Tampilkan ringkasan tanpa insert}', function () {
    $dryRun = (bool) $this->option('dry-run');
    $accountName = $this->option('account');

    if (! Schema::hasTable('kategori_transaksi') || ! Schema::hasTable('transaksi')) {
        $this->error('Tabel legacy tidak ditemukan: butuh tabel `kategori_transaksi` dan `transaksi`.');

        return self::FAILURE;
    }

    if (! Schema::hasTable('expense_categories') || ! Schema::hasTable('expenses') || ! Schema::hasTable('cash_accounts') || ! Schema::hasTable('cash_ledger_entries')) {
        $this->error('Schema baru belum ada. Jalankan dulu `php artisan migrate` untuk membuat tabel baru.');

        return self::FAILURE;
    }

    $accountId = DB::table('cash_accounts')
        ->when(is_string($accountName) && $accountName !== '', fn ($q) => $q->where('name', $accountName))
        ->orderByDesc('is_active')
        ->orderBy('id')
        ->value('id');

    if (! $accountId && ! $dryRun) {
        $accountId = DB::table('cash_accounts')->insertGetId([
            'name' => is_string($accountName) && $accountName !== '' ? $accountName : 'Kas Utama',
            'type' => 'cash',
            'account_number' => null,
            'account_holder' => 'MAN 2 Surakarta',
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->info('Cash account belum ada. Dibuat otomatis: '.DB::table('cash_accounts')->where('id', $accountId)->value('name'));
    }

    $categories = DB::table('kategori_transaksi')->select(['id', 'nama_kategori', 'tipe'])->get();
    $expenseCategoryMap = [];

    $makeCode = function (string $name): string {
        $code = strtoupper(preg_replace('/[^A-Za-z0-9]+/', '_', trim($name)) ?: 'CAT');
        $code = trim($code, '_');
        $code = substr($code, 0, 30);

        return $code !== '' ? $code : 'CAT';
    };

    foreach ($categories as $cat) {
        if ($cat->tipe !== 'pengeluaran') {
            continue;
        }

        if ($dryRun) {
            $expenseCategoryMap[$cat->id] = 1;
            continue;
        }

        $codeBase = $makeCode((string) $cat->nama_kategori);
        $code = $codeBase;
        $suffix = 1;

        while (DB::table('expense_categories')->where('code', $code)->exists()) {
            $suffix++;
            $code = substr($codeBase, 0, max(1, 30 - (strlen((string) $suffix) + 1))) . '_' . $suffix;
        }

        DB::table('expense_categories')->updateOrInsert(
            ['name' => $cat->nama_kategori],
            ['code' => $code, 'is_active' => 1, 'updated_at' => now(), 'created_at' => now()],
        );

        $expenseCategoryId = DB::table('expense_categories')->where('name', $cat->nama_kategori)->value('id');
        $expenseCategoryMap[$cat->id] = $expenseCategoryId;
    }

    $total = (int) DB::table('transaksi')->count();
    $this->info("Ditemukan {$total} transaksi legacy.");

    $importedExpenses = 0;
    $importedLedgerIn = 0;
    $skipped = 0;

    $rows = DB::table('transaksi')
        ->select(['id', 'no_referensi', 'tanggal', 'deskripsi_kegiatan', 'nominal', 'bukti_nota', 'kategori_id', 'submitter_id'])
        ->orderBy('id')
        ->get();

    foreach ($rows as $row) {
        $cat = $categories->firstWhere('id', $row->kategori_id);
        $tipe = $cat?->tipe;

        if ($tipe === 'pengeluaran') {
            $expenseNo = (string) $row->no_referensi;
            $already = DB::table('expenses')->where('expense_no', $expenseNo)->exists();

            if ($already) {
                $skipped++;
                continue;
            }

            if (! array_key_exists($row->kategori_id, $expenseCategoryMap) || ! $expenseCategoryMap[$row->kategori_id]) {
                $this->warn("Kategori pengeluaran tidak termap untuk transaksi #{$row->id}.");
                $skipped++;
                continue;
            }

            if ($dryRun) {
                $importedExpenses++;
                continue;
            }

            $expenseId = DB::table('expenses')->insertGetId([
                'expense_no' => $expenseNo,
                'transaction_date' => $row->tanggal,
                'category_id' => $expenseCategoryMap[$row->kategori_id],
                'payment_account_id' => $accountId,
                'amount' => (int) round((float) $row->nominal),
                'description' => $row->deskripsi_kegiatan,
                'attachment_path' => $row->bukti_nota,
                'status' => 'posted',
                'created_by' => $row->submitter_id,
                'updated_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('cash_ledger_entries')->insert([
                'entry_no' => DocumentNumber::next('LED', \App\Models\CashLedgerEntry::class, 'entry_no', $row->tanggal),
                'transaction_date' => $row->tanggal,
                'account_id' => $accountId,
                'direction' => 'out',
                'source_type' => 'expense',
                'source_id' => $expenseId,
                'amount' => (int) round((float) $row->nominal),
                'description' => $row->deskripsi_kegiatan,
                'status' => 'posted',
                'created_by' => $row->submitter_id,
            ]);

            $importedExpenses++;

            continue;
        }

        if ($tipe === 'pemasukan') {
            $already = DB::table('cash_ledger_entries')
                ->where('source_type', 'legacy_transaksi')
                ->where('source_id', $row->id)
                ->exists();

            if ($already) {
                $skipped++;
                continue;
            }

            if ($dryRun) {
                $importedLedgerIn++;
                continue;
            }

            $categoryLabel = $cat?->nama_kategori ? ('['.$cat->nama_kategori.'] ') : '';

            DB::table('cash_ledger_entries')->insert([
                'entry_no' => DocumentNumber::next('LED', \App\Models\CashLedgerEntry::class, 'entry_no', $row->tanggal),
                'transaction_date' => $row->tanggal,
                'account_id' => $accountId,
                'direction' => 'in',
                'source_type' => 'legacy_transaksi',
                'source_id' => $row->id,
                'amount' => (int) round((float) $row->nominal),
                'description' => $categoryLabel.$row->deskripsi_kegiatan.' (ref: '.$row->no_referensi.')',
                'status' => 'posted',
                'created_by' => $row->submitter_id,
            ]);

            $importedLedgerIn++;
            continue;
        }

        $skipped++;
    }

    $this->line('---');
    $this->line('Ringkasan import:');
    $this->line('Pengeluaran -> expenses: '.$importedExpenses.($dryRun ? ' (dry-run)' : ''));
    $this->line('Pemasukan -> cash_ledger_entries: '.$importedLedgerIn.($dryRun ? ' (dry-run)' : ''));
    $this->line('Skip (sudah ada / tidak termap): '.$skipped);

    return self::SUCCESS;
})->purpose('Import data legacy dari tabel transaksi/kategori_transaksi ke schema baru');

Artisan::command('legacy:fix-passwords {--dry-run : Tampilkan user yang akan diperbaiki}', function () {
    $dryRun = (bool) $this->option('dry-run');

    if (! Schema::hasTable('users')) {
        $this->error('Tabel users tidak ditemukan.');

        return self::FAILURE;
    }

    $users = DB::table('users')->select(['id', 'username', 'email', 'password'])->orderBy('id')->get();
    $fixed = 0;

    foreach ($users as $user) {
        $password = (string) ($user->password ?? '');

        $looksHashed = str_starts_with($password, '$2y$')
            || str_starts_with($password, '$2a$')
            || str_starts_with($password, '$argon2');

        if ($looksHashed) {
            continue;
        }

        $label = $user->username ?: ($user->email ?: ('user#'.$user->id));
        $this->line("Fix password untuk: {$label}");

        if (! $dryRun) {
            DB::table('users')->where('id', $user->id)->update([
                'password' => Hash::make($password !== '' ? $password : 'password123'),
                'updated_at' => now(),
            ]);
        }

        $fixed++;
    }

    $this->info("Selesai. User diperbaiki: {$fixed}".($dryRun ? ' (dry-run)' : ''));

    return self::SUCCESS;
})->purpose('Hash ulang password legacy yang masih plain text');
