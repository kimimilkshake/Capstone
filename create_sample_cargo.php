<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

// Get the voyage
$voyage = DB::table('voyage')->where('voyage_code', 'F1CEBBAY202602-001')->first();

if (!$voyage) {
    echo "❌ Voyage not found!\n";
    exit(1);
}

echo "✓ Found voyage: " . $voyage->voyage_code . " (ID: " . $voyage->voyage_id . ")\n\n";

// Get sender and consignee (create if not exist)
$sender = DB::table('sender')->first();
if (!$sender) {
    $senderId = DB::table('sender')->insertGetId([
        'sender_name' => 'Sample Shipper Co.',
        'sender_contactno' => '+63123456789',
        'sender_email' => 'shipper@example.com',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
} else {
    $senderId = $sender->sender_id;
}

$consignee = DB::table('consignee')->first();
if (!$consignee) {
    $consigneeId = DB::table('consignee')->insertGetId([
        'consignee_name' => 'Sample Receiver Co.',
        'consignee_contactno' => '+63987654321',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
} else {
    $consigneeId = $consignee->consignee_id;
}

// Create cargo items if not exist
$cargoItemsData = [
    ['desc' => 'Rice Sacks (50kg)', 'class' => 'Dry Goods'],
    ['desc' => 'Steel Coils', 'class' => 'Metal/Heavy'],
    ['desc' => 'Wooden Pallets', 'class' => 'Wood Products'],
    ['desc' => 'Plastic Drums', 'class' => 'Plastic'],
    ['desc' => 'Glass Bottles (Cartons)', 'class' => 'Fragile'],
    ['desc' => 'Electronics Equipment', 'class' => 'Electronics'],
];

$cargoItemIds = [];
foreach ($cargoItemsData as $item) {
    $existing = DB::table('cargo_item')
        ->where('cargo_item_description', $item['desc'])
        ->first();

    if (!$existing) {
        $id = DB::table('cargo_item')->insertGetId([
            'cargo_item_description' => $item['desc'],
            'cargo_item_classification' => $item['class'],
            'cargo_item_freight' => 100,
            'cargo_item_arrastre' => 50,
            'route_port_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $cargoItemIds[$item['desc']] = $id;
    } else {
        $cargoItemIds[$item['desc']] = $existing->cargo_item_id;
    }
}

// Sample cargo with dimensions
$baseBRef = 800000;
$sampleCargo = [
    ['desc' => 'Rice Sacks (50kg)', 'qty' => 20, 'length' => 1.2, 'width' => 0.8, 'height' => 0.6, 'weight' => 50],
    ['desc' => 'Steel Coils', 'qty' => 5, 'length' => 2.0, 'width' => 1.5, 'height' => 1.0, 'weight' => 150],
    ['desc' => 'Wooden Pallets', 'qty' => 15, 'length' => 1.0, 'width' => 1.0, 'height' => 1.2, 'weight' => 80],
    ['desc' => 'Plastic Drums', 'qty' => 10, 'length' => 0.9, 'width' => 0.9, 'height' => 1.0, 'weight' => 60],
    ['desc' => 'Glass Bottles (Cartons)', 'qty' => 30, 'length' => 0.6, 'width' => 0.4, 'height' => 0.5, 'weight' => 25],
    ['desc' => 'Electronics Equipment', 'qty' => 8, 'length' => 1.5, 'width' => 1.0, 'height' => 0.8, 'weight' => 100],
];

echo "=== Creating Cargo Data ===\n";

foreach ($sampleCargo as $idx => $cargo) {
    $bref = $baseBRef + $idx;
    $itemId = $cargoItemIds[$cargo['desc']];

    // Create booking first
    try {
        DB::table('booking')->insert([
            'booking_date' => now(),
            'booking_status' => 'Confirmed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        // Get the last inserted booking ref
        $lastBooking = DB::table('booking')->latest('booking_ref_no')->first();
        $bref = $lastBooking->booking_ref_no;
        echo "✓ Created booking $bref\n";
    } catch (\Exception $e) {
        // Might already exist
        if (!str_contains($e->getMessage(), 'Duplicate')) {
            echo "⚠ Booking $bref: " . substr($e->getMessage(), 0, 50) . "...\n";
            continue;
        }
    }

    // Create cargo booking with dimensions
    try {
        DB::table('cargo_booking')->insert([
            'booking_ref_no' => $bref,
            'cargo_item_id' => $itemId,
            'quantity' => $cargo['qty'],
            'length' => $cargo['length'],
            'width' => $cargo['width'],
            'height' => $cargo['height'],
            'weight' => $cargo['weight'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        echo "✓ Created cargo booking $bref with dimensions\n";
    } catch (\Exception $e) {
        echo "❌ Cargo booking $bref failed: " . substr($e->getMessage(), 0, 80) . "\n";
        continue;
    }

    // Create cargo receipt
    try {
        DB::table('cargo_receipt')->insert([
            'sender_id' => $senderId,
            'consignee_id' => $consigneeId,
            'cargo_item_id' => $itemId,
            'voyage_id' => $voyage->voyage_id,
            'booking_ref_no' => $bref,
            'cargo_item_qty' => $cargo['qty'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        echo "✓ Created cargo receipt for booking $bref\n";
    } catch (\Exception $e) {
        echo "❌ Cargo receipt for booking $bref failed: " . substr($e->getMessage(), 0, 80) . "\n";
    }
}

echo "\n✅ Done! Sample cargo has been added to voyage " . $voyage->voyage_code . "\n";
echo "Try selecting the voyage again in Staff > Cargo Auto Placement\n";
