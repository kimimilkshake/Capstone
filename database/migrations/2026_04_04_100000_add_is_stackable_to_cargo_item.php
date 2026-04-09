<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('cargo_item', function (Blueprint $table) {
            $table->boolean('is_stackable')
                ->default(true)
                ->after('floor_only')
                ->comment('Whether other cargo can be stacked on top of this item. Heaviest items placed first so lighter items can stack on them.');
        });
    }

    public function down(): void
    {
        Schema::table('cargo_item', function (Blueprint $table) {
            $table->dropColumn('is_stackable');
        });
    }
};
