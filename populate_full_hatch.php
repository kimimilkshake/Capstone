<?php
require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== POPULATING HATCH WITH DIVERSE CARGO ITEMS ===\n";

// Delete existing cargo for voyage 1
DB::table('cargo_booking')->whereIn(
    'booking_ref_no',
    DB::table('cargo_receipt')->where('voyage_id', 1)->pluck('booking_ref_no')
)->delete();
DB::table('cargo_receipt')->where('voyage_id', 1)->delete();
DB::table('booking')->where('voyage_id', 1)->delete();

echo "✅ Cleared existing cargo\n\n";

// Get existing cargo items to reference
$glassId = DB::table('cargo_item')->where('cargo_item_description', 'LIKE', '%Glass%')->first()->cargo_item_id ?? 5;
$electronicsId = DB::table('cargo_item')->where('cargo_item_description', 'LIKE', '%Electronics%')->first()->cargo_item_id ?? 14;
$steelId = DB::table('cargo_item')->where('cargo_item_description', 'LIKE', '%Steel%')->first()->cargo_item_id ?? 2;
$palletsId = DB::table('cargo_item')->where('cargo_item_description', 'LIKE', '%Pallet%')->first()->cargo_item_id ?? 3;
$drumsId = DB::table('cargo_item')->where('cargo_item_description', 'LIKE', '%Drum%')->first()->cargo_item_id ?? 4;
$riceId = DB::table('cargo_item')->where('cargo_item_description', 'LIKE', '%Rice%')->first()->cargo_item_id ?? 1;
$cementId = DB::table('cargo_item')->where('cargo_item_description', 'LIKE', '%Cement%')->first()->cargo_item_id ?? 8;
$textilesId = DB::table('cargo_item')->where('cargo_item_description', 'LIKE', '%Textile%')->first()->cargo_item_id ?? 7;

// Create diverse cargo mix to fill ~550kg
// Mix of breakable and robust items with different sizes
$cargo = [
    // BREAKABLE (STACKING) - will stack on top of each other
    ['name' => 'Glass Bottles (Cartons)', 'id' => $glassId, 'weight' => 60, 'l' => 0.8, 'w' => 0.6, 'h' => 1.0, 'breakable' => true],
    ['name' => 'Electronics Equipment', 'id' => $electronicsId, 'weight' => 45, 'l' => 0.7, 'w' => 0.5, 'h' => 0.8, 'breakable' => true],

    // ROBUST (SPREADING) - will spread horizontally
    ['name' => 'Steel Coils', 'id' => $steelId, 'weight' => 150, 'l' => 1.5, 'w' => 1.2, 'h' => 0.9, 'breakable' => false],
    ['name' => 'Wooden Pallets (Set 1)', 'id' => $palletsId, 'weight' => 80, 'l' => 1.2, 'w' => 0.8, 'h' => 1.0, 'breakable' => false],
    ['name' => 'Plastic Drums', 'id' => $drumsId, 'weight' => 35, 'l' => 0.9, 'w' => 0.9, 'h' => 1.1, 'breakable' => false],
    ['name' => 'Rice Sacks', 'id' => $riceId, 'weight' => 50, 'l' => 0.8, 'w' => 0.6, 'h' => 1.2, 'breakable' => false],
    ['name' => 'Cement Bags', 'id' => $cementId, 'weight' => 50, 'l' => 0.4, 'w' => 0.5, 'h' => 1.5, 'breakable' => false],
    ['name' => 'Textiles Rolls', 'id' => $textilesId, 'weight' => 40, 'l' => 1.1, 'w' => 0.7, 'h' => 0.6, 'breakable' => false],

    // More items to fill remaining capacity
    ['name' => 'Metal Parts Box', 'id' => $steelId, 'weight' => 70, 'l' => 0.9, 'w' => 0.8, 'h' => 0.95, 'breakable' => false],
    ['name' => 'Rubber Sheets Bundle', 'id' => $drumsId, 'weight' => 30, 'l' => 1.0, 'w' => 0.5, 'h' => 0.4, 'breakable' => false],
    ['name' => 'Wooden Pallets (Set 2)', 'id' => $palletsId, 'weight' => 60, 'l' => 1.0, 'w' => 0.8, 'h' => 0.9, 'breakable' => false],
];

$totalWeight = 0;
echo "📦 CARGO ITEMS TO ADD:\n";
foreach ($cargo as $idx => $item) {
    $fragility = $item['breakable'] ? '🔴 BREAK' : '🟢 ROBUST';
    echo ($idx + 1) . ". {$fragility} {$item['name']}: {$item['weight']}kg ({$item['l']}×{$item['w']}×{$item['h']}m)\n";
    $totalWeight += $item['weight'];
}

echo "\nTotal weight: {$totalWeight}kg";
echo "\nHatch capacity: 600kg";
echo "\nFill %: " . number_format(($totalWeight / 600) * 100, 1) . "%\n\n";

// Create bookings with these items
echo "Creating bookings...\n";
foreach ($cargo as $idx => $item) {
    $bookingRef = 10000 + $idx;

    DB::table('booking')->insert([
        'booking_ref_no' => $bookingRef,
        'booking_date' => now(),
        'booking_status' => 'confirmed',
        'booking_type' => 'cargo',
        'sender_id' => 1,
        'consignee_id' => 1,
        'cargo_item_id' => $item['id'],
        'voyage_id' => 1,
        'payment_id' => null,
    ]);

    DB::table('cargo_booking')->insert([
        'booking_ref_no' => $bookingRef,
        'cargo_item_id' => $item['id'],
        'quantity' => 1,
        'weight' => $item['weight'],
        'length' => $item['l'],
        'width' => $item['w'],
        'height' => $item['h'],
        'cbm' => $item['l'] * $item['w'] * $item['h'],
        'rate' => 100,
        'value_per_item' => 1000,
    ]);

    DB::table('cargo_receipt')->insert([
        'sender_id' => 1,
        'consignee_id' => 1,
        'cargo_item_id' => $item['id'],
        'voyage_id' => 1,
        'payment_id' => null,
        'booking_ref_no' => $bookingRef,
        'cargo_item_qty' => 1,
    ]);

    echo "✅ " . $item['name'] . ": {$item['weight']}kg\n";
}

echo "\n✅ ALL CARGO CREATED AND ASSIGNED TO VOYAGE 1\n";
echo "\nHatch will now display:\n";
echo "  - Breakable items STACKED (Glass, Electronics)\n";
echo "  - Robust items SPREAD (Steel, Pallets, Drums, Textiles, etc.)\n";
echo "  - Total: {$totalWeight}kg filling " . number_format(($totalWeight / 600) * 100, 1) . "% of hatch capacity\n";
