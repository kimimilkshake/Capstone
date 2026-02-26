<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== RESETTING CARGO FOR VOYAGE 1 ===\n";

// Delete existing cargo bookings and receipts for voyage 1
DB::table('cargo_booking')->whereIn(
    'booking_ref_no',
    DB::table('cargo_receipt')->where('voyage_id', 1)->pluck('booking_ref_no')
)->delete();

DB::table('cargo_receipt')->where('voyage_id', 1)->delete();
DB::table('booking')->where('voyage_id', 1)->delete();

echo "✅ Deleted existing cargo for voyage 1\n\n";

// Now we'll create NEW bookings that fit within 60% limit = 360kg
// Let's create 8 items that total ~320kg, spread across 4 zones

// For zone distribution: front-left, front-right, back-left, back-right
// We want weight balance:
// FL: 80kg, FR: 80kg, BL: 80kg, BR: 80kg = 320kg total

$newBookings = [
    // Front-Left Zone (80kg)
    ['cargo_item_id' => 1, 'qty' => 1, 'weight' => 50, 'name' => 'Rice Sacks (50kg)'],
    ['cargo_item_id' => 14, 'qty' => 1, 'weight' => 30, 'name' => 'Electronics Equipment'],

    // Front-Right Zone (80kg)
    ['cargo_item_id' => 8, 'qty' => 1, 'weight' => 50, 'name' => 'Cement Bags (50kg)'],
    ['cargo_item_id' => 7, 'qty' => 1, 'weight' => 30, 'name' => 'Textiles (Rolls)'],

    // Back-Left Zone (80kg)
    ['cargo_item_id' => 5, 'qty' => 1, 'weight' => 60, 'name' => 'Glass Bottles'],
    ['cargo_item_id' => 4, 'qty' => 1, 'weight' => 20, 'name' => 'Plastic Drums (small)'],

    // Back-Right Zone (80kg)
    ['cargo_item_id' => 3, 'qty' => 1, 'weight' => 80, 'name' => 'Wooden Pallets'],
];

echo "Creating new bookings:\n";

foreach ($newBookings as $idx => $item) {
    // Create booking with numeric ref (booking_ref_no is stored as integer in some systems)
    $bookingRefNo = 10000 + $idx;

    DB::table('booking')->insert([
        'booking_ref_no' => $bookingRefNo,
        'booking_date' => now(),
        'booking_status' => 'confirmed',
        'booking_type' => 'cargo',
        'sender_id' => 1,
        'consignee_id' => 1,
        'cargo_item_id' => $item['cargo_item_id'],
        'voyage_id' => 1,
        'payment_id' => null,
    ]);

    // Create cargo booking
    DB::table('cargo_booking')->insert([
        'booking_ref_no' => $bookingRefNo,
        'cargo_item_id' => $item['cargo_item_id'],
        'quantity' => $item['qty'],
        'weight' => $item['weight'],
        'length' => 1.0,
        'width' => 0.8,
        'height' => 0.6,
        'cbm' => 0.48,
        'rate' => 100,
        'value_per_item' => 1000,
    ]);

    // Create cargo receipt
    DB::table('cargo_receipt')->insert([
        'sender_id' => 1,
        'consignee_id' => 1,
        'cargo_item_id' => $item['cargo_item_id'],
        'voyage_id' => 1,
        'payment_id' => null,
        'booking_ref_no' => $bookingRefNo,
        'cargo_item_qty' => $item['qty'],
    ]);

    echo "✅ {$item['name']}: {$item['weight']}kg\n";
}

echo "\nVerifying new data:\n";
$total = DB::table('cargo_booking')->whereIn(
    'booking_ref_no',
    DB::table('cargo_receipt')->where('voyage_id', 1)->pluck('booking_ref_no')
)->sum(DB::raw('weight * quantity'));

$count = DB::table('cargo_booking')->whereIn(
    'booking_ref_no',
    DB::table('cargo_receipt')->where('voyage_id', 1)->pluck('booking_ref_no')
)->count();

echo "✅ Total weight: " . $total . " kg\n";
echo "✅ Total items: " . $count . "\n";
echo "✅ 60% limit: 360 kg\n";
echo "✅ Status: " . ($total <= 360 ? "WITHIN LIMIT ✅" : "EXCEEDS LIMIT ❌") . "\n";
