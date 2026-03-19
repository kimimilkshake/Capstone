<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('passenger_ticket', function (Blueprint $table) {
            if (!Schema::hasColumn('passenger_ticket', 'pt_boarded_at')) {
                $table->timestamp('pt_boarded_at')->nullable()->after('pt_valid_until_ts');
            }
        });
    }

    public function down(): void
    {
        Schema::table('passenger_ticket', function (Blueprint $table) {
            if (Schema::hasColumn('passenger_ticket', 'pt_boarded_at')) {
                $table->dropColumn('pt_boarded_at');
            }
        });
    }
};