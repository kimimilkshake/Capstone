<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Voyage 1 Confirmed Cargo Bookings ===\n";
$cargo = DB::table('cargo_booking as cb')
    ->join('booking as b', 'cb.booking_ref_no', '=', 'b.booking_ref_no')
    ->join('cargo_item as ci', 'cb.cargo_item_id', '=', 'ci.cargo_item_id')
    ->where('b.voyage_id', 1)
    ->where('b.booking_status', 'Confirmed')
    ->select('cb.booking_ref_no', 'ci.cargo_item_description', 'cb.weight', 'cb.width', 'cb.height', 'cb.length')
    ->orderBy('cb.weight', 'desc')
    ->get();

$totalWeight = 0;
foreach ($cargo as $c) {
    echo sprintf(
        "%2d. %s - %3dkg (%sx%sx%s m)\n",
        $c->booking_ref_no,
        $c->cargo_item_description,
        $c->weight,
        $c->width,
        $c->height,
        $c->length
    );
    $totalWeight += $c->weight;
}

echo "\n=== Summary ===\n";
echo "Total Items: " . count($cargo) . "\n";
echo "Total Weight: {$totalWeight}kg (" . ($totalWeight / 1000) . " tons)\n";
echo "Hatch Capacity Each: 0.6 tons (600kg)\n";
echo "Optimal Distribution: ~{" . ($totalWeight / 2) . "}kg per hatch\n";
echo "\n✅ Load-balancing algorithm will distribute across hatches to prevent ship tilt\n";
