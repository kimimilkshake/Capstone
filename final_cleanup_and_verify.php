<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Booking;
use App\Models\CargoReceipt;
use App\Models\CargoBooking;
use App\Models\Voyage;

echo "========================================\n";
echo "FINAL CLEANUP AND VERIFICATION\n";
echo "========================================\n\n";

// 1. Check current database state for Voyage 1
echo "1️⃣ CURRENT DATABASE STATE:\n";
echo "   Bookings on Voyage 1:\n";
$bookings = Booking::where('voyage_id', 1)->get();
foreach ($bookings as $b) {
    echo "      Ref {$b->booking_ref_no}: status={$b->booking_status}\n";
}
echo "   Total: " . count($bookings) . "\n";

echo "\n   CargoReceipts on Voyage 1:\n";
$receipts = CargoReceipt::where('voyage_id', 1)->get();
foreach ($receipts as $r) {
    echo "      Receipt {$r->cargo_receipt_id}: booking_ref={$r->booking_ref_no}\n";
}
echo "   Total: " . count($receipts) . "\n";

echo "\n   Hatches:\n";
$hatches = DB::table('hatch')->whereIn('hatch_id', [1, 2])->get();
foreach ($hatches as $h) {
    echo "      Hatch {$h->hatch_id}: capacity={$h->hatch_weight_capacity} tons\n";
}

// 2. Delete ALL voyage 1 data
echo "\n2️⃣ DELETING ALL VOYAGE 1 DATA:\n";
$deletedReceipts = CargoReceipt::where('voyage_id', 1)->delete();
$deletedBookings = Booking::where('voyage_id', 1)->delete();
echo "   Deleted {$deletedReceipts} receipts\n";
echo "   Deleted {$deletedBookings} bookings\n";

// 3. Verify deletion
echo "\n3️⃣ VERIFY DELETION:\n";
$remainingB = Booking::where('voyage_id', 1)->count();
$remainingR = CargoReceipt::where('voyage_id', 1)->count();
echo "   Remaining bookings: {$remainingB}\n";
echo "   Remaining receipts: {$remainingR}\n";

// 4. Fix Hatch 2 capacity
echo "\n4️⃣ FIXING HATCH CAPACITIES:\n";
DB::table('hatch')->where('hatch_id', 1)->update(['hatch_weight_capacity' => 0.6]);
DB::table('hatch')->where('hatch_id', 2)->update(['hatch_weight_capacity' => 0.6]);
$hatches = DB::table('hatch')->whereIn('hatch_id', [1, 2])->get();
foreach ($hatches as $h) {
    echo "   Hatch {$h->hatch_id}: {$h->hatch_weight_capacity} tons ✓\n";
}

// 5. Create 6 fresh confirmed bookings
echo "\n5️⃣ SEEDING 6 FRESH CONFIRMED BOOKINGS:\n";

$cargoItems = [
    ['description' => 'Steel Coils', 'weight' => 150, 'width' => 1.5, 'height' => 1, 'length' => 2],
    ['description' => 'Cement Bags', 'weight' => 120, 'width' => 0.5, 'height' => 1.5, 'length' => 0.4],
    ['description' => 'Plastic Drums', 'weight' => 90, 'width' => 0.9, 'height' => 1.1, 'length' => 0.9],
    ['description' => 'Wooden Pallets', 'weight' => 80, 'width' => 1, 'height' => 1.2, 'length' => 0.8],
    ['description' => 'Glass Bottles', 'weight' => 60, 'width' => 0.6, 'height' => 0.8, 'length' => 0.6],
    ['description' => 'Rice Sacks', 'weight' => 50, 'width' => 0.8, 'height' => 1.2, 'length' => 0.6],
];

$total_weight = 0;
foreach ($cargoItems as $index => $item) {
    // Create booking
    $booking = Booking::create([
        'voyage_id' => 1,
        'booking_ref_no' => 'TEST-' . (100 + $index),
        'booking_status' => 'Confirmed',
        'passenger_name' => 'Test Shipper ' . ($index + 1),
        'booking_date' => now(),
        'cargo_item_qty' => 1, // Most important: qty = 1
    ]);

    // Create cargo booking
    $cargoBooking = CargoBooking::create([
        'booking_ref_no' => $booking->booking_ref_no,
        'cargo_item_id' => ($index + 1), // Assuming cargo items 1-6 exist
        'weight' => $item['weight'],
        'width' => $item['width'],
        'height' => $item['height'],
        'length' => $item['length'],
    ]);

    // Create cargo receipt
    $receipt = CargoReceipt::create([
        'voyage_id' => 1,
        'booking_ref_no' => $booking->booking_ref_no,
        'cargo_item_id' => ($index + 1),
        'cargo_item_qty' => 1, // Most important: qty = 1
    ]);

    echo "   ✓ Booking {$booking->booking_ref_no}: {$item['description']} ({$item['weight']}kg)\n";
    $total_weight += $item['weight'];
}

echo "\n   Total Weight: {$total_weight}kg\n";
echo "   Total Capacity: 1200kg (600kg per hatch)\n";
echo "   Expected Packing: ~360kg in Hatch 1 (60%), ~190kg in Hatch 2 (32%)\n";

// 6. Final verification via API
echo "\n6️⃣ FINAL API VERIFICATION:\n";
$voyage = Voyage::with(['vessel.hatches', 'cargoReceipts.cargoBooking.cargoItem'])
    ->findOrFail(1);

echo "   Voyage: {$voyage->voyage_code}\n";
echo "   Vessel: {$voyage->vessel->vessel_name}\n";
echo "\n   Hatches:\n";
foreach ($voyage->vessel->hatches as $h) {
    echo "      {$h->hatch_label}: {$h->hatch_width}×{$h->hatch_height}×{$h->hatch_length}m, {$h->hatch_weight_capacity}t\n";
}

echo "\n   Confirmed Bookings:\n";
$confirmedBookings = Booking::where('voyage_id', 1)
    ->whereRaw("LOWER(booking_status) = ?", ['confirmed'])
    ->with('cargoBookings.cargoItem')
    ->get();

foreach ($confirmedBookings as $b) {
    if ($b->cargoBookings->count() > 0) {
        $cb = $b->cargoBookings->first();
        $desc = isset($cb->cargoItem) ? $cb->cargoItem->cargo_item_description : 'Unknown';
        echo "      {$b->booking_ref_no}: {$desc} - {$cb->weight}kg, qty={$b->cargo_item_qty}\n";
    }
}

echo "\n========================================\n";
echo "✅ CLEANUP COMPLETE!\n";
echo "========================================\n";
echo "\nNow hard-refresh your browser (Ctrl+F5) and click on Voyage 1.\n";
echo "You should see 6 cargo items with qty=1 each, total 550kg.\n";
