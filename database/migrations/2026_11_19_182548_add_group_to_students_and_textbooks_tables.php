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
        Schema::table('students', function (Blueprint $table) {
            $table->string('group')->nullable()->after('academic_class_id'); // 'science', 'humanities', 'business'
        });

        Schema::table('textbooks', function (Blueprint $table) {
            $table->string('group')->default('general')->after('subject'); // 'general', 'science', 'humanities', 'business'
        });

        Schema::table('academic_classes', function (Blueprint $table) {
            $table->string('group')->nullable()->after('numeric_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('group');
        });

        Schema::table('textbooks', function (Blueprint $table) {
            $table->dropColumn('group');
        });

        Schema::table('academic_classes', function (Blueprint $table) {
            $table->dropColumn('group');
        });
    }
};
