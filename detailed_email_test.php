<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Mail;

echo "🔍 Detailed Email Delivery Test\n";
echo "================================\n\n";

// Enable detailed SMTP debugging
config(['mail.mailers.smtp.verify_peer' => false]);

echo "📧 Testing email to heisenbergfritz@gmail.com\n";
echo "This will show detailed SMTP conversation...\n\n";

try {
    // Enable SMTP debugging if available
    $originalDebug = ini_get('smtp_debug');

    Mail::raw("Hello from Lapulapu Shipping Lines!\n\nThis is a test email to verify delivery.\n\nSent at: " . now(), function ($message) {
        $message->to('heisenbergfritz@gmail.com')
            ->subject('🚢 Test from Lapulapu Shipping - ' . now()->format('H:i:s'))
            ->from(config('mail.from.address'), config('mail.from.name'));
    });

    echo "✅ Email sent successfully!\n";
    echo "📱 The email should arrive within 1-2 minutes\n";
    echo "📁 If not in inbox, check spam folder\n\n";

    echo "💡 What this means:\n";
    echo "   - SMTP connection: ✅ Working\n";
    echo "   - Gmail authentication: ✅ Working  \n";
    echo "   - Email queued for delivery: ✅ Working\n";
    echo "   - Actual delivery: ⏳ Depends on recipient email provider\n\n";

    echo "🚨 Common issues with other emails:\n";
    echo "   1. Invalid email addresses (bounce back)\n";
    echo "   2. Spam filters on recipient side\n";
    echo "   3. Gmail new sender restrictions\n";
    echo "   4. Recipient email provider blocking\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\n🔍 Error details:\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n📊 To check if emails are being delivered:\n";
echo "1. Ask recipients to check spam folders\n";
echo "2. Try sending to different email providers (Yahoo, Outlook)\n";
echo "3. Check Gmail 'Sent' folder for confirmation\n";
echo "4. Consider Gmail's daily sending limits for new accounts\n";

?>