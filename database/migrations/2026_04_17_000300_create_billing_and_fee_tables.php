<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->string('category', 30);
            $table->boolean('installment_allowed')->default(false);
            $table->string('billing_frequency', 20)->default('monthly');
            $table->string('applies_to', 20)->default('all');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('fee_schemes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fee_type_id')->constrained('fee_types');
            $table->foreignId('batch_id')->nullable()->constrained('batches');
            $table->unsignedBigInteger('nominal');
            $table->date('effective_start');
            $table->date('effective_end')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['fee_type_id', 'batch_id']);
            $table->index(['effective_start', 'effective_end']);
        });

        Schema::create('billing_cycles', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('month');
            $table->unsignedSmallInteger('year');
            $table->string('period_label');
            $table->date('due_date');
            $table->string('status', 20)->default('open');
            $table->timestamps();

            $table->unique(['month', 'year']);
            $table->index(['status', 'due_date']);
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no')->unique();
            $table->foreignId('student_id')->constrained('students');
            $table->foreignId('fee_type_id')->constrained('fee_types');
            $table->foreignId('billing_cycle_id')->nullable()->constrained('billing_cycles');
            $table->string('reference_name')->nullable();
            $table->unsignedBigInteger('total_amount');
            $table->unsignedBigInteger('paid_amount')->default(0);
            $table->unsignedBigInteger('outstanding_amount');
            $table->string('status', 20)->default('unpaid');
            $table->timestamp('published_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->index(['student_id', 'status']);
            $table->index(['fee_type_id', 'billing_cycle_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('billing_cycles');
        Schema::dropIfExists('fee_schemes');
        Schema::dropIfExists('fee_types');
    }
};
