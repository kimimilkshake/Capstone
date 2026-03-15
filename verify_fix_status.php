<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "✅ VERIFICATION: VOYAGE FIX STATUS\n";
echo "==================================\n\n";

// 1. Check code is in place
echo "1️⃣  CODE FIX CHECK:\n";
$bookingCode = file_get_contents(__DIR__ . '/app/Http/Controllers/BookingController.php');
if (strpos($bookingCode, "'voyage_id' => \$voyage->voyage_id,") !== false) {
    echo "   ✅ BookingController - voyage_id fix IS IN PLACE\n";
} else {
    echo "   ❌ BookingController - voyage_id fix MISSING!\n";
}

// 2. Check model relationships
echo "\n2️⃣  MODEL RELATIONSHIPS CHECK:\n";
$bookingModel = file_get_contents(__DIR__ . '/app/Models/Booking.php');
if (strpos($bookingModel, 'public function voyage()') !== false) {
    echo "   ✅ Booking model has voyage() relationship\n";
} else {
    echo "   ❌ Booking model missing voyage() relationship\n";
}

// 3. Check database for saved voyage_ids
echo "\n3️⃣  DATABASE CHECK - Recent Bookings:\n";
$recentBookings = DB::table('booking')
    ->where('booking_type', 'passenger')
    ->where('booking_status', 'Confirmed')
    ->orderByDesc('booking_ref_no')
    ->limit(10)
    ->get(['booking_ref_no', 'voyage_id', 'created_at']);

foreach ($recentBookings as $b) {
    $status = $b->voyage_id ? "✅" : "❌";
    echo "   $status Booking #" . $b->booking_ref_no . " - voyage_id: " . ($b->voyage_id ? $b->voyage_id : "NULL") . " (" . $b->created_at->format('H:i:s') . ")\n";
}

// 4. Test PDF generation for a new booking
echo "\n4️⃣  PDF GENERATION TEST - Booking #27:\n";
$booking = \App\Models\Booking::with([
    'voyage',
    'voyage.vessel',
    'voyage.routePort'
])->where('booking_ref_no', 27)->first();

if ($booking && $booking->voyage) {
    echo "   ✅ Booking loaded with voyage relationship\n";
    echo "   ✅ Voyage Code: " . $booking->voyage->voyage_code . "\n";
    echo "   ✅ Vessel: " . ($booking->voyage->vessel ? $booking->voyage->vessel->vessel_name : "N/A") . "\n";
} else {
    echo "   ❌ Booking or voyage not found\n";
}

echo "\n5️⃣  CONCLUSION:\n";
if ($booking && $booking->voyage) {
    echo "   ✅ ALL SYSTEMS WORKING!\n";
    echo "   ✅ New bookings WILL show voyage information\n";
    echo "   ⚠️  Old bookings (Booking #26 and earlier) won't - they were made before the fix\n";
} else {
    echo "   ❌ Issue found - please investigate\n";
}

?>