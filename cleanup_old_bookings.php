<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Booking;
use App\Models\CargoReceipt;

echo "Cleaning up old bookings 1-7 (keeping seeded bookings 8-14)...\n";

// Delete cargo receipts for bookings 1-7
CargoReceipt::whereIn('booking_ref_no', [1, 2, 3, 4, 5, 6, 7])->delete();
echo "✓ Deleted cargo receipts for bookings 1-7\n";

// Delete bookings 1-7
Booking::whereIn('booking_ref_no', [1, 2, 3, 4, 5, 6, 7])->delete();
echo "✓ Deleted bookings 1-7\n";

// Verify
$remaining = Booking::where('voyage_id', 1)
    ->where('booking_status', 'Confirmed')
    ->count();

echo "\n✅ Cleanup complete! Remaining confirmed bookings on Voyage 1: $remaining\n";

// List remaining
$bookings = Booking::where('voyage_id', 1)
    ->where('booking_status', 'Confirmed')
    ->with('cargoBookings.cargoItem')
    ->get();

echo "\nRemaining bookings:\n";
foreach ($bookings as $b) {
    $cb = $b->cargoBookings->first();
    echo "  Ref {$b->booking_ref_no}: {$cb->cargoItem->cargo_item_description} ({$cb->weight}kg, qty={$cb->quantity})\n";
}
