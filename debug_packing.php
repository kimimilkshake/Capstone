<?php
// Debug script to check packing for Voyage 1
require 'bootstrap/app.php';

use App\Models\Voyage;
use App\Models\Booking;

$app = app();
$voyage = Voyage::find(1);

if (!$voyage) {
    echo "Voyage 1 not found\n";
    exit;
}

// Get confirmed bookings
$bookings = Booking::where('voyage_id', 1)
    ->whereRaw("LOWER(booking_status) = ?", ['confirmed'])
    ->with('cargoBookings.cargoItem')
    ->get();

echo "=== VOYAGE 1 PACKING DEBUG ===\n";
echo "Total confirmed bookings: " . count($bookings) . "\n\n";

$totalWeight = 0;
$items = [];

foreach ($bookings as $booking) {
    foreach ($booking->cargoBookings as $cb) {
        if (!$cb->width || !$cb->height || !$cb->length)
            continue;

        $item = [
            'id' => $cb->cargo_booking_id,
            'description' => $cb->cargoItem->cargo_item_description ?? 'Unknown',
            'width' => (float) $cb->width,
            'height' => (float) $cb->height,
            'depth' => (float) $cb->length,
            'weight' => (float) ($cb->weight ?? 0),
            'is_breakable' => (bool) ($cb->cargoItem->is_breakable ?? false),
        ];
        $items[] = $item;
        $totalWeight += $item['weight'];
    }
}

// Sort by weight descending
usort($items, fn($a, $b) => $b['weight'] <=> $a['weight']);

echo "Cargo items (sorted by weight):\n";
foreach ($items as $i => $item) {
    $frag = $item['is_breakable'] ? '🔴' : '🟢';
    echo sprintf(
        "%2d. %s %s (%.1fkg): %.2fw × %.2fh × %.2fd\n",
        $i + 1,
        $frag,
        substr($item['description'], 0, 30),
        $item['weight'],
        $item['width'],
        $item['height'],
        $item['depth']
    );
}

echo "\nTotal weight: {$totalWeight}kg\n";
echo "Hatch capacity: 600kg\n";
echo "Utilization: " . number_format(($totalWeight / 600) * 100, 1) . "%\n";

// Simulate packing
echo "\n=== SIMULATING PACKING ===\n";

$bin1Weight = 0;
$bin2Weight = 0;
$maxHatchCapacity = 600;

foreach ($items as $item) {
    if ($bin1Weight + $item['weight'] <= $maxHatchCapacity) {
        echo "✅ Hatch 1: +{$item['weight']}kg {$item['description']}\n";
        $bin1Weight += $item['weight'];
    } else {
        echo "⬜ Hatch 2: +{$item['weight']}kg {$item['description']}\n";
        $bin2Weight += $item['weight'];
    }
}

echo "\n=== HATCH UTILIZATION ===\n";
echo "Hatch 1: {$bin1Weight}kg / 600kg (" . number_format(($bin1Weight / 600) * 100, 1) . "%)\n";
echo "Hatch 2: {$bin2Weight}kg / 600kg (" . number_format(($bin2Weight / 600) * 100, 1) . "%)\n";
