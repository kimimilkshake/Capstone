<?php

echo "╔════════════════════════════════════════════════════════════════╗" . PHP_EOL;
echo "║      CARGO BOOKING CUTOFF VALIDATION TEST - BOTH SIDES         ║" . PHP_EOL;
echo "╚════════════════════════════════════════════════════════════════╝" . PHP_EOL;

echo PHP_EOL . "Current Date/Time: " . date('Y-m-d H:i:s') . PHP_EOL;

// PHP Implementation (Staff Side: cargobooking.blade.php)
function validateBookingCutoffByDeparture_PHP($departureDate, $departureTime)
{
    if (!$departureDate || !$departureTime) {
        return ['valid' => false, 'message' => 'Invalid voyage departure date/time.'];
    }

    // Parse time from HH:MM:SS format
    $timeParts = explode(':', $departureTime);
    $depHour = (int) $timeParts[0];

    $now = new DateTime();
    $depDateTime = new DateTime($departureDate . ' ' . $departureTime);
    $depDateStart = new DateTime($departureDate . ' 00:00:00');

    if ($depHour <= 18) {
        if ($now >= $depDateStart) {
            return [
                'valid' => false,
                'message' => 'For departures from 12:00 AM to 6:00 PM, booking must be completed the day before.'
            ];
        }
        return ['valid' => true];
    }

    $depDateEnd = new DateTime($departureDate . ' 23:59:59');
    if ($now > $depDateEnd) {
        return ['valid' => false, 'message' => 'This voyage booking window has already closed.'];
    }

    $isSameDay = $now->format('Y-m-d') === $depDateTime->format('Y-m-d');

    if ($isSameDay) {
        $cutoff = new DateTime($departureDate . ' 17:00:00');
        if ($now >= $cutoff) {
            return [
                'valid' => false,
                'message' => 'For departures from 7:00 PM to 11:59 PM, same-day booking cutoff is 5:00 PM.'
            ];
        }
    }

    return ['valid' => true];
}

// JavaScript Equivalent (Passenger Side: bookingtype.js)
function validateCargoCutoff_JS($departureDate, $departureTimeStr)
{
    if (!$departureDate || !$departureTimeStr) {
        return ['valid' => false, 'message' => 'Invalid voyage departure information.'];
    }

    $timeParts = explode(':', $departureTimeStr);
    $depHour = (int) $timeParts[0];

    $now = new DateTime();
    $depDate = new DateTime($departureDate);
    $depDateStart = new DateTime($departureDate . ' 00:00:00');

    // 12:00 AM to 6:00 PM (00:00 to 18:00) - must book day before
    if ($depHour <= 18) {
        if ($now >= $depDateStart) {
            return [
                'valid' => false,
                'message' => 'Cargo booking is not allowed anymore at this time'
            ];
        }
        return ['valid' => true];
    }

    // 7:00 PM to 11:59 PM (19:00+) - same day booking with 5:00 PM cutoff
    $depDateEnd = new DateTime($departureDate . ' 23:59:59');

    if ($now > $depDateEnd) {
        return [
            'valid' => false,
            'message' => 'Cargo booking is not allowed anymore at this time'
        ];
    }

    $isSameDay = $now->format('Y-m-d') === $depDate->format('Y-m-d');

    if ($isSameDay) {
        $cutoff = new DateTime($departureDate . ' 17:00:00'); // 5:00 PM
        if ($now >= $cutoff) {
            return [
                'valid' => false,
                'message' => 'Cargo booking is not allowed anymore at this time'
            ];
        }
    }

    return ['valid' => true];
}

// Test cases
$testCases = [
    [
        'name' => 'Today 2:00 PM (12AM-6PM window)',
        'date' => date('Y-m-d'),
        'time' => '14:00:00',
        'expected' => false,
        'reason' => 'Must book day before'
    ],
    [
        'name' => 'Tomorrow 2:00 PM (12AM-6PM window)',
        'date' => date('Y-m-d', strtotime('+1 day')),
        'time' => '14:00:00',
        'expected' => true,
        'reason' => 'Day before departure'
    ],
    [
        'name' => 'Tomorrow 8:00 PM (7PM-11PM window)',
        'date' => date('Y-m-d', strtotime('+1 day')),
        'time' => '20:00:00',
        'expected' => true,
        'reason' => 'Before 5PM cutoff'
    ],
    [
        'name' => '3 days from now 5:00 AM (12AM-6PM window)',
        'date' => date('Y-m-d', strtotime('+3 days')),
        'time' => '05:00:00',
        'expected' => true,
        'reason' => 'Well before departure'
    ],
    [
        'name' => '2 days from now 9:00 PM (7PM-11PM window)',
        'date' => date('Y-m-d', strtotime('+2 days')),
        'time' => '21:00:00',
        'expected' => true,
        'reason' => 'Before 5PM cutoff'
    ],
];

echo PHP_EOL . "╔════════════════════════════════════════════════════════════════╗" . PHP_EOL;
echo "║                      VALIDATION TESTS                             ║" . PHP_EOL;
echo "╚════════════════════════════════════════════════════════════════╝" . PHP_EOL;

$allMatch = true;
$passCount = 0;

foreach ($testCases as $test) {
    echo PHP_EOL . "Test: " . $test['name'] . " ({$test['date']})";
    echo PHP_EOL . "  Departure: {$test['time']} | Expected: " . ($test['expected'] ? 'ALLOW' : 'BLOCK');
    echo PHP_EOL . "  Reason: " . $test['reason'] . PHP_EOL;

    // Test PHP implementation (Staff)
    $phpResult = validateBookingCutoffByDeparture_PHP($test['date'], $test['time']);
    $phpValid = $phpResult['valid'];

    // Test JS implementation (Passenger)
    $jsResult = validateCargoCutoff_JS($test['date'], $test['time']);
    $jsValid = $jsResult['valid'];

    // Check if both match expected behavior
    $phpMatch = ($phpValid === $test['expected']);
    $jsMatch = ($jsValid === $test['expected']);
    $bothMatch = ($phpValid === $jsValid);

    echo "  ├─ PHP (Staff):       " . ($phpValid ? '✓ ALLOW' : '✗ BLOCK') . " " . ($phpMatch ? "✓" : "✗ MISMATCH") . PHP_EOL;
    echo "  ├─ JS (Passenger):    " . ($jsValid ? '✓ ALLOW' : '✗ BLOCK') . " " . ($jsMatch ? "✓" : "✗ MISMATCH") . PHP_EOL;
    echo "  └─ Match:             " . ($bothMatch ? "✓ YES" : "✗ NO") . PHP_EOL;

    if ($bothMatch && $phpMatch && $jsMatch) {
        $passCount++;
    } else {
        $allMatch = false;
    }
}

echo PHP_EOL . "╔════════════════════════════════════════════════════════════════╗" . PHP_EOL;
echo "║                        FINAL RESULTS                              ║" . PHP_EOL;
echo "╚════════════════════════════════════════════════════════════════╝" . PHP_EOL;

$totalTests = count($testCases);
echo PHP_EOL . "Tests Passed: $passCount / $totalTests" . PHP_EOL;
echo "Status: " . ($allMatch ? "✓ ALL TESTS PASSED - Both implementations match!" : "✗ TESTS FAILED") . PHP_EOL;

echo PHP_EOL . "Implementation Locations:" . PHP_EOL;
echo "  • Staff Side:     resources/views/authorized/staff/cargobooking.blade.php" . PHP_EOL;
echo "                    Function: validateBookingCutoffByDeparture()" . PHP_EOL;
echo "  • Passenger Side: public/js/bookingtype.js" . PHP_EOL;
echo "                    Function: validateCargoCutoff()" . PHP_EOL;

echo PHP_EOL . "Booking Rules:" . PHP_EOL;
echo "  1. Departures 12:00 AM - 6:00 PM   → Must book day BEFORE" . PHP_EOL;
echo "  2. Departures 7:00 PM - 11:59 PM   → Can book same day, cutoff at 5:00 PM" . PHP_EOL;

echo PHP_EOL;
?>