<?php
// Comprehensive debugging script for ticket copy feature
// Run: php artisan tinker < debug_ticket_copy.php

use Illuminate\Support\Facades\DB;

echo "\n========== TICKET COPY DEBUGGING ==========\n\n";

// 1. Check route_port table
echo "1️⃣  ROUTE_PORT TABLE:\n";
$routePorts = DB::table('route_port')->get();
if ($routePorts->isEmpty()) {
    echo "   ❌ NO ROUTES FOUND!\n";
} else {
    echo "   Found " . $routePorts->count() . " routes:\n";
    foreach ($routePorts as $rp) {
        echo "   - ID: {$rp->route_port_id}, {$rp->route_origin} → {$rp->route_destination}\n";
    }
}

// 2. Check voyage table
echo "\n2️⃣  VOYAGE TABLE (today + future):\n";
$voyages = DB::table('voyage')
    ->where('voyage_departure_date', '>=', now()->toDateString())
    ->get();
if ($voyages->isEmpty()) {
    echo "   ❌ NO VOYAGES FOUND!\n";
} else {
    echo "   Found " . $voyages->count() . " voyages:\n";
    foreach ($voyages as $v) {
        echo "   - ID: {$v->voyage_id}, Route: {$v->route_port_id}, Date: {$v->voyage_departure_date}, Status: {$v->voyage_status}\n";
    }
}

// 3. Check booking table
echo "\n3️⃣  BOOKING TABLE (all statuses):\n";
$bookings = DB::table('booking')->get();
if ($bookings->isEmpty()) {
    echo "   ❌ NO BOOKINGS FOUND!\n";
} else {
    echo "   Found " . $bookings->count() . " bookings:\n";
    foreach ($bookings as $b) {
        echo "   - ID: {$b->booking_ref_no}, Status: {$b->booking_status}, Type: {$b->booking_type}, VoyageID: {$b->voyage_id}\n";
    }
}

// 4. Check payment table
echo "\n4️⃣  PAYMENT TABLE (all statuses):\n";
$payments = DB::table('payment')->get();
if ($payments->isEmpty()) {
    echo "   ❌ NO PAYMENTS FOUND!\n";
} else {
    echo "   Found " . $payments->count() . " payments:\n";
    foreach ($payments as $p) {
        echo "   - Booking: {$p->booking_ref_no}, Status: {$p->payment_status}, Amount: {$p->total_amount}\n";
    }
}

// 5. Check passenger table
echo "\n5️⃣  PASSENGER TABLE (all):\n";
$passengers = DB::table('passenger')->get();
if ($passengers->isEmpty()) {
    echo "   ❌ NO PASSENGERS FOUND!\n";
} else {
    echo "   Found " . $passengers->count() . " passengers:\n";
    foreach ($passengers as $p) {
        echo "   - ID: {$p->passenger_id}, Name: {$p->passenger_firstname} {$p->passenger_lastname}, Email: {$p->passenger_email}\n";
    }
}

// 6. Check passenger_ticket table
echo "\n6️⃣  PASSENGER_TICKET TABLE (all connections):\n";
$tickets = DB::table('passenger_ticket')->get();
if ($tickets->isEmpty()) {
    echo "   ❌ NO PASSENGER TICKETS FOUND!\n";
} else {
    echo "   Found " . $tickets->count() . " passenger tickets:\n";
    foreach ($tickets as $t) {
        $passenger = DB::table('passenger')->where('passenger_id', $t->passenger_id)->first();
        $voyage = DB::table('voyage')->where('voyage_id', $t->voyage_id)->first();
        $booking = DB::table('booking')->where('booking_ref_no', $t->booking_ref_no)->first();
        $payment = DB::table('payment')->where('booking_ref_no', $t->booking_ref_no)->first();

        $passengerName = $passenger ? "{$passenger->passenger_firstname} {$passenger->passenger_lastname}" : "UNKNOWN";
        $passengerEmail = $passenger ? $passenger->passenger_email : "NO EMAIL";
        $voyageDate = $voyage ? $voyage->voyage_departure_date : "NO DATE";
        $bookingStatus = $booking ? $booking->booking_status : "NO BOOKING";
        $paymentStatus = $payment ? $payment->payment_status : "NO PAYMENT";

        echo "   - PT ID: {$t->passenger_ticket_id}\n";
        echo "     Passenger: {$passengerName} ({$passengerEmail})\n";
        echo "     Voyage: {$t->voyage_id}, Date: {$voyageDate}\n";
        echo "     Booking: {$t->booking_ref_no}, Status: {$bookingStatus}\n";
        echo "     Payment: {$t->payment_id}, Status: {$paymentStatus}\n";
    }
}

// 7. Full query attempt with your data
echo "\n7️⃣  TESTING QUERY WITH SAMPLE DATA:\n";
echo "   (Using first passenger email as example)\n";

if (!$passengers->isEmpty()) {
    $testPassenger = $passengers->first();
    $testEmail = $testPassenger->passenger_email;

    echo "   Email: {$testEmail}\n";

    // Find what this passenger is booked for
    $testTickets = DB::table('passenger_ticket as pt')
        ->join('passenger as p', 'pt.passenger_id', '=', 'p.passenger_id')
        ->join('voyage as v', 'pt.voyage_id', '=', 'v.voyage_id')
        ->join('booking as b', 'pt.booking_ref_no', '=', 'b.booking_ref_no')
        ->where('p.passenger_email', $testEmail)
        ->select(
            'pt.passenger_id',
            'v.voyage_departure_date',
            'v.route_port_id',
            'b.booking_status',
            'p.passenger_email'
        )
        ->get();

    if ($testTickets->isEmpty()) {
        echo "   ❌ Query returned NO RESULTS for this email!\n";
    } else {
        echo "   ✓ Query found " . $testTickets->count() . " bookings:\n";
        foreach ($testTickets as $t) {
            $route = DB::table('route_port')->where('route_port_id', $t->route_port_id)->first();
            $routeStr = $route ? "{$route->route_origin} → {$route->route_destination}" : "Unknown";
            echo "   - Date: {$t->voyage_departure_date}, Route: {$routeStr}, Status: {$t->booking_status}\n";
        }
    }
}

echo "\n========== END DEBUG ==========\n";
