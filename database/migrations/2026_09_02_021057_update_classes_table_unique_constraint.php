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
        Schema::table('classes', function (Blueprint $table) {
            // Drop the existing single-column unique constraint
            $table->dropUnique('classes_class_code_unique');

            // Add a composite unique index for class_code and lecturer_id
            $table->unique(['class_code', 'lecturer_id']);
        });
    }

    public function down(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->dropUnique(['class_code', 'lecturer_id']);
            $table->unique('class_code');
        });
    }
};
