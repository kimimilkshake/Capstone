<?php
/**
 * Test: Verify staff booking email works with PDFs
 * Simulates: Staff creates a "Physical" payment booking
 */

require 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

echo "\n╔════════════════════════════════════════════════════════════════╗\n";
echo "║     STAFF BOOKING EMAIL SYSTEM TEST                           ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

// Find a recent staff booking with Physical payment mode (confirmed with completed payment)
echo "Looking for staff bookings with completed payments...\n";

$staffBooking = DB::table('booking')
    ->join('payment', 'booking.booking_ref_no', '=', 'payment.booking_ref_no')
    ->where('booking.booking_type', 'passenger')
    ->where('booking.booking_status', 'Confirmed')
    ->where('payment.payment_status', 'Completed')
    ->orderBy('payment.payment_date', 'desc')
    ->select('booking.booking_ref_no', 'payment.mode_of_payment', 'payment.payment_date', 'payment.payment_status')
    ->first();

if (!$staffBooking) {
    echo "❌ No completed staff bookings found in database.\n";
    echo "   Need to create a test booking first with 'Physical' payment mode.\n\n";
    exit(1);
}

echo "✓ Found booking: {$staffBooking->booking_ref_no}\n";
echo "  Mode: {$staffBooking->mode_of_payment}\n";
echo "  Status: {$staffBooking->payment_status}\n";
echo "  Date: {$staffBooking->payment_date}\n\n";

// Get passenger info
$passengers = DB::table('passenger')
    ->join('passenger_ticket', 'passenger.passenger_id', '=', 'passenger_ticket.passenger_id')
    ->where('passenger_ticket.booking_ref_no', $staffBooking->booking_ref_no)
    ->select('passenger.passenger_firstname', 'passenger.passenger_lastname', 'passenger.passenger_email')
    ->get();

echo "Passengers in this booking:\n";
foreach ($passengers as $p) {
    echo "  · {$p->passenger_firstname} {$p->passenger_lastname} ({$p->passenger_email})\n";
}
echo "\n";

// Dispatch the email job
echo "Dispatching SendTicketEmail job for testing...\n";

try {
    \App\Jobs\SendTicketEmail::dispatch($staffBooking->booking_ref_no);
    echo "✓ Job dispatched successfully!\n\n";
} catch (\Exception $e) {
    echo "❌ Failed to dispatch: {$e->getMessage()}\n\n";
    exit(1);
}

// Check queue
$jobCount = DB::table('jobs')->count();
echo "Current queue size: {$jobCount} job(s)\n";
echo "  → Queue worker will process this and send email with PDFs\n\n";

// Check logs
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║                    NEXT STEPS                                  ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

echo "1️⃣  Make sure queue worker is running:\n";
echo "   php artisan queue:work\n\n";

echo "2️⃣  Monitor the email being sent:\n";
echo "   tail -f storage/logs/laravel.log | grep -E '(SendTicketEmail|PassengerTicketConfirmed|PDF)'\n\n";

echo "3️⃣  Check received email:\n";
echo "   Look for email from: lapulapushippinglinescorp@gmail.com\n";
echo "   To: " . $passengers->first()->passenger_email . "\n";
echo "   Subject: Your Passenger Ticket Confirmed - Booking #" . $staffBooking->booking_ref_no . "\n";
echo "   Attachments: Should have PDF(s) for each passenger\n\n";

echo "4️⃣  Verify PDFs:\n";
foreach ($passengers as $p) {
    $filename = 'ticket_' . str_replace(' ', '_', $p->passenger_firstname) . '_' . str_replace(' ', '_', $p->passenger_lastname) . '.pdf';
    echo "   ✓ Should have: {$filename}\n";
}

echo "\n";
