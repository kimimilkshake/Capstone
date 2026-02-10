<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "========================================\n";
echo "SEEDING 6 FRESH CONFIRMED BOOKINGS\n";
echo "========================================\n\n";

$cargoItems = [
    ['cargo_item_id' => 2, 'description' => 'Steel Coils', 'weight' => 150, 'width' => 1.5, 'height' => 1, 'length' => 2],
    ['cargo_item_id' => 8, 'description' => 'Cement Bags', 'weight' => 120, 'width' => 0.5, 'height' => 1.5, 'length' => 0.4],
    ['cargo_item_id' => 4, 'description' => 'Plastic Drums', 'weight' => 90, 'width' => 0.9, 'height' => 1.1, 'length' => 0.9],
    ['cargo_item_id' => 3, 'description' => 'Wooden Pallets', 'weight' => 80, 'width' => 1, 'height' => 1.2, 'length' => 0.8],
    ['cargo_item_id' => 5, 'description' => 'Glass Bottles', 'weight' => 60, 'width' => 0.6, 'height' => 0.8, 'length' => 0.6],
    ['cargo_item_id' => 1, 'description' => 'Rice Sacks', 'weight' => 50, 'width' => 0.8, 'height' => 1.2, 'length' => 0.6],
];

$total_weight = 0;
$booking_refs = [];

// Get next booking_ref_no
$maxRef = DB::table('booking')->max('booking_ref_no') ?? 0;
$nextRef = $maxRef + 1;

foreach ($cargoItems as $index => $item) {
    $booking_ref = $nextRef + $index;

    // Insert into booking table (NO cargo_item_qty here)
    DB::table('booking')->insert([
        'booking_ref_no' => $booking_ref,
        'booking_type' => 'cargo',
        'booking_status' => 'Confirmed',
        'voyage_id' => 1,
        'cargo_item_id' => $item['cargo_item_id'],
        'booking_date' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Insert into cargo_booking table
    DB::table('cargo_booking')->insert([
        'booking_ref_no' => $booking_ref,
        'cargo_item_id' => $item['cargo_item_id'],
        'weight' => $item['weight'],
        'width' => $item['width'],
        'height' => $item['height'],
        'length' => $item['length'],
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    // Insert into cargo_receipt table (HAS cargo_item_qty)
    DB::table('cargo_receipt')->insert([
        'voyage_id' => 1,
        'booking_ref_no' => $booking_ref,
        'cargo_item_id' => $item['cargo_item_id'],
        'cargo_item_qty' => 1, // CRITICAL: qty = 1
        'sender_id' => 1, // dummy sender
        'consignee_id' => 1, // dummy consignee
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    echo "✓ Booking {$booking_ref}: {$item['description']} ({$item['weight']}kg)\n";
    $booking_refs[] = $booking_ref;
    $total_weight += $item['weight'];
}

echo "\n========================================\n";
echo "VERIFICATION\n";
echo "========================================\n\n";

echo "Confirmed Bookings on Voyage 1:\n";
$bookings = DB::table('booking')
    ->where('voyage_id', 1)
    ->whereRaw("LOWER(booking_status) = ?", ['confirmed'])
    ->get();

foreach ($bookings as $b) {
    echo "   Booking {$b->booking_ref_no}: status={$b->booking_status}\n";
}

echo "\nCargo Bookings:\n";
$cbs = DB::table('cargo_booking')
    ->whereIn('booking_ref_no', $booking_refs)
    ->get();

foreach ($cbs as $cb) {
    echo "   {$cb->booking_ref_no}: {$cb->weight}kg, {$cb->width}×{$cb->height}×{$cb->length}m\n";
}

echo "\nCargo Receipts:\n";
$receipts = DB::table('cargo_receipt')
    ->where('voyage_id', 1)
    ->get();

foreach ($receipts as $r) {
    echo "   Receipt {$r->cargo_receipt_id}: {$r->booking_ref_no}, qty={$r->cargo_item_qty}\n";
}

echo "\n========================================\n";
echo "SUMMARY\n";
echo "========================================\n";
echo "✅ Created: 6 Confirmed Bookings\n";
echo "✅ Total Weight: {$total_weight}kg\n";
echo "✅ Per Hatch Capacity: 600kg\n";
echo "✅ Expected Fill: Hatch 1 ~360kg (60%), Hatch 2 ~190kg (32%)\n";
echo "\nNext: Hard-refresh browser (Ctrl+F5) and click Voyage 1\n";
