<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cargo_booking', function (Blueprint $table) {
            if (Schema::hasColumn('cargo_booking', 'arrastre')) {
                $table->dropColumn('arrastre');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cargo_booking', function (Blueprint $table) {
            if (!Schema::hasColumn('cargo_booking', 'arrastre')) {
                $table->decimal('arrastre', 10, 2)->nullable()->after('freight');
            }
        });
    }
};
