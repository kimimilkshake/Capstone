<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Mail;
use App\Mail\TicketMailable;

echo "🔍 Checking Your Recent Booking\n";
echo "===============================\n\n";

// Get the latest booking
$latestBooking = DB::table('booking')->orderBy('booking_ref_no', 'desc')->first();

echo "📋 Latest Booking: #{$latestBooking->booking_ref_no}\n";
echo "📅 Booking Date: {$latestBooking->booking_date}\n";
echo "✅ Status: {$latestBooking->booking_status}\n\n";

// Get passenger details
$passenger = DB::table('passenger_ticket')
    ->join('passenger', 'passenger_ticket.passenger_id', '=', 'passenger.passenger_id')
    ->where('passenger_ticket.booking_ref_no', $latestBooking->booking_ref_no)
    ->select('passenger.*')
    ->first();

if ($passenger) {
    echo "👤 Passenger: {$passenger->passenger_firstname} {$passenger->passenger_lastname}\n";
    echo "📧 Email: {$passenger->passenger_email}\n\n";

    // Check if there are any jobs in the queue
    $queuedJobs = DB::table('jobs')->count();
    $failedJobs = DB::table('failed_jobs')->count();

    echo "📊 Queue Status:\n";
    echo "   Active Jobs: {$queuedJobs}\n";
    echo "   Failed Jobs: {$failedJobs}\n\n";

    // Test sending email for this booking
    echo "🧪 Testing Email for Booking #{$latestBooking->booking_ref_no}...\n";

    try {
        Mail::to($passenger->passenger_email)
            ->send(new TicketMailable($latestBooking->booking_ref_no));

        echo "✅ Email sent successfully to {$passenger->passenger_email}!\n";
        echo "📱 Check your inbox in 1-2 minutes\n";
        echo "📁 Also check spam folder\n";

    } catch (Exception $e) {
        echo "❌ Email failed: " . $e->getMessage() . "\n";
        echo "\n🔍 Error details:\n";
        echo $e->getFile() . ":" . $e->getLine() . "\n";
    }

} else {
    echo "❌ No passenger found for this booking\n";
}

// Check if email was supposed to be sent automatically
echo "\n🤔 Why didn't you receive the email automatically?\n";
echo "   1. Check if queue worker is running: php artisan queue:work\n";
echo "   2. Check if payment triggered email job\n";
echo "   3. Email might be in spam folder\n";
echo "   4. Gmail delivery delay (can take 2-5 minutes)\n";

?>