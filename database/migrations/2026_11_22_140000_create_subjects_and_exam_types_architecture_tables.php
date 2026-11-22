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
        // 1. Exam Types table (পরীক্ষার ধরন)
        Schema::create('exam_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->string('name'); // e.g. Half Yearly, Annual, Class Test, Model Test
            $table->string('code')->nullable(); // e.g. half_yearly, annual, class_test
            $table->float('weight_percentage')->default(100.0);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 2. Subjects table (বিষয়)
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignId('academic_class_id')->nullable()->constrained('academic_classes')->cascadeOnDelete();
            $table->string('name'); // e.g. বাংলা, ইংরেজি, গণিত
            $table->string('code')->nullable();
            $table->float('full_marks')->default(100.0);
            $table->float('pass_marks')->default(33.0);
            $table->string('status')->default('active');
            $table->timestamps();
        });

        // 3. Add exam_type_id to exams table
        Schema::table('exams', function (Blueprint $table) {
            $table->foreignId('exam_type_id')->nullable()->after('academic_class_id')->constrained('exam_types')->nullOnDelete();
        });

        // 4. Add subject_id and exam_type_id to exam_results table
        Schema::table('exam_results', function (Blueprint $table) {
            $table->foreignId('exam_type_id')->nullable()->after('exam_id')->constrained('exam_types')->nullOnDelete();
            $table->foreignId('subject_id')->nullable()->after('student_id')->constrained('subjects')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_results', function (Blueprint $table) {
            $table->dropConstrainedForeignId('subject_id');
            $table->dropConstrainedForeignId('exam_type_id');
        });

        Schema::table('exams', function (Blueprint $table) {
            $table->dropConstrainedForeignId('exam_type_id');
        });

        Schema::dropIfExists('subjects');
        Schema::dropIfExists('exam_types');
    }
};
