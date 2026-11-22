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
        // 1. Institutions (Schools, Colleges, Universities, Madrasahs)
        Schema::create('institutions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('code')->unique(); // e.g., DRMC-101, TMKM-202
            $table->string('eiin')->nullable()->index();
            $table->string('type'); // school, college, university, madrasah
            $table->string('division'); // Dhaka, Chittagong, Rajshahi, etc.
            $table->string('district');
            $table->text('address')->nullable();
            $table->string('phone');
            $table->string('email');
            $table->string('website')->nullable();
            $table->string('logo')->nullable();
            $table->string('status')->default('active'); // active, pending, suspended
            $table->string('subscription_plan')->default('standard'); // starter, standard, premium, enterprise
            $table->integer('student_capacity')->default(1000);
            $table->timestamps();
        });

        // 2. Extend Users table
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('institution_id')->nullable()->constrained('institutions')->nullOnDelete()->after('id');
            $table->string('role')->default('student')->after('email'); // super_admin, institution_admin, teacher, student, guardian
            $table->string('phone')->nullable()->after('role');
            $table->string('avatar')->nullable()->after('phone');
            $table->string('status')->default('active')->after('avatar');
        });

        // 3. Academic Classes / Grades / Programs
        Schema::create('academic_classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->string('name'); // Class 6, Class 10, Alim 1st Year, HSC Science, B.Sc CSE
            $table->integer('numeric_level')->default(1);
            $table->string('medium')->default('bangla'); // bangla, english, arabic
            $table->timestamps();
        });

        // 4. Sections & Shifts
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignId('academic_class_id')->constrained('academic_classes')->cascadeOnDelete();
            $table->string('name'); // Section A, Padma, Meghna, Group A
            $table->string('shift')->default('morning'); // morning, day
            $table->string('room_number')->nullable();
            $table->timestamps();
        });

        // 5. Teachers
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('employee_id');
            $table->string('name');
            $table->string('designation'); // Senior Teacher, Lecturer, Assistant Professor, Muhtamim
            $table->string('department'); // Mathematics, Physics, Quran & Hadith, English, Bangla
            $table->string('phone');
            $table->string('email');
            $table->string('qualification')->nullable();
            $table->date('joining_date')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        // 6. Students
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('academic_class_id')->constrained('academic_classes')->cascadeOnDelete();
            $table->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
            $table->string('admission_number');
            $table->integer('roll_number');
            $table->string('name');
            $table->string('gender')->default('male'); // male, female
            $table->date('dob')->nullable();
            $table->string('blood_group')->nullable();
            $table->string('guardian_name');
            $table->string('guardian_phone');
            $table->string('guardian_relation')->default('father');
            $table->text('address')->nullable();
            $table->string('status')->default('active'); // active, transferred, graduated
            $table->timestamps();
        });

        // 7. Attendances
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignId('student_id')->nullable()->constrained('students')->cascadeOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained('teachers')->cascadeOnDelete();
            $table->date('date');
            $table->string('status')->default('present'); // present, absent, late, excused
            $table->string('remarks')->nullable();
            $table->timestamps();
        });

        // 8. Exams
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignId('academic_class_id')->constrained('academic_classes')->cascadeOnDelete();
            $table->string('title'); // 1st Term Exam 2026, Half Yearly, Dakhil Model Test
            $table->date('start_date');
            $table->date('end_date');
            $table->string('session_year')->default('2026');
            $table->timestamps();
        });

        // 9. Exam Results
        Schema::create('exam_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignId('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('subject_name');
            $table->integer('total_marks')->default(100);
            $table->float('obtained_marks');
            $table->string('grade'); // A+, A, A-, B, C, D, F
            $table->float('grade_point'); // 5.00, 4.00, etc.
            $table->timestamps();
        });

        // 10. Fees & Invoices
        Schema::create('fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('invoice_no')->unique();
            $table->string('title'); // Tuition Fee - October, Admission Fee, Exam Fee
            $table->decimal('amount', 10, 2);
            $table->date('due_date');
            $table->string('status')->default('unpaid'); // unpaid, paid, partial
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->string('payment_method')->nullable(); // bKash, Nagad, Rocket, Cash
            $table->string('transaction_id')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        // 11. Notices
        Schema::create('notices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->nullable()->constrained('institutions')->cascadeOnDelete();
            $table->string('title');
            $table->text('content');
            $table->string('audience')->default('all'); // all, institutions, teachers, students, guardians
            $table->date('published_at');
            $table->boolean('is_pinned')->default(false);
            $table->timestamps();
        });

        // 12. Class Routines
        Schema::create('class_routines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignId('academic_class_id')->constrained('academic_classes')->cascadeOnDelete();
            $table->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->string('day'); // Saturday, Sunday, Monday, etc.
            $table->string('subject');
            $table->string('start_time'); // 09:00 AM
            $table->string('end_time'); // 09:45 AM
            $table->string('room')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('class_routines');
        Schema::dropIfExists('notices');
        Schema::dropIfExists('fees');
        Schema::dropIfExists('exam_results');
        Schema::dropIfExists('exams');
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('students');
        Schema::dropIfExists('teachers');
        Schema::dropIfExists('sections');
        Schema::dropIfExists('academic_classes');
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['institution_id']);
            $table->dropColumn(['institution_id', 'role', 'phone', 'avatar', 'status']);
        });
        Schema::dropIfExists('institutions');
    }
};
