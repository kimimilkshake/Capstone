<?php
/**
 * Test the request ticket copy feature with passenger_id
 * This simulates the second form submission after passenger selection
 */

use App\Services\TicketCopyService;

$email = 'passenger@example.com'; // Update with actual test email
$departureDate = '2026-03-20'; // Update with actual date
$routeFrom = 'Manila'; // Update with actual origin
$routeTo = 'Iloilo'; // Update with actual destination
$passengerId = 1; // Test with actual passenger_id

echo "Testing TicketCopyService with passenger_id...\n\n";

$service = new TicketCopyService();
$result = $service->requestTicketCopy(
    $email,
    $departureDate,
    $routeFrom,
    $routeTo,
    $passengerId
);

echo "Result:\n";
echo json_encode($result, JSON_PRETTY_PRINT) . "\n\n";

if ($result['success']) {
    echo "✓ Test passed\n";
} else {
    echo "✗ Test failed: " . $result['message'] . "\n";
}
