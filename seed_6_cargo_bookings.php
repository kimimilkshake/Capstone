<?php
/**
 * Seed 6 confirmed cargo bookings for voyage 1 testing
 * Run: php seed_6_cargo_bookings.php
 */

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Booking;
use App\Models\CargoBooking;
use App\Models\CargoItem;
use App\Models\CargoReceipt;
use App\Models\Sender;
use App\Models\Consignee;
use Illuminate\Support\Facades\DB;

// Define cargo items with dimensions and weights (use existing cargo item IDs)
$cargoData = [
    [
        'cargo_item_id' => 1,
        'description' => 'Rice Sacks (50kg)',
        'weight' => 50,      // kg
        'width' => 0.8,      // meters
        'height' => 1.2,
        'depth' => 0.6,
    ],
    [
        'cargo_item_id' => 2,
        'description' => 'Steel Coils',
        'weight' => 150,
        'width' => 1.5,
        'height' => 1.0,
        'depth' => 2.0,
    ],
    [
        'cargo_item_id' => 3,
        'description' => 'Wooden Pallets',
        'weight' => 80,
        'width' => 1.0,
        'height' => 1.2,
        'depth' => 0.8,
    ],
    [
        'cargo_item_id' => 4,
        'description' => 'Plastic Drums',
        'weight' => 90,
        'width' => 0.9,
        'height' => 1.1,
        'depth' => 0.9,
    ],
    [
        'cargo_item_id' => 5,
        'description' => 'Glass Bottles (Cartons)',
        'weight' => 60,
        'width' => 0.6,
        'height' => 0.8,
        'depth' => 0.6,
    ],
    [
        'cargo_item_id' => 8,
        'description' => 'Cement Bags (50kg)',
        'weight' => 120,
        'width' => 0.5,
        'height' => 1.5,
        'depth' => 0.4,
    ],
];

try {
    // Get or create test sender and consignee
    $sender = Sender::firstOrCreate(
        ['sender_id' => 999],
        [
            'sender_name' => 'Test Sender Co.',
            'sender_contactno' => '09123456789',
            'sender_email' => 'sender@test.com',
            'sender_tin' => '123456789',
        ]
    );

    $consignee = Consignee::firstOrCreate(
        ['consignee_id' => 999],
        [
            'consignee_name' => 'Test Consignee Co.',
            'consignee_contactno' => '09987654321',
        ]
    );

    echo "Creating 6 confirmed cargo bookings for Voyage 1...\n";

    foreach ($cargoData as $idx => $cargo) {
        // Use existing cargo item
        $cargoItem = CargoItem::find($cargo['cargo_item_id']);
        if (!$cargoItem) {
            echo "  Error: Cargo item ID {$cargo['cargo_item_id']} not found\n";
            continue;
        }

        // Create booking
        $booking = Booking::create([
            'booking_type' => 'cargo',
            'booking_status' => 'Confirmed',  // Use proper case: Confirmed
            'voyage_id' => 1,  // Voyage 1
            'sender_id' => $sender->sender_id,
            'consignee_id' => $consignee->consignee_id,
            'cargo_item_id' => $cargoItem->cargo_item_id,
        ]);

        echo "  Created Booking #" . $booking->booking_ref_no . "\n";

        // Create cargo booking
        $cargoBooking = CargoBooking::create([
            'booking_ref_no' => $booking->booking_ref_no,
            'cargo_item_id' => $cargoItem->cargo_item_id,
            'quantity' => 1,
            'weight' => $cargo['weight'],      // in kg
            'length' => $cargo['depth'],
            'width' => $cargo['width'],
            'height' => $cargo['height'],
            'cbm' => $cargo['width'] * $cargo['height'] * $cargo['depth'],
            'rate' => 150,
            'value_per_item' => 1000,
        ]);

        echo "    - CargoBooking #" . $cargoBooking->cargo_booking_id . " (" . $cargo['description'] . ")\n";
        echo "      Weight: " . $cargo['weight'] . "kg | Dimensions: " . $cargo['width'] . "×" . $cargo['height'] . "×" . $cargo['depth'] . "m\n";

        // Create cargo receipt (confirms the cargo)
        $receipt = CargoReceipt::create([
            'booking_ref_no' => $booking->booking_ref_no,
            'sender_id' => $sender->sender_id,
            'consignee_id' => $consignee->consignee_id,
            'cargo_item_id' => $cargoItem->cargo_item_id,
            'voyage_id' => 1,
            'cargo_item_qty' => 1,
        ]);

        echo "    - CargoReceipt #" . $receipt->cargo_receipt_id . " (CONFIRMED)\n\n";
    }

    // Display summary
    $bookings = Booking::where('voyage_id', 1)->where('booking_status', 'confirmed')->count();
    $totalWeight = CargoBooking::join('booking', 'cargo_booking.booking_ref_no', '=', 'booking.booking_ref_no')
        ->where('booking.voyage_id', 1)
        ->where('booking.booking_status', 'confirmed')
        ->sum('cargo_booking.weight');

    echo "=====================================\n";
    echo "Summary for Voyage 1:\n";
    echo "  Total Confirmed Bookings: " . $bookings . "\n";
    echo "  Total Weight: " . $totalWeight . " kg (" . ($totalWeight / 1000) . " tons)\n";
    echo "  Hatch Capacity: 0.6 tons (600 kg) per hatch\n";
    echo "  Cargo fits: " . ($totalWeight <= 1200 ? 'YES' . " across 2 hatches" : 'NO - exceeds capacity') . "\n";
    echo "=====================================\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "Done!\n";
