<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_student', function (Blueprint $table) {

            $table->foreignId('class_id')
                ->constrained('classes', 'class_id')
                ->onDelete('cascade');

            $table->foreignId('student_id')
                ->constrained('students', 'student_id')
                ->onDelete('cascade');

            $table->primary(['class_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_student');
    }
};