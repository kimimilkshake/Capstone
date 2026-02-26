<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== MARKING BREAKABLE CARGO ITEMS ===\n";

// Define which items are breakable
$breakableItems = [
    'Glass Bottles' => true,      // Fragile glass
    'Electronics' => true,         // Sensitive electronics
    'Textiles' => false,           // Fabric is flexible, can compress
    'Cement Bags' => false,        // Paper bags but contents protected
];

// Get all cargo items
$items = DB::table('cargo_item')->get();

foreach ($items as $item) {
    $isBreakable = false;

    // Check description for breakable indicators
    if (
        stripos($item->cargo_item_description, 'Glass') !== false ||
        stripos($item->cargo_item_description, 'Bottle') !== false
    ) {
        $isBreakable = true;
    } elseif (
        stripos($item->cargo_item_description, 'Electronics') !== false ||
        stripos($item->cargo_item_description, 'Equipment') !== false
    ) {
        $isBreakable = true;
    } elseif (
        stripos($item->cargo_item_description, 'Ceramic') !== false ||
        stripos($item->cargo_item_description, 'Fragile') !== false
    ) {
        $isBreakable = true;
    }

    DB::table('cargo_item')
        ->where('cargo_item_id', $item->cargo_item_id)
        ->update(['is_breakable' => $isBreakable]);

    $label = $isBreakable ? '🔴 BREAKABLE' : '🟢 ROBUST';
    echo "{$label}: {$item->cargo_item_description}\n";
}

echo "\n✅ Cargo items marked with fragility status\n";
