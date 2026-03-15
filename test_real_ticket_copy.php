<?php
// Test TicketCopyService with REAL data from your database
// Run: php artisan tinker < test_real_ticket_copy.php

use App\Services\TicketCopyService;
use Illuminate\Support\Facades\DB;

echo "\n========== REAL DATA TEST ==========\n\n";

// Find a booking with multiple passengers (e.g., booking 26, 27, 28, 29, 30 from debug output)
// Let's use booking 26 which has 3 passengers: Shem, Kirzteen, Sophia
// Route: Cebu → Baybay (route_port_id = 1)
// Date: 2026-03-18
// Voyage: 1

echo "Test 1: Multi-passenger booking (Booking 26)\n";
echo "  Route: Cebu → Baybay\n";
echo "  Date: 2026-03-18\n";
echo "  Expected passengers: Shem Rupert Cardoza, Kirzteen Marie Uy, Sophia Ann Cohon\n\n";

$service = new TicketCopyService();

// First request - look for all passengers (no passenger_id specified)
echo "Step 1: Search without specifying passenger\n";
$result = $service->requestTicketCopy(
    'shemcardoza7@gmail.com',
    '2026-03-18',
    'Cebu',
    'Baybay'
);

echo "Response: " . json_encode($result, JSON_PRETTY_PRINT) . "\n\n";

// If multiple passengers, try requesting for a specific one
if ($result['success'] && $result['pending_selection'] ?? false) {
    echo "Step 2: Request for specific passenger\n";
    if (isset($result['passengers']) && count($result['passengers']) > 0) {
        $firstPassenger = $result['passengers'][0];
        $passengerId = $firstPassenger['passenger_id'];

        echo "  Requesting for: {$firstPassenger['name']} (ID: {$passengerId})\n\n";

        $result2 = $service->requestTicketCopy(
            'shemcardoza7@gmail.com',
            '2026-03-18',
            'Cebu',
            'Baybay',
            $passengerId
        );

        echo "Response: " . json_encode($result2, JSON_PRETTY_PRINT) . "\n\n";
    }
}

// Test 2: Try with different passenger email
echo "\n\nTest 2: Different passenger email (Kirzteen)\n";
echo "  Route: Cebu → Baybay\n";
echo "  Date: 2026-03-18\n";
echo "  Email: kirzteenuy27@gmail.com\n\n";

$result3 = $service->requestTicketCopy(
    'kirzteenuy27@gmail.com',
    '2026-03-18',
    'Cebu',
    'Baybay'
);

echo "Response: " . json_encode($result3, JSON_PRETTY_PRINT) . "\n\n";

echo "========== END TEST ==========\n";
