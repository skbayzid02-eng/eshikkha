<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institution_id')->constrained('institutions')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->foreignId('academic_class_id')->constrained('academic_classes')->cascadeOnDelete();
            $table->foreignId('section_id')->nullable()->constrained('sections')->nullOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->string('subject_name');
            $table->integer('period_number')->nullable();
            $table->date('date');
            $table->boolean('class_conducted')->default(true);
            $table->text('cancellation_reason')->nullable(); // ক্লাস না হলে কারণ
            $table->text('topics_covered')->nullable();      // আজ কী পড়ানো হলো
            $table->text('homework_assigned')->nullable();   // হোমওয়ার্ক
            $table->integer('present_count')->nullable();
            $table->integer('absent_count')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_reports');
    }
};
