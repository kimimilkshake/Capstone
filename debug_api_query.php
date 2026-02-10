<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Voyage, App\Models\Booking, App\Models\CargoBooking;

echo "========================================\n";
echo "API DEBUG: Testing getPackingData query\n";
echo "========================================\n\n";

// Get voyage
$voyage = Voyage::with(['vessel.hatches', 'cargoReceipts.cargoBooking.cargoItem'])
    ->findOrFail(1);

echo "1️⃣ VOYAGE Found: {$voyage->voyage_code}\n";
echo "2️⃣ HATCHES:\n";
foreach ($voyage->vessel->hatches as $h) {
    echo "   Hatch {$h->hatch_id}: {$h->hatch_width}×{$h->hatch_height}×{$h->hatch_length}m, maxWeight={$h->hatch_weight_capacity}t\n";
}

echo "\n3️⃣ CONFIRMED BOOKINGS (via Booking model):\n";
$confirmedBookings = Booking::where('voyage_id', 1)
    ->whereRaw("LOWER(booking.booking_status) = ?", ['confirmed'])
    ->with('cargoBookings.cargoItem')
    ->get();

echo "   Total: " . count($confirmedBookings) . "\n";
foreach ($confirmedBookings as $b) {
    echo "   Booking {$b->booking_ref_no}: cargo_item_id={$b->cargo_item_id}\n";
    echo "      cargoBookings count: " . count($b->cargoBookings) . "\n";
    foreach ($b->cargoBookings as $cb) {
        echo "         CargoBooking ID {$cb->cargo_booking_id}: {$cb->weight}kg, {$cb->width}×{$cb->height}×{$cb->length}m\n";
        if ($cb->cargoItem) {
            echo "            CargoItem: {$cb->cargoItem->cargo_item_description}\n";
        } else {
            echo "            CargoItem: NOT FOUND\n";
        }
    }
}

echo "\n4️⃣ CARGO DATA (as API would build it):\n";
$cargoData = [];
foreach ($confirmedBookings as $booking) {
    $cb = $booking->cargoBookings->first();
    if ($cb && $cb->length && $cb->width && $cb->height) {
        $cargoData[] = [
            'id' => (string) ($cb->cargo_booking_id ?? $booking->booking_ref_no),
            'booking_ref' => $booking->booking_ref_no,
            'width' => (float) $cb->width,
            'height' => (float) $cb->height,
            'depth' => (float) $cb->length,
            'weight' => (float) ($cb->weight ?? 0),
            'quantity' => 1,
            'description' => ($cb->cargoItem ? $cb->cargoItem->cargo_item_description : 'Unknown'),
        ];
    }
}

echo "   Total cargo items: " . count($cargoData) . "\n";
foreach ($cargoData as $item) {
    echo "   {$item['booking_ref']}: {$item['description']} ({$item['weight']}kg)\n";
}

if (count($cargoData) == 0) {
    echo "\n🚨 FALLBACK PATH WILL BE USED - cargoData is empty!\n";
}
