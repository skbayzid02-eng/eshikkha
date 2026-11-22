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
            $table->string('present_village')->nullable()->after('address');
            $table->string('present_post_office')->nullable()->after('present_village');
            $table->string('present_upazila')->nullable()->after('present_post_office');
            $table->string('present_district')->nullable()->after('present_upazila');
            $table->string('permanent_village')->nullable()->after('present_district');
            $table->string('permanent_post_office')->nullable()->after('permanent_village');
            $table->string('permanent_upazila')->nullable()->after('permanent_post_office');
            $table->string('permanent_district')->nullable()->after('permanent_upazila');
            $table->text('permanent_address')->nullable()->after('permanent_district');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'present_village',
                'present_post_office',
                'present_upazila',
                'present_district',
                'permanent_village',
                'permanent_post_office',
                'permanent_upazila',
                'permanent_district',
                'permanent_address',
            ]);
        });
    }
};
