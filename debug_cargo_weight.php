<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// Get voyage code from command line or use example
$voyageCode = $argv[1] ?? 'F1CEBBAY202603-001';

echo "\n=== CARGO WEIGHT DEBUG ===\n";
echo "Voyage: $voyageCode\n";
echo str_repeat("=", 50) . "\n\n";

// Find voyage
$voyage = DB::table('voyage')->where('voyage_code', $voyageCode)->first();

if (!$voyage) {
    echo "❌ Voyage not found: $voyageCode\n";
    exit(1);
}

$voyageId = $voyage->voyage_id;
echo "✓ Voyage ID: $voyageId\n";
echo "  Departed: " . $voyage->voyage_departure_date . " at " . $voyage->voyage_estimated_TD . "\n\n";

// Get all cargo receipts and their bookings for this voyage
$data = DB::table('cargo_receipt')
    ->join('cargo_booking', 'cargo_receipt.cargo_booking_id', '=', 'cargo_booking.cargo_booking_id')
    ->select(
        'cargo_receipt.cargo_receipt_id',
        'cargo_receipt.booking_ref_no',
        'cargo_receipt.hatch_id',
        'cargo_receipt.cargo_item_qty',
        'cargo_booking.weight',
        'cargo_booking.cargo_booking_id'
    )
    ->where('cargo_receipt.voyage_id', $voyageId)
    ->orderBy('cargo_receipt.hatch_id')
    ->orderBy('cargo_receipt.cargo_receipt_id')
    ->get();

if ($data->isEmpty()) {
    echo "❌ No cargo receipts found for this voyage\n";
    exit(1);
}

// Group by hatch and calculate totals
$byHatch = [];
$totalWeight = 0;

foreach ($data as $row) {
    $hatchId = $row->hatch_id;

    if (!isset($byHatch[$hatchId])) {
        $byHatch[$hatchId] = [
            'items' => [],
            'totalWeight' => 0,
            'totalQty' => 0,
        ];
    }

    $byHatch[$hatchId]['items'][] = [
        'receiptId' => $row->cargo_receipt_id,
        'bookingRef' => $row->booking_ref_no,
        'bookingId' => $row->cargo_booking_id,
        'qty' => $row->cargo_item_qty,
        'weight' => $row->weight,
    ];

    $byHatch[$hatchId]['totalWeight'] += $row->weight;
    $byHatch[$hatchId]['totalQty'] += $row->cargo_item_qty;
    $totalWeight += $row->weight;
}

// Display results
foreach ($byHatch as $hatchId => $hatchData) {
    echo "📦 HATCH ID: $hatchId\n";
    echo str_repeat("-", 50) . "\n";

    foreach ($hatchData['items'] as $item) {
        echo sprintf(
            "  Receipt %d | Booking Ref: %s | Booking ID: %d | Qty: %d | Weight: %.2f kg\n",
            $item['receiptId'],
            $item['bookingRef'],
            $item['bookingId'],
            $item['qty'],
            $item['weight']
        );
    }

    echo sprintf(
        "  >>> HATCH TOTAL: %.2f kg (from %d items, %d qty)\n\n",
        $hatchData['totalWeight'],
        count($hatchData['items']),
        $hatchData['totalQty']
    );
}

echo str_repeat("=", 50) . "\n";
echo sprintf("VOYAGE TOTAL WEIGHT: %.2f kg\n\n", $totalWeight);

// Now compare with what the page shows
echo "📊 DATABASE QUERY (used by placement page):\n";
echo str_repeat("-", 50) . "\n";

$hatchCapacities = DB::table('hatch')
    ->join('vessel', 'hatch.vessel_id', '=', 'vessel.vessel_id')
    ->join('voyage', 'voyage.vessel_id', '=', 'vessel.vessel_id')
    ->where('voyage.voyage_id', $voyageId)
    ->select('hatch.hatch_id', 'hatch.hatch_label', 'hatch.hatch_capacity_per_hold')
    ->get();

foreach ($hatchCapacities as $hatch) {
    $currentWeight = DB::table('cargo_receipt')
        ->join('cargo_booking', 'cargo_receipt.cargo_booking_id', '=', 'cargo_booking.cargo_booking_id')
        ->where('cargo_receipt.hatch_id', $hatch->hatch_id)
        ->where('cargo_receipt.voyage_id', $voyageId)
        ->sum('cargo_booking.weight');

    $maxCapacity = ((float) $hatch->hatch_capacity_per_hold) * 1000;
    $availableWeight = $maxCapacity - $currentWeight;

    echo sprintf(
        "Hatch %s (ID: %d)\n  Capacity: %.2f kg | Current: %.2f kg | Available: %.2f kg\n\n",
        $hatch->hatch_label,
        $hatch->hatch_id,
        $maxCapacity,
        $currentWeight,
        $availableWeight
    );
}

echo "\n✓ Debug complete. Use this data to verify weight calculations.\n";
