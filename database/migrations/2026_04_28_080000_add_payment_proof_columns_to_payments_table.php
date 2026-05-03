<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('payment_proof_drive_id')->nullable()->after('bank_reference');
            $table->string('payment_proof_name')->nullable()->after('payment_proof_drive_id');
            $table->string('payment_proof_mime_type')->nullable()->after('payment_proof_name');
            $table->text('payment_proof_url')->nullable()->after('payment_proof_mime_type');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'payment_proof_drive_id',
                'payment_proof_name',
                'payment_proof_mime_type',
                'payment_proof_url',
            ]);
        });
    }
};
