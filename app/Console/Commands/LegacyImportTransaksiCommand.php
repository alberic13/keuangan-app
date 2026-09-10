<?php

namespace App\Console\Commands;

use App\Models\CashLedgerEntry;
use App\Support\DocumentNumber;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LegacyImportTransaksiCommand extends Command
{
    protected $signature = 'legacy:import-transaksi {--account= : Nama cash account untuk sumber kas} {--dry-run : Tampilkan ringkasan tanpa insert}';
    protected $description = 'Import data legacy dari tabel transaksi/kategori_transaksi ke schema baru';

    public function handle(): int
    {
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

        $accountId = $this->resolveAccountId($accountName, $dryRun);
        $categories = DB::table('kategori_transaksi')->select(['id', 'nama_kategori', 'tipe'])->get();
        $expenseCategoryMap = $this->buildCategoryMap($categories, $dryRun);

        $total = (int) DB::table('transaksi')->count();
        $this->info("Ditemukan {$total} transaksi legacy.");

        [$importedExpenses, $importedLedgerIn, $skipped] = $this->importRows($categories, $expenseCategoryMap, $accountId, $dryRun);

        $this->line('---');
        $this->line('Ringkasan import:');
        $this->line('Pengeluaran -> expenses: ' . $importedExpenses . ($dryRun ? ' (dry-run)' : ''));
        $this->line('Pemasukan -> cash_ledger_entries: ' . $importedLedgerIn . ($dryRun ? ' (dry-run)' : ''));
        $this->line('Skip (sudah ada / tidak termap): ' . $skipped);

        return self::SUCCESS;
    }

    protected function resolveAccountId(?string $accountName, bool $dryRun): ?int
    {
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

            $this->info('Cash account belum ada. Dibuat otomatis: ' . DB::table('cash_accounts')->where('id', $accountId)->value('name'));
        }

        return $accountId;
    }

    protected function buildCategoryMap(Collection $categories, bool $dryRun): array
    {
        $expenseCategoryMap = [];

        foreach ($categories as $cat) {
            if ($cat->tipe !== 'pengeluaran') {
                continue;
            }

            if ($dryRun) {
                $expenseCategoryMap[$cat->id] = 1;
                continue;
            }

            $codeBase = strtoupper(preg_replace('/[^A-Za-z0-9]+/', '_', trim((string) $cat->nama_kategori)) ?: 'CAT');
            $codeBase = substr(trim($codeBase, '_'), 0, 30) ?: 'CAT';
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

            $expenseCategoryMap[$cat->id] = DB::table('expense_categories')->where('name', $cat->nama_kategori)->value('id');
        }

        return $expenseCategoryMap;
    }

    protected function importRows(Collection $categories, array $expenseCategoryMap, ?int $accountId, bool $dryRun): array
    {
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
                if (DB::table('expenses')->where('expense_no', $expenseNo)->exists() || empty($expenseCategoryMap[$row->kategori_id])) {
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
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('cash_ledger_entries')->insert([
                    'entry_no' => DocumentNumber::next('LED', CashLedgerEntry::class, 'entry_no', $row->tanggal),
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
                if (DB::table('cash_ledger_entries')->where('source_type', 'legacy_transaksi')->where('source_id', $row->id)->exists()) {
                    $skipped++;
                    continue;
                }

                if ($dryRun) {
                    $importedLedgerIn++;
                    continue;
                }

                $categoryLabel = $cat?->nama_kategori ? ('[' . $cat->nama_kategori . '] ') : '';
                DB::table('cash_ledger_entries')->insert([
                    'entry_no' => DocumentNumber::next('LED', CashLedgerEntry::class, 'entry_no', $row->tanggal),
                    'transaction_date' => $row->tanggal,
                    'account_id' => $accountId,
                    'direction' => 'in',
                    'source_type' => 'legacy_transaksi',
                    'source_id' => $row->id,
                    'amount' => (int) round((float) $row->nominal),
                    'description' => $categoryLabel . $row->deskripsi_kegiatan . ' (ref: ' . $row->no_referensi . ')',
                    'status' => 'posted',
                    'created_by' => $row->submitter_id,
                ]);

                $importedLedgerIn++;
                continue;
            }

            $skipped++;
        }

        return [$importedExpenses, $importedLedgerIn, $skipped];
    }
}
