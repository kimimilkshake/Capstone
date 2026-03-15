<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "📊 CHECKING YOUR 3-PASSENGER BOOKING\n";
echo "====================================\n\n";

// Get the most recent confirmed booking (most likely the one you just made)
$booking = DB::table('booking')
    ->where('booking_status', 'Confirmed')
    ->orderByDesc('booking_ref_no')
    ->first();

if (!$booking) {
    echo "❌ No confirmed booking found\n";
    exit;
}

echo "✓ Found Booking: #" . $booking->booking_ref_no . "\n";
echo "  Status: " . $booking->booking_status . "\n";
echo "  Voyage ID: " . $booking->voyage_id . "\n";
echo "  Created: " . $booking->created_at . "\n\n";

// Get voyage details
$voyage = DB::table('voyage')->where('voyage_id', $booking->voyage_id)->first();
if ($voyage) {
    echo "Voyage Details:\n";
    echo "  Code: " . $voyage->voyage_code . "\n";
    echo "  Departure: " . $voyage->voyage_departure_date . " " . $voyage->voyage_estimated_TD . "\n\n";
}

// Get all passengers
$passengers = DB::table('passenger_ticket')
    ->join('passenger', 'passenger_ticket.passenger_id', '=', 'passenger.passenger_id')
    ->where('passenger_ticket.booking_ref_no', $booking->booking_ref_no)
    ->orderBy('passenger_ticket.created_at')
    ->get([
        'passenger.passenger_firstname',
        'passenger.passenger_lastname',
        'passenger.passenger_type',
        'passenger.passenger_email',
        'passenger_ticket.pt_cot_no',
        'passenger_ticket.pt_ticket_price'
    ]);

echo "Passengers (" . count($passengers) . "):\n";
$totalAmount = 0;
foreach ($passengers as $index => $p) {
    $num = $index + 1;
    echo "  $num. " . $p->passenger_firstname . " " . $p->passenger_lastname . "\n";
    echo "     Type: " . $p->passenger_type . "\n";
    echo "     Email: " . $p->passenger_email . "\n";
    echo "     COT #: " . $p->pt_cot_no . "\n";
    echo "     Price: ₱" . number_format($p->pt_ticket_price, 2) . "\n\n";
    $totalAmount += $p->pt_ticket_price;
}

// Get payment
$payment = DB::table('payment')->where('booking_ref_no', $booking->booking_ref_no)->first();
if ($payment) {
    echo "Payment:\n";
    echo "  Total: ₱" . number_format($payment->total_amount, 2) . "\n";
    echo "  Status: " . $payment->payment_status . "\n";
    echo "  Mode: " . $payment->mode_of_payment . "\n\n";
}

// Check first passenger email
$firstPassenger = $passengers[0] ?? null;
if ($firstPassenger) {
    echo "📧 EMAIL DETAILS:\n";
    echo "============================\n";
    echo "Email Recipient: " . $firstPassenger->passenger_email . "\n";
    echo "Subject: Your Passenger Ticket Confirmed - Booking #" . $booking->booking_ref_no . "\n\n";

    echo "Expected Attachments:\n";
    foreach ($passengers as $p) {
        echo "  📄 ticket_" . $p->passenger_firstname . "_" . $p->passenger_lastname . ".pdf\n";
    }
}

echo "\n";
echo "🔍 CHECKING LOGS FOR EMAIL DELIVERY...\n";
echo "====================================\n";

// Check if email was sent
$bookingRef = $booking->booking_ref_no;
$logFile = __DIR__ . '/storage/logs/laravel.log';
$logs = shell_exec("tail -100 \"$logFile\" | grep -i \"SendTicketEmail\\|$bookingRef\" | tail -5");

if ($logs) {
    echo $logs;
} else {
    echo "No email logs found yet.\n";
}

echo "\n✅ Booking #" . $booking->booking_ref_no . " - Ready to dispatch email!\n";

?>