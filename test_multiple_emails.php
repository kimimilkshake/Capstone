<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Mail;
use App\Mail\TicketMailable;

echo "🧪 Multiple Email Test\n";
echo "====================\n\n";

// Test different email addresses
$testEmails = [
    'shemcardoza7@gmail.com' => 'Your Gmail (known working)',
    'heisenbergfritz@gmail.com' => 'Another Gmail account',
    'test@example.com' => 'Invalid test domain'
];

foreach ($testEmails as $email => $description) {
    echo "📧 Testing: {$email} ({$description})\n";

    try {
        // Send a simple test email
        Mail::raw("Test email from Lapulapu Shipping Lines\n\nThis is a test to verify email delivery to your address.", function ($message) use ($email) {
            $message->to($email)
                ->subject('Test Email - Lapulapu Shipping Lines')
                ->from(config('mail.from.address'), config('mail.from.name'));
        });

        echo "✅ Email sent successfully to {$email}\n";

    } catch (Exception $e) {
        echo "❌ Email failed to {$email}: " . $e->getMessage() . "\n";

        // Check if it's a specific type of error
        if (strpos($e->getMessage(), 'invalid') !== false || strpos($e->getMessage(), 'rejected') !== false) {
            echo "   💡 This might be due to invalid email domain or recipient rejection\n";
        } elseif (strpos($e->getMessage(), 'rate limit') !== false || strpos($e->getMessage(), 'quota') !== false) {
            echo "   💡 This might be due to Gmail sending limits\n";
        } elseif (strpos($e->getMessage(), 'authentication') !== false) {
            echo "   💡 This might be an SMTP authentication issue\n";
        }
    }

    echo "\n";

    // Small delay between emails to avoid rate limiting
    sleep(2);
}

echo "🔍 Summary:\n";
echo "- Gmail to Gmail should work (both accounts)\n";
echo "- Invalid domains (example.com) will likely fail\n";
echo "- Real external domains should work but may take longer\n";
echo "- Check recipient spam folders for external domains\n";

?>