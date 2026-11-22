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
        Schema::create('textbooks', function (Blueprint $table) {
            $table->id();
            $table->integer('class_level'); // 1 to 10
            $table->string('class_name'); // 'Class 1', 'Class 2', etc.
            $table->string('class_name_bn'); // 'প্রথম শ্রেণি', etc.
            $table->string('title'); // e.g. 'আমার বাংলা বই'
            $table->string('english_title')->nullable();
            $table->string('subject'); // Bangla, English, Mathematics, Science, etc.
            $table->string('cover_image')->nullable();
            $table->string('cover_color_start')->default('#3B82F6');
            $table->string('cover_color_end')->default('#1D4ED8');
            $table->string('icon')->default('menu_book_rounded');
            $table->string('author')->default('জাতীয় শিক্ষাক্রম ও পাঠ্যপুস্তক বোর্ড (NCTB)');
            $table->string('edition')->default('২০২৬ শিক্ষাবর্ষ');
            $table->integer('total_pages')->default(120);
            $table->boolean('has_pdf')->default(true);
            $table->string('pdf_url')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('textbooks');
    }
};
