<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('accommodation', function (Blueprint $table) {
            $table->string('accommodation_cot_plan_url')->nullable()->after('accommodation_cot_range');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accommodation', function (Blueprint $table) {
            $table->dropColumn('accommodation_cot_plan_url');
        });
    }
};
