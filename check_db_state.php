<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "========================================\n";
echo "CLEAN DATABASE STATE CHECK\n";
echo "========================================\n\n";

// 1. Check ALL confirmed bookings with voyage_id = 1
echo "1️⃣ All Confirmed Bookings on Voyage 1:\n";
$bookings = DB::table('booking')
    ->where('voyage_id', 1)
    ->whereRaw("LOWER(booking_status) = ?", ['confirmed'])
    ->get();

foreach ($bookings as $b) {
    echo "   Ref {$b->booking_ref_no}: qty={$b->cargo_item_qty}\n";
}
echo "   Total: " . count($bookings) . "\n";

// 2. Check all cargo_receipt for voyage 1
echo "\n2️⃣ All Cargo Receipts on Voyage 1:\n";
$receipts = DB::table('cargo_receipt')
    ->where('voyage_id', 1)
    ->get();

foreach ($receipts as $r) {
    echo "   Receipt {$r->cargo_receipt_id}: booking_ref={$r->booking_ref_no}, qty={$r->cargo_item_qty}\n";
}
echo "   Total: " . count($receipts) . "\n";

// 3. Check cargo_booking for these bookings
echo "\n3️⃣ Cargo Bookings for Voyage 1:\n";
$cbs = DB::table('cargo_booking')
    ->whereIn('booking_ref_no', $bookings->pluck('booking_ref_no')->toArray())
    ->get();

foreach ($cbs as $cb) {
    echo "   Booking {$cb->booking_ref_no}: weight={$cb->weight}kg, dimensions={$cb->width}×{$cb->height}×{$cb->length}\n";
}

// 4. Check if issue is in cargo_item table
echo "\n4️⃣ Checking cargo_item table (might be max qty):\n";
$items = DB::table('cargo_item')->limit(10)->get();
foreach ($items as $item) {
    $qty = isset($item->cargo_item_qty) ? $item->cargo_item_qty : 'NULL';
    echo "   ID {$item->cargo_item_id}: {$item->cargo_item_description} - default_qty={$qty}\n";
}

echo "\n========================================\n";
echo "If all bookings have qty=1 and Hatch 2 has 0.6t, then DB is OK.\n";
echo "If not, items might be coming from cargo_item table instead of booking/receipt.\n";
echo "========================================\n";
