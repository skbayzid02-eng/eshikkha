<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guardian_students', function (Blueprint $table) {
            $table->foreignId('institution_id')->nullable()->after('id')->constrained('institutions')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('guardian_students', function (Blueprint $table) {
            $table->dropConstrainedForeignId('institution_id');
        });
    }
};
