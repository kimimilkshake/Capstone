<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Jobs\SendTicketEmail;

echo "Testing Ticket Request for: shemcardoza7@gmail.com\n";
echo "Departure Date: 2025-12-09\n";
echo "=======================================================\n\n";

// Simulate the ticket request
$email = 'shemcardoza7@gmail.com';
$departureDate = '2025-12-09';

// Find the ticket (same logic as controller)
$ticket = DB::table('passenger_ticket')
    ->join('voyage', 'passenger_ticket.voyage_id', '=', 'voyage.voyage_id')
    ->join('booking', 'passenger_ticket.booking_ref_no', '=', 'booking.booking_ref_no')
    ->join('payment', 'booking.booking_ref_no', '=', 'payment.booking_ref_no')
    ->join('passenger', 'passenger_ticket.passenger_id', '=', 'passenger.passenger_id')
    ->where('passenger.passenger_email', $email)
    ->whereDate('voyage.voyage_departure_date', $departureDate)
    ->where('booking.booking_status', 'Confirmed')
    ->where('payment.payment_status', 'Completed')
    ->select('passenger_ticket.booking_ref_no')
    ->first();

if (!$ticket) {
    echo "❌ No ticket found\n";
    exit(1);
}

echo "✅ Found ticket: {$ticket->booking_ref_no}\n";
echo "📧 Dispatching email to queue...\n\n";

// Dispatch the email job
SendTicketEmail::dispatch($ticket->booking_ref_no, $email);

echo "✅ Email job dispatched successfully!\n";
echo "\n";
echo "Next steps:\n";
echo "1. Start the queue worker: php artisan queue:work\n";
echo "2. The email will be sent to: {$email}\n";
echo "3. Check your inbox (and spam folder)\n";
