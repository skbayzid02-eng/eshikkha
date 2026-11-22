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
        // 1. Add extra columns to exam_results for Internal Marks, Credits & Breakdown
        Schema::table('exam_results', function (Blueprint $table) {
            $table->integer('credits')->default(3)->after('subject_name');
            $table->float('theory_marks')->default(0)->after('total_marks');
            $table->float('practical_marks')->default(0)->after('theory_marks');
            $table->float('internal_marks')->default(0)->after('practical_marks');
            $table->float('highest_marks')->default(0)->after('obtained_marks');
        });

        // 2. Student Exam Overall Summaries (Merit position, CGPA/GPA, Total Marks, etc.)
        Schema::create('student_exam_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->float('total_marks_possible')->default(500);
            $table->float('total_marks_obtained')->default(0);
            $table->float('percentage')->default(0);
            $table->float('gpa')->default(5.00);
            $table->float('cgpa')->nullable();
            $table->string('grade')->default('A+');
            $table->integer('section_merit_rank')->default(1);
            $table->integer('class_merit_rank')->default(1);
            $table->integer('total_students_section')->default(45);
            $table->integer('total_students_class')->default(180);
            $table->string('status')->default('promoted'); // promoted, passed, failed
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->unique(['exam_id', 'student_id']);
        });

        // 3. Subject Internal Assessment Details (Assignments, Sessionals matching screenshot)
        Schema::create('subject_internal_marks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_result_id')->constrained('exam_results')->cascadeOnDelete();
            $table->string('item_name'); // e.g. Assignment 1, Sessional 1
            $table->float('marks_obtained');
            $table->float('total_marks');
            $table->float('attendance_rate')->default(85.0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subject_internal_marks');
        Schema::dropIfExists('student_exam_summaries');
        Schema::table('exam_results', function (Blueprint $table) {
            $table->dropColumn(['credits', 'theory_marks', 'practical_marks', 'internal_marks', 'highest_marks']);
        });
    }
};
