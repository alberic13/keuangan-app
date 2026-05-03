<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_logs', function (Blueprint $table) {
            $table->id();
            $table->string('type', 50);
            $table->string('file_name');
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('success_rows')->default(0);
            $table->unsignedInteger('failed_rows')->default(0);
            $table->foreignId('imported_by')->nullable()->constrained('users');
            $table->json('metadata_json')->nullable();
            $table->timestamps();
        });

        Schema::create('import_log_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_log_id')->constrained('import_logs')->cascadeOnDelete();
            $table->unsignedInteger('row_number');
            $table->json('payload_json')->nullable();
            $table->string('status', 20);
            $table->text('error_message')->nullable();
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users');
            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->string('action', 50);
            $table->text('reason')->nullable();
            $table->json('before_json')->nullable();
            $table->json('after_json')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['entity_type', 'entity_id']);
            $table->index(['actor_id', 'action']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('import_log_rows');
        Schema::dropIfExists('import_logs');
    }
};
