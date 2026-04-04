<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Cargo Placements for Voyage 1 ===\n";
$rows = DB::table('cargo_hatch_placement')
    ->where('voyage_id', 1)
    ->select('cargo_receipt_id', DB::raw('count(*) as cnt'))
    ->groupBy('cargo_receipt_id')
    ->get();

echo "Total receipt groups: " . $rows->count() . "\n";
foreach ($rows as $row) {
    echo "  Receipt {$row->cargo_receipt_id}: {$row->cnt} items placed\n";
}

$total = DB::table('cargo_hatch_placement')->where('voyage_id', 1)->count();
echo "Grand total placements: $total\n";

// Show receipts and their expected quantities
echo "\n=== Expected vs Placed ===\n";
$receipts = DB::table('cargo_receipts')
    ->whereIn('id', [1,2,3,4,5,6])
    ->select('id', 'booking_ref', 'cargo_item_name', 'quantity')
    ->get();
foreach ($receipts as $r) {
    $placed = DB::table('cargo_hatch_placement')
        ->where('voyage_id', 1)
        ->where('cargo_receipt_id', $r->id)
        ->count();
    echo "  Receipt {$r->id} ({$r->booking_ref} - {$r->cargo_item_name}): expected {$r->quantity}, placed $placed\n";
}
