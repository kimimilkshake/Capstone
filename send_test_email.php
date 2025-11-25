<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Jobs\SendTicketEmail;

echo "🎫 Ticket Email Sender\n";
echo "====================\n\n";

// Check mail configuration
$mailer = config('mail.default');
$host = config('mail.mailers.smtp.host');
$username = config('mail.mailers.smtp.username');

echo "Current mail settings:\n";
echo "Driver: $mailer\n";
echo "SMTP Host: $host\n";
echo "Username: " . ($username ?: 'NOT SET') . "\n\n";

if (!$username) {
    echo "⚠️  MAIL_USERNAME is not set in .env file!\n";
    echo "Please follow the Gmail setup instructions in GMAIL_SETUP.md\n";
    exit(1);
}

echo "📨 Sending ticket email for booking #15 to shemcardoza7@gmail.com\n";
echo "Make sure queue worker is running: php artisan queue:work\n\n";

try {
    // Dispatch email job for your booking
    SendTicketEmail::dispatch(15, 'shemcardoza7@gmail.com');

    echo "✅ Email job queued successfully!\n";
    echo "📋 Check queue with: php artisan queue:work\n";
    echo "📧 Check your Gmail inbox in a few minutes\n";
    echo "📱 Check spam folder if not in inbox\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n🎯 Your ticket will include:\n";
echo "   - Booking #15 confirmation\n";
echo "   - Route: Cebu → Baybay, Leyte\n";
echo "   - Passenger: Shem Cardoza\n";
echo "   - Cot assignment and pricing\n";
echo "   - QR code for check-in\n";
echo "   - Professional ferry ticket design\n";
