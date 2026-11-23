<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. স্কুলে প্রবেশের উপস্থিতি (Gate / Campus Entry & Exit Attendance)
        Schema::create('gate_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->date('date');
            $table->time('entry_time')->nullable();
            $table->time('exit_time')->nullable();
            $table->string('status')->default('present'); // present, late, absent, left_early
            $table->string('gate_number')->nullable();
            $table->string('device_id')->nullable();
            $table->string('remarks')->nullable();
            $table->timestamps();

            $table->unique(['institution_id', 'student_id', 'date']);
        });

        // 2. ক্লাসভিত্তিক উপস্থিতি (Subject / Period / Classroom Attendance)
        Schema::create('class_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('academic_class_id')->constrained('academic_classes')->cascadeOnDelete();
            $table->foreignId('section_id')->nullable()->constrained('sections')->nullOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
            $table->integer('period_number')->nullable();
            $table->string('subject_name')->nullable();
            $table->date('date');
            $table->string('status')->default('present'); // present, absent, late, excused
            $table->string('remarks')->nullable();
            $table->timestamps();

            $table->index(['institution_id', 'academic_class_id', 'section_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_attendances');
        Schema::dropIfExists('gate_attendances');
    }
};
