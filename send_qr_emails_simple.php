<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Booking;
use App\Models\PassengerTicket;
use App\Mail\PassengerTicketConfirmed;
use Illuminate\Support\Facades\Mail;

echo "=== SENDING QR EMAILS ===\n\n";

// Get booking
$booking = Booking::where('booking_status', 'Confirmed')->first();
if (!$booking) {
    echo "NO BOOKING\n";
    exit;
}

echo "Booking: " . $booking->booking_ref_no . "\n";

// Get tickets
$tickets = PassengerTicket::where('booking_ref_no', $booking->booking_ref_no)->with('passenger')->get();

echo "Passengers: " . count($tickets) . "\n\n";

// Send to each passenger
foreach ($tickets as $ticket) {
    $email = $ticket->passenger->passenger_email;
    $name = $ticket->passenger->passenger_firstname;
    
    Mail::to($email)->send(new PassengerTicketConfirmed($booking->booking_ref_no, $email));
    
    echo "✅ SENT TO {$name} ({$email})\n";
}

echo "\nDONE\n";
