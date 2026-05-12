<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        foreach ([
            ['slug' => 'regular', 'label' => 'Reguler'],
            ['slug' => 'full_day', 'label' => 'Full Day'],
            ['slug' => 'boarding', 'label' => 'Asrama'],
        ] as $type) {
            DB::table('student_types')->updateOrInsert(
                ['slug' => $type['slug']],
                [
                    'label' => $type['label'],
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            );
        }

        DB::table('student_types')
            ->whereNotIn('slug', ['regular', 'full_day', 'boarding'])
            ->update(['is_active' => false, 'updated_at' => $now]);

        DB::table('fee_types')->where('category', 'spp')->update([
            'installment_allowed' => false,
            'billing_frequency' => 'monthly',
            'applies_to' => 'all',
            'updated_at' => $now,
        ]);

        DB::table('fee_types')->where('category', 'activity')->update([
            'installment_allowed' => true,
            'billing_frequency' => 'one_time',
            'applies_to' => 'full_day',
            'updated_at' => $now,
        ]);

        DB::table('fee_types')->where('category', 'meal')->update([
            'installment_allowed' => false,
            'billing_frequency' => 'monthly',
            'applies_to' => 'boarding',
            'updated_at' => $now,
        ]);

        $spp = DB::table('fee_types')->where('code', 'SPP')->first();

        if ($spp) {
            DB::table('fee_schemes')
                ->where('fee_type_id', $spp->id)
                ->whereNotNull('batch_id')
                ->delete();

            DB::table('fee_schemes')->updateOrInsert(
                [
                    'fee_type_id' => $spp->id,
                    'batch_id' => null,
                    'effective_start' => '2026-01-01',
                ],
                [
                    'nominal' => 400000,
                    'effective_end' => null,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            );
        }

        DB::table('majors')
            ->whereIn('code', ['BHS', 'BAHASA'])
            ->update(['is_active' => false, 'updated_at' => $now]);
    }

    public function down(): void
    {
        DB::table('student_types')
            ->where('slug', 'full_day')
            ->update(['is_active' => false, 'updated_at' => now()]);
    }
};
