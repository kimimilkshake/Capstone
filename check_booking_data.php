<?php
require 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Booking;
use App\Models\Passenger;
use App\Models\PassengerTicket;

echo "=== RECENT BOOKINGS ===\n";
$bookings = Booking::orderByDesc('id')->limit(5)->get();
foreach ($bookings as $booking) {
    echo "\nBooking ID: {$booking->id}\n";
    echo "  Status: {$booking->booking_status}\n";
    echo "  Payment: {$booking->payment_status}\n";
    echo "  Passengers Count (relation): " . $booking->passengers()->count() . "\n";
    echo "  Passenger IDs: " . implode(', ', $booking->passengers()->pluck('id')->toArray()) . "\n";
}

echo "\n\n=== RECENT PASSENGER TICKETS ===\n";
$tickets = PassengerTicket::orderByDesc('id')->limit(10)->get();
foreach ($tickets as $ticket) {
    echo "\nTicket ID: {$ticket->id}\n";
    echo "  Passenger ID: {$ticket->passenger_id}\n";
    echo "  Booking ID: {$ticket->booking_id}\n";
    echo "  Voyage ID: {$ticket->voyage_id}\n";
}

echo "\n\n=== RECENT PASSENGERS ===\n";
$passengers = Passenger::orderByDesc('id')->limit(10)->get();
foreach ($passengers as $passenger) {
    echo "\nPassenger ID: {$passenger->id}\n";
    echo "  Name: {$passenger->passenger_name}\n";
    echo "  Email: {$passenger->passenger_email}\n";
    echo "  Booking IDs: " . implode(', ', $passenger->bookings()->pluck('id')->toArray()) . "\n";
}
