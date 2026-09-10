<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class LegacyFixPasswordsCommand extends Command
{
    protected $signature = 'legacy:fix-passwords {--dry-run : Tampilkan user yang akan diperbaiki}';
    protected $description = 'Hash ulang password legacy yang masih plain text';

    public function handle(): int
    {
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

            $label = $user->username ?: ($user->email ?: ('user#' . $user->id));
            $this->line("Fix password untuk: {$label}");

            if (! $dryRun) {
                DB::table('users')->where('id', $user->id)->update([
                    'password' => Hash::make($password !== '' ? $password : 'password123'),
                    'updated_at' => now(),
                ]);
            }

            $fixed++;
        }

        $this->info("Selesai. User diperbaiki: {$fixed}" . ($dryRun ? ' (dry-run)' : ''));

        return self::SUCCESS;
    }
}
