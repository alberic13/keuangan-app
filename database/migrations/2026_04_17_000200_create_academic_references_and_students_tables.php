<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->string('year_label', 20);
            $table->string('academic_year', 30);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('level', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('majors', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 100);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('nis', 50)->nullable()->unique();
            $table->string('nisn', 50)->nullable()->unique();
            $table->string('full_name');
            $table->foreignId('class_id')->constrained('classes');
            $table->foreignId('major_id')->constrained('majors');
            $table->foreignId('batch_id')->constrained('batches');
            $table->string('student_type', 20)->default('regular');
            $table->boolean('is_active')->default(true);
            $table->date('enrollment_date')->nullable();
            $table->date('exit_date')->nullable();
            $table->timestamps();

            $table->index(['batch_id', 'class_id', 'major_id']);
            $table->index(['student_type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
        Schema::dropIfExists('majors');
        Schema::dropIfExists('classes');
        Schema::dropIfExists('batches');
    }
};
