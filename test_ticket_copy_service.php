<?php
// Test script for TicketCopyService
// Usage: php artisan tinker < test_ticket_copy_service.php

use App\Services\TicketCopyService;

// Test data - change these to match your actual data
$testEmail = 'john.doe@example.com'; // Change to actual passenger email
$testDate = '2026-03-16'; // Change to actual voyage date
$routeFrom = 'Cebu'; // Change to actual route
$routeTo = 'Bohol'; // Change to actual route

echo "========== TicketCopyService Test ==========\n";
echo "Email: $testEmail\n";
echo "Date: $testDate\n";
echo "Route: $routeFrom -> $routeTo\n";
echo "==========================================\n\n";

$service = new TicketCopyService();
$result = $service->requestTicketCopy($testEmail, $testDate, $routeFrom, $routeTo);

echo "Result:\n";
echo json_encode($result, JSON_PRETTY_PRINT) . "\n\n";

if ($result['success']) {
    echo "✓ TEST PASSED\n";
    if (isset($result['pending_selection']) && $result['pending_selection']) {
        echo "  Multiple passengers found - selection list returned\n";
        echo "  Passengers: " . count($result['passengers']) . "\n";
    } else if ($result['direct_send'] ?? false) {
        echo "  Direct send - single passenger found and email dispatched\n";
    }
} else {
    echo "✗ TEST FAILED\n";
    echo "  Error: " . $result['message'] . "\n";
}
