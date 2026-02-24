<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

// Check current hatch weight capacities
echo "=== Current Hatch Weight Capacities ===\n";
$hatches = DB::table('hatch')->get();
foreach ($hatches as $h) {
    echo "Hatch ID: {$h->hatch_id}, Max Weight: {$h->hatch_weight_capacity} tons\n";
}

// Get cargo weights for voyage 1
echo "\n=== Cargo Weights for Voyage 1 ===\n";
$cargo = DB::table('cargo_booking')
    ->whereIn('booking_ref_no', [1, 2])
    ->get();

$totalWeight = 0;
foreach ($cargo as $c) {
    $weight = $c->weight ?? 0;
    echo "Booking Ref: {$c->booking_ref_no}, Weight: {$weight}kg\n";
    $totalWeight += $weight;
}
echo "Total Weight: {$totalWeight}kg (" . ($totalWeight / 1000) . " tons)\n";

// Check if weight capacities are realistic
echo "\n=== Weight Capacity Check ===\n";
echo "Hatch 1 capacity: 0.6 tons (600kg)\n";
echo "Total cargo weight: " . ($totalWeight / 1000) . " tons\n";

if (($totalWeight / 1000) > 0.6) {
    echo "WARNING: Cargo weight exceeds Hatch 1 capacity!\n";
    echo "Recommendation: Update hatch weight capacity or reduce cargo.\n";

    // Suggest updating hatch weight capacity
    echo "\nSuggestion: Update hatch weight capacity to at least " . ($totalWeight / 1000) . " tons\n";
    echo "Update query: UPDATE hatch SET hatch_weight_capacity = " . ($totalWeight / 1000) . " WHERE hatch_id = 1;\n";
} else {
    echo "Cargo weight fits within capacity.\n";
}
