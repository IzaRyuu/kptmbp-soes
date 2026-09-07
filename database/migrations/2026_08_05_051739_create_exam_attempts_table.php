<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_attempts', function (Blueprint $table) {

            $table->id('attempt_id');

            $table->foreignId('exam_id')
                ->constrained('exams', 'exam_id')
                ->onDelete('cascade');

            $table->foreignId('student_id')
                ->constrained('students', 'student_id')
                ->onDelete('cascade');

            $table->timestamp('started_at')->nullable();

            $table->timestamp('submitted_at')->nullable();

            $table->enum('status', [
                'in_progress',
                'submitted'
            ])->default('in_progress');

            $table->integer('total_score')->default(0);

            $table->timestamps();

            // Prevent the same student from submitting the same exam twice
            $table->unique(['exam_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_attempts');
    }
};