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
        Schema::table('cargo_item', function (Blueprint $table) {
            $table->boolean('is_breakable')->default(false)->comment('Mark if item is fragile and should be stacked instead of spread horizontally');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cargo_item', function (Blueprint $table) {
            $table->dropColumn('is_breakable');
        });
    }
};
