<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('class_exam', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes', 'class_id')->onDelete('cascade');
            $table->foreignId('exam_id')->constrained('exams', 'exam_id')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('class_exam');
        Schema::enableForeignKeyConstraints();
    }
};
