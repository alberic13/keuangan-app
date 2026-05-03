<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type', 20)->default('cash');
            $table->string('account_number')->nullable();
            $table->string('account_holder')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_no')->unique();
            $table->foreignId('student_id')->constrained('students');
            $table->date('payment_date');
            $table->string('method', 20);
            $table->foreignId('cash_account_id')->constrained('cash_accounts');
            $table->unsignedBigInteger('total_amount');
            $table->string('bank_reference')->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('posted');
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('edited_by')->nullable()->constrained('users');
            $table->text('edited_reason')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'payment_date']);
            $table->index(['method', 'status']);
        });

        Schema::create('payment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained('payments')->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained('invoices');
            $table->unsignedBigInteger('amount');
            $table->timestamps();

            $table->index(['invoice_id']);
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('expense_no')->unique();
            $table->date('transaction_date');
            $table->foreignId('category_id')->constrained('expense_categories');
            $table->foreignId('payment_account_id')->constrained('cash_accounts');
            $table->unsignedBigInteger('amount');
            $table->text('description');
            $table->string('attachment_path')->nullable();
            $table->string('status', 20)->default('posted');
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
        });

        Schema::create('cash_ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->string('entry_no')->unique();
            $table->date('transaction_date');
            $table->foreignId('account_id')->constrained('cash_accounts');
            $table->string('direction', 10);
            $table->string('source_type', 30);
            $table->unsignedBigInteger('source_id');
            $table->unsignedBigInteger('amount');
            $table->text('description');
            $table->string('status', 20)->default('posted');
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->index(['account_id', 'transaction_date']);
            $table->index(['source_type', 'source_id']);
            $table->index(['direction']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_ledger_entries');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('payment_items');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('expense_categories');
        Schema::dropIfExists('cash_accounts');
    }
};
