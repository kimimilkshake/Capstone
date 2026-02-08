<?php

/**
 * Quick test to verify cargo notifications are working
 * Run: php test_cargo_notification.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Notification;

echo "Testing Cargo Notification System\n";
echo "==================================\n\n";

try {
    // Create a test notification (simulating a new cargo booking)
    $notification = Notification::create([
        'cargo_receipt_id' => null,
        'payment_id' => null,
        'booking_ref_no' => 1, // Use an existing booking ID from your database
        'notification_message' => 'Test: New cargo booking #1 from Juan Cruz is pending review',
        'notification_type' => 'cargo booking approval',
        'notification_status' => 'approved',
        'notification_created' => now(),
    ]);

    echo "✓ Test notification created successfully!\n";
    echo "  ID: {$notification->notification_id}\n";
    echo "  Message: {$notification->notification_message}\n\n";

    // Check unread count
    $unreadCount = Notification::whereNotIn('notification_status', ['read', 'archived'])->count();
    echo "✓ Current unread notifications: {$unreadCount}\n\n";

    echo "Now check the bell icon in your authHeader! 🔔\n";
    echo "\nTo view in browser:\n";
    echo "1. Visit any page with authHeader (staff or admin dashboard)\n";
    echo "2. Look for the bell icon in the top-right corner\n";
    echo "3. You should see a red badge with the notification count\n";
    echo "4. Click the bell to see your notifications\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}
