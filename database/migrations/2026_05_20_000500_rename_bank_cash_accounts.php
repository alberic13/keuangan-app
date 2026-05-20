<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('cash_accounts')
            ->where('name', 'Bank BSI Operasional')
            ->update([
                'name' => 'BSI - 5250005255',
                'type' => 'bank',
                'account_number' => '5250005255',
                'account_holder' => 'Komite MAN 2 Surakarta',
                'updated_at' => now(),
            ]);

        DB::table('cash_accounts')
            ->where('name', 'Bank Mandiri Komite')
            ->update([
                'name' => 'Danamon - 003640599068',
                'type' => 'bank',
                'account_number' => '003640599068',
                'account_holder' => 'Komite Madrasah aliyah negeri 2 surakarta',
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        DB::table('cash_accounts')
            ->where('name', 'BSI - 5250005255')
            ->update([
                'name' => 'Bank BSI Operasional',
                'type' => 'bank',
                'account_number' => '7123456789',
                'account_holder' => 'MAN 2 Surakarta',
                'updated_at' => now(),
            ]);

        DB::table('cash_accounts')
            ->where('name', 'Danamon - 003640599068')
            ->update([
                'name' => 'Bank Mandiri Komite',
                'type' => 'bank',
                'account_number' => '1450099990',
                'account_holder' => 'Komite MAN 2 Surakarta',
                'updated_at' => now(),
            ]);
    }
};