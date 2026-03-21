<?php
require 'vendor/autoload.php';
$app = require 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Booking;
use App\Models\PassengerTicket;
use App\Mail\PassengerTicketConfirmed;
use Illuminate\Support\Facades\Mail;

echo "📧 Send Integrated QR Code Emails\n\n";

// Find a recent confirmed booking
$booking = Booking::where('booking_status', 'Confirmed')
    ->whereHas('passengerTickets')
    ->orderBy('created_at', 'desc')
    ->first();

if (!$booking) {
    echo "❌ No confirmed bookings found\n";
    exit;
}

echo "✅ Found Booking: " . $booking->booking_ref_no . "\n";

$tickets = PassengerTicket::where('booking_ref_no', $booking->booking_ref_no)
    ->with('passenger')
    ->get();

echo "✅ Passengers:\n";
foreach ($tickets as $ticket) {
    echo "   - " . $ticket->passenger->passenger_firstname . " " . $ticket->passenger->passenger_lastname . " (" . $ticket->passenger->passenger_email . ")\n";
}
echo "\n";

// Send email to all passengers
echo "📨 Sending Emails...\n";
try {
    foreach ($tickets as $ticket) {
        $passengerEmail = $ticket->passenger->passenger_email;
        $passengerName = $ticket->passenger->passenger_firstname . ' ' . $ticket->passenger->passenger_lastname;
        
        Mail::to($passengerEmail)
            ->send(new PassengerTicketConfirmed($booking->booking_ref_no, $passengerEmail));
        
        echo "   ✅ Sent to $passengerName ($passengerEmail)\n";
    }
    
    echo "\n✅ All emails sent successfully!\n";
    echo "   Booking: " . $booking->booking_ref_no . "\n";
    echo "   Passengers: " . $tickets->count() . "\n\n";
    
    echo "📦 PDF Attachments (one per passenger):\n";
    foreach ($tickets as $ticket) {
        $filename = 'ticket_' . str_replace(' ', '_', $ticket->passenger->passenger_firstname) . '_' . str_replace(' ', '_', $ticket->passenger->passenger_lastname) . '.pdf';
        echo "   📄 {$filename}\n";
    }
    
    echo "\n✅ All emails sent with integrated QR codes!\n";
    
} catch (\Exception $e) {
    echo "❌ Failed to send email: " . $e->getMessage() . "\n";
    exit;
}
