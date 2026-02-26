<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

// Get the voyage
$voyage = DB::table('voyage')->where('voyage_code', 'F1CEBBAY202602-001')->first();

if (!$voyage) {
    echo "Voyage not found!\n";
    exit(1);
}

echo "Found voyage: " . $voyage->voyage_code . " (ID: " . $voyage->voyage_id . ")\n\n";

// Get or create cargo items
$cargoItems = [
    ['description' => 'Rice Sacks (50kg)', 'classification' => 'Dry Goods'],
    ['description' => 'Steel Coils', 'classification' => 'Metal/Heavy'],
    ['description' => 'Wooden Pallets', 'classification' => 'Wood Products'],
    ['description' => 'Plastic Drums', 'classification' => 'Plastic'],
    ['description' => 'Glass Bottles (Cartons)', 'classification' => 'Fragile'],
    ['description' => 'Electronics Equipment', 'classification' => 'Electronics'],
];

$cargoItemIds = [];
foreach ($cargoItems as $item) {
    $existingItem = DB::table('cargo_item')
        ->where('cargo_item_description', $item['description'])
        ->first();

    if (!$existingItem) {
        $id = DB::table('cargo_item')->insertGetId([
            'cargo_item_description' => $item['description'],
            'cargo_item_classification' => $item['classification'],
            'cargo_item_freight' => 100,
            'cargo_item_arrastre' => 50,
            'route_port_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $cargoItemIds[] = $id;
        echo "Created cargo item: " . $item['description'] . " (ID: $id)\n";
    } else {
        $cargoItemIds[] = $existingItem->cargo_item_id;
        echo "Found existing cargo item: " . $item['description'] . "\n";
    }
}

echo "\n=== Creating Sample Cargo ===\n";

// Sample cargo data with dimensions
// Get or create sender and consignee
$sender = DB::table('sender')->first();
if (!$sender) {
    $senderId = DB::table('sender')->insertGetId([
        'shipper_company' => 'Sample Shipper',
        'shipper_contact_number' => '+63123456789',
        'shipper_email' => 'shipper@example.com',
        'shipper_address' => 'Sample Address',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    echo "Created sender (ID: $senderId)\n";
} else {
    $senderId = $sender->sender_id;
    echo "Using existing sender (ID: $senderId)\n";
}

$consignee = DB::table('consignee')->first();
if (!$consignee) {
    $consigneeId = DB::table('consignee')->insertGetId([
        'receiver_company' => 'Sample Receiver',
        'receiver_contact_number' => '+63987654321',
        'receiver_email' => 'receiver@example.com',
        'receiver_address' => 'Sample Address',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    echo "Created consignee (ID: $consigneeId)\n";
} else {
    $consigneeId = $consignee->consignee_id;
    echo "Using existing consignee (ID: $consigneeId)\n";
}

// Sample cargo data with dimensions
$sampleCargo = [
    ['item_idx' => 0, 'qty' => 20, 'length' => 1.2, 'width' => 0.8, 'height' => 0.6, 'weight' => 50],
    ['item_idx' => 1, 'qty' => 5, 'length' => 2.0, 'width' => 1.5, 'height' => 1.0, 'weight' => 150],
    ['item_idx' => 2, 'qty' => 15, 'length' => 1.0, 'width' => 1.0, 'height' => 1.2, 'weight' => 80],
    ['item_idx' => 3, 'qty' => 10, 'length' => 0.9, 'width' => 0.9, 'height' => 1.0, 'weight' => 60],
    ['item_idx' => 4, 'qty' => 30, 'length' => 0.6, 'width' => 0.4, 'height' => 0.5, 'weight' => 25],
    ['item_idx' => 5, 'qty' => 8, 'length' => 1.5, 'width' => 1.0, 'height' => 0.8, 'weight' => 100],
];

$baseRefNo = 500000;
foreach ($sampleCargo as $idx => $cargo) {
    $refNo = $baseRefNo + $idx;
    $itemId = $cargoItemIds[$cargo['item_idx']];

    // Create cargo booking with dimensions
    try {
        DB::table('cargo_booking')->insert([
            'booking_ref_no' => (string) $refNo,
            'cargo_item_id' => $itemId,
            'quantity' => $cargo['qty'],
            'length' => $cargo['length'],
            'width' => $cargo['width'],
            'height' => $cargo['height'],
            'weight' => $cargo['weight'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    } catch (\Exception $e) {
        // Cargo booking might fail due to foreign key, that's okay
    }

    // Create cargo receipt
    try {
        DB::table('cargo_receipt')->insert([
            'sender_id' => $senderId,
            'consignee_id' => $consigneeId,
            'voyage_id' => $voyage->voyage_id,
            'booking_ref_no' => $refNo,
            'cargo_item_id' => $itemId,
            'cargo_item_qty' => $cargo['qty'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        echo "Created cargo receipt for voyage {$voyage->voyage_id}\n";
    } catch (\Exception $e) {
        echo "Error creating receipt: " . $e->getMessage() . "\n";
    }
}

echo "\n✓ Done! Sample cargo data created.\n";
echo "Try selecting the voyage again in the staff cargo placement page.\n";
