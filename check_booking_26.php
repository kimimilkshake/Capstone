<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Jobs\SendTicketEmail;

echo "📊 YOUR 3-PASSENGER BOOKING #26\n";
echo "================================\n\n";

$booking = DB::table('booking')->where('booking_ref_no', 26)->first();

if (!$booking) {
    echo "❌ Booking not found\n";
    exit;
}

echo "Booking Status: " . $booking->booking_status . "\n";
echo "Voyage ID: " . ($booking->voyage_id ? $booking->voyage_id : "NULL ⚠️ (OLD BOOKING - BEFORE FIX)") . "\n\n";

// Get voyage details - try to find it anyway
$voyage = null;
if ($booking->voyage_id) {
    $voyage = DB::table('voyage')->where('voyage_id', $booking->voyage_id)->first();
}

// Get all passengers
$passengers = DB::table('passenger_ticket')
    ->join('passenger', 'passenger_ticket.passenger_id', '=', 'passenger.passenger_id')
    ->where('passenger_ticket.booking_ref_no', 26)
    ->orderBy('passenger_ticket.created_at')
    ->get([
        'passenger.passenger_firstname',
        'passenger.passenger_lastname',
        'passenger.passenger_type',
        'passenger.passenger_email',
        'passenger_ticket.pt_cot_no',
        'passenger_ticket.pt_ticket_price',
        'passenger_ticket.voyage_id'
    ]);

echo "Your 3 Passengers:\n";
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
$payment = DB::table('payment')->where('booking_ref_no', 26)->first();
if ($payment) {
    echo "Payment Info:\n";
    echo "  Total: ₱" . number_format($payment->total_amount, 2) . "\n";
    echo "  Status: " . $payment->payment_status . "\n";
    echo "  Mode: " . $payment->mode_of_payment . "\n\n";
}

echo "========================================\n";
echo "📧 YOUR EMAIL:\n";
echo "========================================\n";
echo "To: shemcardoza7@gmail.com\n";
echo "PDF Attachments:\n";
foreach ($passengers as $p) {
    echo "  📄 ticket_" . $p->passenger_firstname . "_" . $p->passenger_lastname . ".pdf\n";
}

echo "\n⚠️  NOTE: This booking was created BEFORE the voyage_id fix.\n";
echo "   The accommodations may not show in the PDFs.\n";
echo "   But all passenger info should be there!\n\n";

echo "========================================\n";
echo "🔍 EMAIL DELIVERY STATUS:\n";
echo "========================================\n";

// Check logs
$logFile = __DIR__ . '/storage/logs/laravel.log';
$logs = shell_exec("grep -i 'booking 26\\|booking_ref_no.*26' \"$logFile\" | tail -5");

if ($logs && strpos($logs, 'SendTicketEmail') !== false) {
    echo "✅ EMAIL WAS ALREADY SENT!\n";
    echo $logs;
} else {
    echo "❓ No recent email logs for booking #26 found.\n";
    echo "\nWould you like me to resend the email?\n";
    echo "If yes, I can dispatch it again:\n\n";
    echo "   SendTicketEmail::dispatch(26);\n";
}

?>