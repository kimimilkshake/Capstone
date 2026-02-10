<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "========================================\n";
echo "RECREATE CARGO_BOOKING DATA\n";
echo "========================================\n\n";

$cargoData = [
    8 => ['item_id' => 2, 'weight' => 150, 'width' => 1.5, 'height' => 1, 'length' => 2, 'desc' => 'Steel Coils'],
    9 => ['item_id' => 8, 'weight' => 120, 'width' => 0.5, 'height' => 1.5, 'length' => 0.4, 'desc' => 'Cement Bags'],
    10 => ['item_id' => 4, 'weight' => 90, 'width' => 0.9, 'height' => 1.1, 'length' => 0.9, 'desc' => 'Plastic Drums'],
    11 => ['item_id' => 3, 'weight' => 80, 'width' => 1, 'height' => 1.2, 'length' => 0.8, 'desc' => 'Wooden Pallets'],
    12 => ['item_id' => 5, 'weight' => 60, 'width' => 0.6, 'height' => 0.8, 'length' => 0.6, 'desc' => 'Glass Bottles'],
    13 => ['item_id' => 1, 'weight' => 50, 'width' => 0.8, 'height' => 1.2, 'length' => 0.6, 'desc' => 'Rice Sacks'],
];

echo "1️⃣ DELETE EXISTING CARGO_BOOKING RECORDS FOR BOOKINGS 8-13:\n";
DB::table('cargo_booking')
    ->whereIn('booking_ref_no', [8, 9, 10, 11, 12, 13])
    ->delete();
echo "   ✓ Deleted\n";

echo "\n2️⃣ CREATE NEW CARGO_BOOKING RECORDS:\n";
$total_weight = 0;
foreach ($cargoData as $booking_ref => $data) {
    DB::table('cargo_booking')->insert([
        'booking_ref_no' => $booking_ref,
        'cargo_item_id' => $data['item_id'],
        'weight' => $data['weight'],
        'width' => $data['width'],
        'height' => $data['height'],
        'length' => $data['length'],
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    echo "   ✓ Booking {$booking_ref}: {$data['desc']} ({$data['weight']}kg)\n";
    $total_weight += $data['weight'];
}

echo "\n3️⃣ VERIFY CARGO_BOOKING RECORDS:\n";
$cbs = DB::table('cargo_booking')
    ->whereIn('booking_ref_no', [8, 9, 10, 11, 12, 13])
    ->orderBy('booking_ref_no')
    ->get();

echo "   Total: " . count($cbs) . "\n";
foreach ($cbs as $cb) {
    echo "   Ref {$cb->booking_ref_no}: {$cb->weight}kg, {$cb->width}×{$cb->height}×{$cb->length}m\n";
}

echo "\n========================================\n";
echo "SUMMARY\n";
echo "========================================\n";
echo "✅ 6 Confirmed Bookings (refs 8-13)\n";
echo "✅ 6 Cargo Bookings with dimensions\n";
echo "✅ Total Weight: {$total_weight}kg\n";
echo "✅ Hatch 1 Capacity: 600kg\n";
echo "✅ Hatch 2 Capacity: 600kg\n";
echo "✅ Expected: 360kg in Hatch 1 (60%), 190kg in Hatch 2 (32%)\n";
echo "\nNow hard-refresh browser and test.\n";
