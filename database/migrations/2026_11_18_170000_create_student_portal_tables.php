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
        // 1. Homework / Assignments Table
        Schema::create('homeworks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignId('academic_class_id')->constrained('academic_classes')->cascadeOnDelete();
            $table->foreignId('section_id')->nullable()->constrained('sections')->nullOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->string('subject'); // e.g. English, Mathematics, Science, ICT
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('due_date');
            $table->integer('total_marks')->default(100);
            $table->string('status')->default('due'); // due, overdue, submitted, completed
            $table->string('color_theme')->default('orange'); // orange, blue, amber, teal
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });

        // 2. Exam Schedules / Exam Routine Table
        Schema::create('exam_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignId('academic_class_id')->constrained('academic_classes')->cascadeOnDelete();
            $table->string('subject');
            $table->date('exam_date');
            $table->string('start_time'); // e.g. 10:00 AM
            $table->string('end_time');   // e.g. 01:00 PM
            $table->string('room')->nullable();
            $table->integer('marks')->default(100);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_schedules');
        Schema::dropIfExists('homeworks');
    }
};
