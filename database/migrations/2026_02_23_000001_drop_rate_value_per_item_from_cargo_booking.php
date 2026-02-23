<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cargo_booking', function (Blueprint $table) {
            if (Schema::hasColumn('cargo_booking', 'rate')) {
                $table->dropColumn('rate');
            }

            if (Schema::hasColumn('cargo_booking', 'value_per_item')) {
                $table->dropColumn('value_per_item');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cargo_booking', function (Blueprint $table) {
            if (!Schema::hasColumn('cargo_booking', 'rate')) {
                $table->decimal('rate', 10, 2)->nullable()->after('cbm');
            }

            if (!Schema::hasColumn('cargo_booking', 'value_per_item')) {
                $table->decimal('value_per_item', 10, 2)->nullable()->after('rate');
            }
        });
    }
};
