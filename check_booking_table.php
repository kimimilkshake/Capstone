<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Booking;
use Illuminate\Support\Facades\DB;

// Check booking table directly
echo "=== Raw Booking Table ===\n";
$bookings = DB::table('booking')->get();
echo "Total bookings: " . $bookings->count() . "\n";
foreach ($bookings as $b) {
    echo "  Ref: {$b->booking_ref_no}, Voyage: {$b->voyage_id}, Status: {$b->booking_status}, Type: {$b->booking_type}\n";
}

echo "\n=== Booking Model Query (voyage_id = 1) ===\n";
$modelBookings = Booking::where('voyage_id', 1)->get();
echo "Total: " . $modelBookings->count() . "\n";
foreach ($modelBookings as $b) {
    echo "  Ref: {$b->booking_ref_no}, Voyage: {$b->voyage_id}, Status: {$b->booking_status}\n";
}

// Check if any bookings exist at all
echo "\n=== All Bookings in DB ===\n";
$allBookings = Booking::limit(10)->get();
echo "Total: " . $allBookings->count() . "\n";
foreach ($allBookings as $b) {
    echo "  Ref: {$b->booking_ref_no}, Voyage: {$b->voyage_id}, Status: {$b->booking_status}\n";
}
