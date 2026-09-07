<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('questions', 'correct_answer_text')) {
            Schema::table('questions', function (Blueprint $table) {
                $table->text('correct_answer_text')->nullable()->after('correct_answer');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('questions', 'correct_answer_text')) {
            Schema::table('questions', function (Blueprint $table) {
                $table->dropColumn('correct_answer_text');
            });
        }
    }
};