<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

// Keep only bookings 1 and 2, assign them to voyage 1, and delete 3-6
echo "=== Updating bookings ===\n";

// Update bookings 1 and 2 to voyage_id = 1
DB::table('booking')->whereIn('booking_ref_no', [1, 2])->update(['voyage_id' => 1]);
echo "Updated bookings 1, 2 to voyage_id = 1\n";

// Delete cargo_receipts 3-6 (must do this first due to FK)
DB::table('cargo_receipt')->whereIn('cargo_receipt_id', [3, 4, 5, 6])->delete();
echo "Deleted cargo_receipts 3-6\n";

// Delete cargo_bookings linked to refs 3-6
DB::table('cargo_booking')->whereIn('booking_ref_no', [3, 4, 5, 6])->delete();
echo "Deleted cargo_bookings linked to refs 3-6\n";

// Delete bookings 3-6 (after cargo_receipts are gone)
DB::table('booking')->whereIn('booking_ref_no', [3, 4, 5, 6])->delete();
echo "Deleted bookings 3-6\n";

// Verify
echo "\n=== Verification ===\n";
$bookings = DB::table('booking')->get();
echo "Remaining bookings: " . $bookings->count() . "\n";
foreach ($bookings as $b) {
    echo "  Ref: {$b->booking_ref_no}, Voyage: {$b->voyage_id}, Status: {$b->booking_status}\n";
}

$receipts = DB::table('cargo_receipt')->where('voyage_id', 1)->get();
echo "\nCargo receipts for voyage 1: " . $receipts->count() . "\n";
foreach ($receipts as $r) {
    echo "  Receipt ID: {$r->cargo_receipt_id}, Booking Ref: {$r->booking_ref_no}\n";
}
