<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Voyage;
use App\Models\Booking;
use App\Models\CargoBooking;
use App\Models\CargoReceipt;

$voyageId = 1;
$voyage = Voyage::find($voyageId);
if (!$voyage) {
    echo "Voyage $voyageId not found\n";
    exit;
}

echo "=== Voyage $voyageId ===\n";
echo "Code: {$voyage->voyage_code}\n";
echo "Vessel: {$voyage->vessel->vessel_name}\n\n";

// Check all bookings for this voyage
echo "=== All Bookings for Voyage $voyageId ===\n";
$allBookings = Booking::where('voyage_id', $voyageId)->get();
echo "Total: " . $allBookings->count() . "\n";
foreach ($allBookings as $b) {
    echo "  Ref: {$b->booking_ref_no}, Status: {$b->booking_status}, Type: {$b->booking_type}\n";
}

// Check confirmed bookings
echo "\n=== Confirmed Bookings for Voyage $voyageId ===\n";
$confirmedBookings = Booking::where('voyage_id', $voyageId)
    ->whereRaw("LOWER(booking.booking_status) = ?", ['confirmed'])
    ->with('cargoBookings.cargoItem')
    ->get();
echo "Total: " . $confirmedBookings->count() . "\n";
foreach ($confirmedBookings as $b) {
    echo "  Ref: {$b->booking_ref_no}, Status: {$b->booking_status}\n";
    foreach ($b->cargoBookings as $cb) {
        echo "    - CargoBooking ID: {$cb->cargo_booking_id}, Item: {$cb->cargoItem->cargo_item_description}, Dims: {$cb->width}×{$cb->height}×{$cb->length}\n";
    }
}

// Check cargo receipts
echo "\n=== Cargo Receipts for Voyage $voyageId ===\n";
$receipts = CargoReceipt::where('voyage_id', $voyageId)->with('booking', 'cargoBooking.cargoItem')->get();
echo "Total: " . $receipts->count() . "\n";
foreach ($receipts as $r) {
    $bookingStatus = $r->booking ? $r->booking->booking_status : 'N/A';
    echo "  Receipt ID: {$r->cargo_receipt_id}, Booking Ref: {$r->booking_ref_no}, Status: {$bookingStatus}\n";
    if ($r->cargoBooking) {
        echo "    - Item: {$r->cargoBooking->cargoItem->cargo_item_description}, Dims: {$r->cargoBooking->width}×{$r->cargoBooking->height}×{$r->cargoBooking->length}\n";
    }
}

echo "\n=== Calling getPackingData endpoint ===\n";
$controller = new \App\Http\Controllers\CargoAutoPlacementController();
$request = new \Illuminate\Http\Request(['voyage_id' => 1]);
$response = $controller->getPackingData($request);
if (method_exists($response, 'getContent')) {
    $data = json_decode($response->getContent(), true);
    echo "Response cargo count: " . count($data['cargo'] ?? []) . "\n";
    foreach ($data['cargo'] ?? [] as $c) {
        echo "  - {$c['id']}: {$c['description']}, Dims: {$c['width']}×{$c['height']}×{$c['depth']}, Qty: {$c['quantity']}\n";
    }
}
