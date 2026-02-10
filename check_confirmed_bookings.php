<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Booking;
use App\Models\CargoBooking;

echo "=== Confirmed Bookings on Voyage 1 ===\n";
$bookings = Booking::where('voyage_id', 1)
    ->where('booking_status', 'Confirmed')
    ->with('cargoBookings.cargoItem')
    ->get();

echo "Total confirmed bookings: " . $bookings->count() . "\n\n";

foreach ($bookings as $booking) {
    $cb = $booking->cargoBookings->first();
    if ($cb) {
        echo "Booking Ref: {$booking->booking_ref_no}\n";
        echo "  Item: {$cb->cargoItem->cargo_item_description}\n";
        echo "  Qty: {$cb->quantity}\n";
        echo "  Weight: {$cb->weight}kg\n";
        echo "  Dimensions: {$cb->width}×{$cb->height}×{$cb->length}m\n\n";
    }
}
