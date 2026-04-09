<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     * Adds floor_only flag to cargo_item.
     * When true, the item must sit directly on the deck and cannot be elevated
     * onto other cargo (vehicles, livestock, cadavers, bulky machinery, etc.)
     */
    public function up(): void
    {
        Schema::table('cargo_item', function (Blueprint $table) {
            $table->boolean('floor_only')
                ->default(false)
                ->after('is_breakable')
                ->comment('Item must stay on deck level — cannot be stacked on other cargo (e.g. vehicles, livestock, machinery)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cargo_item', function (Blueprint $table) {
            $table->dropColumn('floor_only');
        });
    }
};
