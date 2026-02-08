<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Checking for bookings with email: shemcardoza7@gmail.com\n";
echo "=======================================================\n\n";

$tickets = DB::table('passenger_ticket')
    ->join('passenger', 'passenger_ticket.passenger_id', '=', 'passenger.passenger_id')
    ->join('voyage', 'passenger_ticket.voyage_id', '=', 'voyage.voyage_id')
    ->join('booking', 'passenger_ticket.booking_ref_no', '=', 'booking.booking_ref_no')
    ->join('payment', 'booking.booking_ref_no', '=', 'payment.booking_ref_no')
    ->where('passenger.passenger_email', 'shemcardoza7@gmail.com')
    ->where('booking.booking_status', 'Confirmed')
    ->where('payment.payment_status', 'Completed')
    ->select(
        'passenger_ticket.booking_ref_no',
        'voyage.voyage_departure_date',
        'booking.booking_status',
        'payment.payment_status'
    )
    ->get();

if ($tickets->isEmpty()) {
    echo "❌ No confirmed bookings found for this email.\n";
    echo "   You need to create a test booking first or use a different email.\n\n";

    // Check if there are any bookings at all
    $anyBookings = DB::table('passenger_ticket')
        ->join('passenger', 'passenger_ticket.passenger_id', '=', 'passenger.passenger_id')
        ->join('booking', 'passenger_ticket.booking_ref_no', '=', 'booking.booking_ref_no')
        ->join('payment', 'booking.booking_ref_no', '=', 'payment.booking_ref_no')
        ->where('booking.booking_status', 'Confirmed')
        ->where('payment.payment_status', 'Completed')
        ->select('passenger.passenger_email', 'passenger_ticket.booking_ref_no')
        ->limit(5)
        ->get();

    if (!$anyBookings->isEmpty()) {
        echo "Available confirmed bookings in system:\n";
        foreach ($anyBookings as $booking) {
            echo "  - Email: {$booking->passenger_email}\n";
            echo "    Booking Ref: {$booking->booking_ref_no}\n\n";
        }
    }
} else {
    echo "✅ Found " . count($tickets) . " confirmed booking(s):\n\n";
    foreach ($tickets as $ticket) {
        echo "Booking Reference: {$ticket->booking_ref_no}\n";
        echo "Departure Date: {$ticket->voyage_departure_date}\n";
        echo "Status: {$ticket->booking_status} / {$ticket->payment_status}\n";
        echo "---\n";
    }
}
