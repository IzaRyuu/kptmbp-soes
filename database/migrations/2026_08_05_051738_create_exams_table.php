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
        Schema::create('exams', function (Blueprint $table) {
            // Primary Key (Auto Increment)
            $table->id('exam_id');

            // Foreign Key to Lecturers/Users
            $table->foreignId('lecturer_id')
                  ->constrained('lecturers', 'lecturer_id')
                  ->onDelete('cascade');

            // Foreign Key to Courses (Nullable in case exam isn't tied to a specific course)
            // Make course_id nullable
            $table->foreignId('course_id')
                ->nullable() // <--- THIS ALLOWS NULL VALUES
                ->constrained('courses', 'course_id')
                ->nullOnDelete();

            // Foreign Key to Classes (Nullable so exam remains if class is deleted)
            $table->foreignId('class_id')
                  ->nullable()
                  ->constrained('classes', 'class_id')
                  ->nullOnDelete();

            // Exam Details
            $table->string('title');
            $table->integer('duration_minutes')->default(60);
            $table->dateTime('start_time')->nullable();
            $table->dateTime('end_time')->nullable();
            $table->boolean('is_randomized')->default(false);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};