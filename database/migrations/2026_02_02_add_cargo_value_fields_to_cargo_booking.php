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
        Schema::table('cargo_booking', function (Blueprint $table) {
            $table->decimal('cbm', 10, 4)->nullable()->after('height');
            $table->decimal('rate', 10, 2)->nullable()->after('cbm');
            $table->decimal('value_per_item', 10, 2)->nullable()->after('rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cargo_booking', function (Blueprint $table) {
            $table->dropColumn(['cbm', 'rate', 'value_per_item']);
        });
    }
};
