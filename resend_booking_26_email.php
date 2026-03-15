<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Jobs\SendTicketEmail;

echo "📧 RESENDING EMAIL FOR BOOKING #26\n";
echo "==================================\n\n";

echo "📨 Passenger 1: Shem Rupert Cardoza\n";
echo "   Email: shemcardoza7@gmail.com ✓ (PRIMARY RECIPIENT)\n\n";

echo "📄 Attached PDFs (3):\n";
echo "   1. ticket_Shem Rupert_Cardoza.pdf (Student)\n";
echo "   2. ticket_Kirzteen Marie_Uy.pdf (Regular)\n";
echo "   3. ticket_Sophia Ann_Cohon.pdf (Regular)\n\n";

echo "Passengers in Email:\n";
echo "   • Shem Rupert Cardoza (Student) - COT #141 - ₱512.00\n";
echo "   • Kirzteen Marie Uy (Regular) - COT #144 - ₱640.00\n";
echo "   • Sophia Ann Cohon (Regular) - COT #143 - ₱640.00\n";
echo "   TOTAL: ₱1,792.00\n\n";

echo "========================================\n";
echo "🚀 Dispatching Email NOW...\n";
echo "========================================\n";

try {
    SendTicketEmail::dispatch(26);
    echo "✅ Email queued successfully!\n\n";
    echo "Email should arrive in your inbox shortly at:\n";
    echo "   📧 shemcardoza7@gmail.com\n\n";
    echo "Subject: Your Passenger Ticket Confirmed - Booking #26\n\n";
    echo "The email will contain:\n";
    echo "   ✓ All 3 passenger tickets as PDF attachments\n";
    echo "   ✓ Booking reference and voyage details\n";
    echo "   ✓ Total amount paid (₱1,792.00)\n";
    echo "   ✓ Important reminders and contact info\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

?>