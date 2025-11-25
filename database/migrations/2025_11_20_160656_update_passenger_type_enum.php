<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, update existing data to match new enum values
        DB::statement("UPDATE passenger SET passenger_type = 'Regular' WHERE passenger_type = 'adult'");
        DB::statement("UPDATE passenger SET passenger_type = 'Student' WHERE passenger_type = 'student'");
        DB::statement("UPDATE passenger SET passenger_type = 'Senior Citizen' WHERE passenger_type = 'senior'");
        DB::statement("UPDATE passenger SET passenger_type = 'PWD' WHERE passenger_type = 'PWD'");
        DB::statement("UPDATE passenger SET passenger_type = '3 to 11 years old' WHERE passenger_type = 'minor'");

        // Alter the enum column to include new values
        DB::statement("ALTER TABLE passenger MODIFY COLUMN passenger_type ENUM(
            'Regular',
            'Senior Citizen',
            'PWD',
            'Student',
            'Uniformed Personnel',
            '3 to 11 years old',
            'Below 3 years old'
        )");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum values
        DB::statement("ALTER TABLE passenger MODIFY COLUMN passenger_type ENUM(
            'adult',
            'student',
            'minor',
            'uniformed personnel',
            'senior',
            'PWD'
        )");

        // Revert data
        DB::statement("UPDATE passenger SET passenger_type = 'adult' WHERE passenger_type = 'Regular'");
        DB::statement("UPDATE passenger SET passenger_type = 'student' WHERE passenger_type = 'Student'");
        DB::statement("UPDATE passenger SET passenger_type = 'senior' WHERE passenger_type = 'Senior Citizen'");
        DB::statement("UPDATE passenger SET passenger_type = 'minor' WHERE passenger_type = '3 to 11 years old'");
    }
};
