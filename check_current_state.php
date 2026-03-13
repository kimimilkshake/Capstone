<?php
require 'vendor/autoload.php';
$app = require_once('bootstrap/app.php');
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== CARGO RECEIPTS STATE ===\n";
$receipts = \Illuminate\Support\Facades\DB::table('cargo_receipt')
    ->join('cargo_booking', 'cargo_receipt.cargo_booking_id', '=', 'cargo_booking.cargo_booking_id')
    ->where('cargo_receipt.voyage_id', 1)
    ->select('cargo_receipt.cargo_receipt_id', 'cargo_receipt.hatch_id', 'cargo_booking.weight', 'cargo_booking.booking_ref_no')
    ->orderBy('cargo_receipt.cargo_receipt_id')
    ->get();

$totalByHatch = [];
foreach ($receipts as $r) {
    echo "Receipt {$r->cargo_receipt_id}: {$r->weight}kg, hatch_id=" . ($r->hatch_id ?? 'NULL') . ", booking={$r->booking_ref_no}\n";
    if ($r->hatch_id) {
        $totalByHatch[$r->hatch_id] = ($totalByHatch[$r->hatch_id] ?? 0) + $r->weight;
    }
}

echo "\n=== WEIGHT BY HATCH ===\n";
foreach ($totalByHatch as $hatchId => $weight) {
    echo "Hatch {$hatchId}: {$weight}kg\n";
}

echo "\n=== HATCH CAPACITIES ===\n";
$hatches = \Illuminate\Support\Facades\DB::table('hatch')
    ->where('vessel_id', 3)
    ->select('hatch_id', 'hatch_label', 'hatch_capacity_per_hold')
    ->get();
foreach ($hatches as $h) {
    echo "Hatch {$h->hatch_id} (label={$h->hatch_label}): {$h->hatch_capacity_per_hold} tons = " . ($h->hatch_capacity_per_hold * 1000) . "kg\n";
}
