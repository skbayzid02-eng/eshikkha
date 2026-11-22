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
        Schema::table('class_routines', function (Blueprint $table) {
            $table->string('class_name')->nullable()->after('academic_class_id');
            $table->string('section_name')->nullable()->after('section_id');
            $table->string('teacher_name')->nullable()->after('teacher_id');
            $table->boolean('is_break')->default(false)->after('room');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('class_routines', function (Blueprint $table) {
            $table->dropColumn(['class_name', 'section_name', 'teacher_name', 'is_break']);
        });
    }
};
