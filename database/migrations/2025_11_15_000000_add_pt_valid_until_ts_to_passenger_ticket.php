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
        Schema::table('passenger_ticket', function (Blueprint $table) {
            if (!Schema::hasColumn('passenger_ticket', 'pt_valid_until_ts')) {
                $table->dateTime('pt_valid_until_ts')->nullable()->after('pt_valid_until');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('passenger_ticket', function (Blueprint $table) {
            if (Schema::hasColumn('passenger_ticket', 'pt_valid_until_ts')) {
                $table->dropColumn('pt_valid_until_ts');
            }
        });
    }
};
