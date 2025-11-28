<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Mail;
use App\Mail\TicketMailable;

echo "🧪 Direct Email Test (No Queue)\n";
echo "==============================\n\n";

try {
    // Get booking data using the correct column name
    $booking = DB::table('booking')->where('booking_ref_no', 15)->first();
    if (!$booking) {
        echo "❌ Booking #15 not found\n";
        exit(1);
    }

    // Get passenger data for email address
    $passenger = DB::table('passenger_ticket')
        ->join('passenger', 'passenger_ticket.passenger_id', '=', 'passenger.passenger_id')
        ->where('passenger_ticket.booking_ref_no', 15)
        ->select('passenger.*')
        ->first();

    if (!$passenger) {
        echo "❌ Passenger for booking #15 not found\n";
        exit(1);
    }

    echo "📋 Found booking #15\n";
    echo "👤 Customer: {$passenger->passenger_firstname} {$passenger->passenger_lastname}\n";
    echo "📧 Email: {$passenger->passenger_email}\n\n";

    // Test mail configuration
    echo "📨 Testing direct email send...\n";

    // Send email directly (not queued)
    Mail::to($passenger->passenger_email)
        ->send(new TicketMailable(15)); // Using booking_ref_no

    echo "✅ Email sent successfully to Gmail!\n";
    echo "📱 Check your inbox at: {$passenger->passenger_email}\n";
    echo "📁 Also check your spam folder\n";

} catch (Exception $e) {
    echo "❌ Email failed: " . $e->getMessage() . "\n";
    echo "🔍 Full error details:\n";
    echo $e->getFile() . ":" . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
}

?>