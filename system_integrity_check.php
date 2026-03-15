<?php
require 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "\n╔════════════════════════════════════════════════════════════╗\n";
echo "║          SYSTEM INTEGRITY CHECK - COMPREHENSIVE             ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

// ==================== PASSENGER BOOKINGS CHECK ====================
echo "📋 PASSENGER BOOKINGS INTEGRITY CHECK\n";
echo str_repeat("─", 60) . "\n";

// 1. Check for bookings without passengers
$bookingsNoPassengers = DB::table('booking as b')
    ->leftJoin('passenger_ticket as pt', 'b.booking_ref_no', '=', 'pt.booking_ref_no')
    ->whereNull('pt.booking_ref_no')
    ->select('b.booking_ref_no', 'b.booking_status', 'b.payment_status')
    ->count();

if ($bookingsNoPassengers > 0) {
    echo "⚠️  WARNING: $bookingsNoPassengers booking(s) have NO passengers assigned!\n";
} else {
    echo "✓ All bookings have passengers assigned\n";
}

// 2. Check for duplicate passengers in same booking
$duplicatePassengers = DB::table('passenger_ticket')
    ->select('booking_ref_no', 'passenger_id', DB::raw('COUNT(*) as count'))
    ->groupBy('booking_ref_no', 'passenger_id')
    ->having(DB::raw('COUNT(*)'), '>', 1)
    ->count();

if ($duplicatePassengers > 0) {
    echo "⚠️  WARNING: $duplicatePassengers duplicate passenger(s) found in bookings!\n";
} else {
    echo "✓ No duplicate passengers in bookings\n";
}

// 3. Check for orphaned passenger records (passengers with no bookings)
$orphanedPassengers = DB::table('passenger as p')
    ->leftJoin('passenger_ticket as pt', 'p.passenger_id', '=', 'pt.passenger_id')
    ->whereNull('pt.passenger_id')
    ->count();

if ($orphanedPassengers > 0) {
    echo "⚠️  WARNING: $orphanedPassengers orphaned passenger record(s) found!\n";
} else {
    echo "✓ No orphaned passenger records\n";
}

// 4. Check for payment-booking mismatches
$paymentBookingMismatch = DB::table('payment as p')
    ->leftJoin('booking as b', 'p.booking_ref_no', '=', 'b.booking_ref_no')
    ->whereNull('b.booking_ref_no')
    ->count();

if ($paymentBookingMismatch > 0) {
    echo "⚠️  WARNING: $paymentBookingMismatch payment(s) have NO matching booking!\n";
} else {
    echo "✓ All payments have matching bookings\n";
}

// 5. Check booking status consistency
$inconsistentStatus = DB::table('booking as b')
    ->join('payment as p', 'b.booking_ref_no', '=', 'p.booking_ref_no')
    ->where('b.booking_status', 'Confirmed')
    ->whereNotIn('p.payment_status', ['Completed', 'Pending'])
    ->count();

if ($inconsistentStatus > 0) {
    echo "⚠️  WARNING: $inconsistentStatus booking(s) is Confirmed but payment status is inconsistent!\n";
} else {
    echo "✓ Booking and payment statuses are consistent\n";
}

// 6. Recent passenger bookings summary
echo "\n📊 Recent Passenger Bookings:\n";
$recentBookings = DB::table('booking')
    ->orderBy('created_at', 'desc')
    ->limit(5)
    ->get(['booking_ref_no', 'booking_status', 'payment_status', 'created_at']);

foreach ($recentBookings as $b) {
    $passengerCount = DB::table('passenger_ticket')
        ->where('booking_ref_no', $b->booking_ref_no)
        ->count();
    echo "  Booking: {$b->booking_ref_no} | Status: {$b->booking_status} | Payment: {$b->payment_status} | Passengers: {$passengerCount}\n";
}

// ==================== CARGO BOOKINGS CHECK ====================
echo "\n\n📦 CARGO BOOKINGS INTEGRITY CHECK\n";
echo str_repeat("─", 60) . "\n";

// 1. Check for cargo bookings without items
$cargoNoItems = DB::table('cargo_booking as cb')
    ->leftJoin('cargo_item as ci', 'cb.cargo_item_id', '=', 'ci.cargo_item_id')
    ->whereNull('ci.cargo_item_id')
    ->count();

if ($cargoNoItems > 0) {
    echo "⚠️  WARNING: $cargoNoItems cargo booking(s) have NO items!\n";
} else {
    echo "✓ All cargo bookings have items\n";
}

// 2. Check for cargo bookings without voyage
$cargoNoVoyage = DB::table('cargo_booking as cb')
    ->leftJoin('voyage as v', 'cb.voyage_id', '=', 'v.voyage_id')
    ->whereNull('v.voyage_id')
    ->count();

if ($cargoNoVoyage > 0) {
    echo "⚠️  WARNING: $cargoNoVoyage cargo booking(s) have NO voyage assigned!\n";
} else {
    echo "✓ All cargo bookings have voyages assigned\n";
}

// 3. Check for orphaned cargo receipts
$orphanedReceipts = DB::table('cargo_receipt as cr')
    ->leftJoin('cargo_booking as cb', 'cr.cargo_booking_id', '=', 'cb.cargo_booking_id')
    ->whereNull('cb.cargo_booking_id')
    ->count();

if ($orphanedReceipts > 0) {
    echo "⚠️  WARNING: $orphanedReceipts orphaned cargo receipt(s) found!\n";
} else {
    echo "✓ No orphaned cargo receipts\n";
}

// 4. Check cargo booking approval status
$unapprovedCargo = DB::table('cargo_booking')
    ->where('approved_by_staff_id', null)
    ->count();

echo "ℹ️  Unapproved cargo bookings: $unapprovedCargo\n";

// 5. Recent cargo bookings summary
echo "\n📊 Recent Cargo Bookings:\n";
$recentCargo = DB::table('cargo_booking')
    ->orderBy('created_at', 'desc')
    ->limit(5)
    ->get(['cargo_booking_id', 'voyage_id', 'approved_by_staff_id', 'created_at']);

foreach ($recentCargo as $c) {
    $status = $c->approved_by_staff_id ? 'Approved' : 'Pending';
    $itemCount = DB::table('cargo_item')->where('cargo_booking_id', $c->cargo_booking_id)->count();
    echo "  Cargo ID: {$c->cargo_booking_id} | Voyage: {$c->voyage_id} | Status: {$status} | Items: {$itemCount}\n";
}

// ==================== BILL OF LADING CHECK ====================
echo "\n\n📄 BILL OF LADING INTEGRITY CHECK\n";
echo str_repeat("─", 60) . "\n";

// 1. Check for BOL records without cargo booking
$bolNoCargo = DB::table('bill_of_lading as bol')
    ->leftJoin('cargo_booking as cb', 'bol.cargo_booking_id', '=', 'cb.cargo_booking_id')
    ->whereNull('cb.cargo_booking_id')
    ->count();

if ($bolNoCargo > 0) {
    echo "⚠️  WARNING: $bolNoCargo BOL record(s) have NO cargo booking!\n";
} else {
    echo "✓ All BOL records have cargo bookings\n";
}

// 2. Check voyage data in BOL
$bolNoVoyage = DB::table('bill_of_lading')
    ->whereNull('voyage_id')
    ->count();

echo "ℹ️  BOL records without voyage_id: $bolNoVoyage\n";

// 3. Recent BOL summary
echo "\n📊 Recent Bill of Lading Records:\n";
$recentBOL = DB::table('bill_of_lading')
    ->orderBy('created_at', 'desc')
    ->limit(5)
    ->get(['bill_of_lading_id', 'cargo_booking_id', 'voyage_id']);

if ($recentBOL->isEmpty()) {
    echo "  No BOL records found yet\n";
} else {
    foreach ($recentBOL as $bol) {
        echo "  BOL ID: {$bol->bill_of_lading_id} | Cargo: {$bol->cargo_booking_id} | Voyage: {$bol->voyage_id}\n";
    }
}

// ==================== AUTOPLACEMENT CHECK ====================
echo "\n\n🎯 AUTOPLACEMENT INTEGRITY CHECK\n";
echo str_repeat("─", 60) . "\n";

// 1. Check vessels with hatches
$vesselsWithHatches = DB::table('vessel')
    ->join('hatch', 'vessel.vessel_id', '=', 'hatch.vessel_id')
    ->select('vessel.vessel_id', 'vessel.vessel_name', DB::raw('COUNT(hatch.hatch_id) as hatch_count'))
    ->groupBy('vessel.vessel_id', 'vessel.vessel_name')
    ->count();

echo "ℹ️  Vessels with hatches: $vesselsWithHatches\n";

// 2. Check voyages and their vessels
$voyagesNoVessel = DB::table('voyage as v')
    ->leftJoin('vessel as ve', 'v.vessel_id', '=', 've.vessel_id')
    ->whereNull('ve.vessel_id')
    ->count();

if ($voyagesNoVessel > 0) {
    echo "⚠️  WARNING: $voyagesNoVessel voyage(s) have NO vessel assigned!\n";
} else {
    echo "✓ All voyages have vessels assigned\n";
}

// 3. Check routes
$routesCheck = DB::table('route')->count();
echo "ℹ️  Total routes configured: $routesCheck\n";

$routePortsCheck = DB::table('route_port')->count();
echo "ℹ️  Total route-port combinations: $routePortsCheck\n";

// 4. Recent voyages
echo "\n📊 Recent Voyages:\n";
$recentVoyages = DB::table('voyage')
    ->orderBy('voyage_departure_date', 'desc')
    ->limit(5)
    ->get(['voyage_id', 'vessel_id', 'voyage_departure_date', 'voyage_status']);

foreach ($recentVoyages as $v) {
    echo "  Voyage: {$v->voyage_id} | Vessel: {$v->vessel_id} | Departure: {$v->voyage_departure_date} | Status: {$v->voyage_status}\n";
}

// ==================== SUMMARY ====================
echo "\n\n" . str_repeat("═", 60) . "\n";
echo "✓ SYSTEM CHECK COMPLETE\n";
echo str_repeat("═", 60) . "\n";
echo "If you see any ⚠️ warnings above, please investigate those records.\n";
echo "All ✓ marks indicate healthy system status.\n\n";
