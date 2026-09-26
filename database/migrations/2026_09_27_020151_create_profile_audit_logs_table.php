<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profile_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // ID of user whose profile changed
            $table->string('user_name');
            $table->string('user_role'); // Lecturer, Student, Admin
            $table->string('changed_field'); // e.g., Name, Email, Staff ID
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->string('changed_by_name'); // Who performed the update
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_audit_logs');
    }
};