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
        Schema::create('textbook_chapters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('textbook_id')->constrained('textbooks')->cascadeOnDelete();
            $table->integer('chapter_number');
            $table->string('title');
            $table->string('english_title')->nullable();
            $table->integer('page_start')->default(1);
            $table->integer('page_end')->default(10);
            $table->text('summary')->nullable();
            $table->longText('content')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('textbook_chapters');
    }
};
