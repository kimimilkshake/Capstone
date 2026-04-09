<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Backfill floor_only, is_stackable, and is_breakable flags on cargo_item
 * based on description keywords.
 *
 * floor_only + is_stackable=false:
 *   Engines, motorcycles, tractors, cadavers, live animals, transformers,
 *   generators (large), safety vaults, stainless tanks, multicab cargo box.
 *   These items must sit directly on the cargo deck and may not have other
 *   cargo stacked on top of them.
 *
 * is_breakable:
 *   Glass sheets, televisions, freezers/refrigerators, CPU/LCD monitors.
 *   These items follow special fragile-stacking rules in the packer.
 */
return new class extends Migration {
    public function up(): void
    {
        // Flag backfill is handled in DatabaseSeeder (runs after CargoItemSeeder).
        // This migration intentionally does nothing — it exists only so that
        // environments which ran it before the seeder change don't re-run it.
    }

    public function down(): void
    {
        // Reset all three flags to their column defaults
        DB::table('cargo_item')->update([
            'floor_only' => false,
            'is_stackable' => true,
            'is_breakable' => false,
        ]);
    }
};
