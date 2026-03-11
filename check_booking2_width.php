<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$booking2 = \App\Models\Booking::where('booking_ref_no', 2)->with(['cargoBookings.cargoItem', 'cargoBookings.measurementUnit'])->first();

if ($booking2) {
    echo "=== BOOKING 2 DETAILS ===\n";
    echo "Booking Ref: " . $booking2->booking_ref_no . "\n";
    echo "Status: " . $booking2->booking_status . "\n\n";

    $totalWidth = 0;
    $maxDimension = 0;

    foreach ($booking2->cargoBookings as $idx => $cargo) {
        echo "📦 Cargo Item " . ($idx + 1) . ":\n";
        echo "  - Item: " . ($cargo->cargoItem->cargo_item_name ?? 'N/A') . "\n";
        echo "  - Quantity: " . $cargo->quantity . "\n";
        echo "  - Weight: " . $cargo->weight . "kg\n";
        echo "  - Dimensions (L×W×H): " . $cargo->length . " × " . $cargo->width . " × " . $cargo->height . " cm\n";
        echo "  - Width specifically: " . $cargo->width . " cm\n";
        echo "  - CBM: " . $cargo->cbm . "\n";
        echo "  - Measurement Unit: " . ($cargo->measurementUnit->measurement_unit_abbreviation ?? 'cm') . "\n\n";

        $totalWidth += $cargo->width;
        $maxDimension = max($maxDimension, $cargo->width, $cargo->length, $cargo->height);
    }

    echo "📊 SUMMARY FOR BOOKING 2 (RIGHT ZONE):\n";
    echo "  - Total Width Sum: " . $totalWidth . " cm\n";
    echo "  - Max Dimension: " . $maxDimension . " cm\n";
} else {
    echo "Booking 2 not found\n";
}
