<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['major_id']);
            $table->index(['batch_id', 'class_id']);
            $table->dropIndex(['batch_id', 'class_id', 'major_id']);
            $table->dropColumn('major_id');
        });

        Schema::dropIfExists('majors');
    }

    public function down(): void
    {
        Schema::create('majors', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->string('name', 100);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('students', function (Blueprint $table) {
            $table->foreignId('major_id')->nullable()->constrained('majors');
            $table->index(['batch_id', 'class_id', 'major_id']);
        });
    }
};
