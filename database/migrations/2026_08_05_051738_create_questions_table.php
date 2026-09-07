<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Questions Table
        Schema::create('questions', function (Blueprint $table) {
            $table->id('question_id');
            $table->foreignId('exam_id')->constrained('exams', 'exam_id')->onDelete('cascade');
            $table->text('question_text');
            $table->enum('question_type', ['mcq', 'short_answer']);
            $table->integer('points')->default(1);
            $table->text('correct_answer_text')->nullable(); // Reference answer for short answers
            $table->timestamps();
        });

        // 2. Question Options Table (For MCQs)
        Schema::create('question_options', function (Blueprint $table) {
            $table->id('option_id');
            $table->foreignId('question_id')->constrained('questions', 'question_id')->onDelete('cascade');
            $table->string('option_text');
            $table->boolean('is_correct')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('question_options');
        Schema::dropIfExists('questions');
    }
};