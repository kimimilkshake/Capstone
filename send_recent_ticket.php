<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Mail;
use App\Mail\TicketMailable;

echo "🎫 Sending Ticket for Your Recent Booking\n";
echo "=========================================\n\n";

// Get your latest booking
$booking = DB::table('booking')
    ->where('booking_status', 'Confirmed')
    ->orderBy('booking_ref_no', 'desc')
    ->first();

echo "📋 Processing Booking #{$booking->booking_ref_no}\n";

try {
    // Send the ticket email directly (not queued)
    Mail::to('shemcardoza7@gmail.com')
        ->send(new TicketMailable($booking->booking_ref_no));

    echo "✅ Ferry ticket sent successfully!\n";
    echo "📧 Email sent to: shemcardoza7@gmail.com\n";
    echo "🎫 Booking: #{$booking->booking_ref_no}\n";
    echo "📅 Date: {$booking->booking_date}\n\n";

    echo "📱 Check your Gmail inbox now!\n";
    echo "📁 If not in inbox, check spam folder\n";
    echo "⏰ Email should arrive within 1-2 minutes\n\n";

    echo "💡 This email contains:\n";
    echo "   - Your booking confirmation\n";
    echo "   - Ferry route and schedule\n";
    echo "   - Passenger details\n";
    echo "   - Seat/accommodation info\n";
    echo "   - QR code for boarding\n";
    echo "   - Professional ticket design\n";

} catch (Exception $e) {
    echo "❌ Failed to send ticket: " . $e->getMessage() . "\n";
}

echo "\n🔧 For automatic emails in future bookings:\n";
echo "1. The payment system should automatically send tickets\n";
echo "2. Make sure to run: php artisan queue:work\n";
echo "3. Keep the queue worker running in background\n";

?>