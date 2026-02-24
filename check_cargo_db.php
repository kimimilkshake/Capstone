<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Get current cargo
echo "=== CURRENT CARGO FOR VOYAGE 1 ===\n";
$bookings = DB::table('booking')
    ->join('cargo_receipt', 'booking.booking_id', '=', 'cargo_receipt.booking_id')
    ->join('cargo_booking', 'booking.booking_id', '=', 'cargo_booking.booking_id')
    ->join('cargo_item', 'cargo_booking.cargo_item_id', '=', 'cargo_item.cargo_item_id')
    ->where('cargo_receipt.voyage_id', 1)
    ->select('cargo_item.cargo_item_name', 'cargo_booking.weight', 'cargo_booking.length', 'cargo_booking.width', 'cargo_booking.height')
    ->get();

$totalWeight = 0;
foreach ($bookings as $b) {
    echo "- {$b->cargo_item_name}: {$b->weight}kg ({$b->length}m x {$b->width}m x {$b->height}m)\n";
    $totalWeight += $b->weight;
}
echo "\nTotal: " . $totalWeight . " kg\n";
echo "60% of 600kg hatch capacity: 360 kg\n";
echo "Status: " . ($totalWeight > 360 ? "EXCEEDS LIMIT BY " . ($totalWeight - 360) . " kg" : "OK") . "\n\n";

// Get available cargo items to add
echo "=== AVAILABLE CARGO ITEMS ===\n";
$items = DB::table('cargo_item')->get();
foreach ($items as $item) {
    echo "ID {$item->cargo_item_id}: {$item->cargo_item_name} - {$item->cargo_item_weight}kg\n";
}
