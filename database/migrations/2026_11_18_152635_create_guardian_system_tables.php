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
        // 1. Guardian - Student Relationship Mapping (Supports Multi-Child across different institutions & multiple guardians per child)
        Schema::create('guardian_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guardian_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('relationship')->default('father'); // father, mother, legal_guardian
            $table->string('status')->default('approved'); // approved, pending, rejected
            $table->string('verification_otp')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->unique(['guardian_user_id', 'student_id']);
        });

        // 2. Student & Guardian Messages
        Schema::create('student_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->string('sender_name');
            $table->string('sender_role')->default('teacher'); // teacher, principal, admin
            $table->string('title');
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_messages');
        Schema::dropIfExists('guardian_students');
    }
};
