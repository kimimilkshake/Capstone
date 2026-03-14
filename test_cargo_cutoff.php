<?php

echo 'Current Date/Time: ' . date('Y-m-d H:i:s') . PHP_EOL;

// Test the booking cutoff logic
function testCargoCutoff($depDate, $depTimeStr)
{
    $now = new DateTime();
    $depDateTime = new DateTime($depDate . ' ' . $depTimeStr);
    $depHour = (int) $depDateTime->format('H');

    $depDateStart = new DateTime($depDate . ' 00:00:00');

    // 12:00 AM to 6:00 PM (00:00 to 18:00) - must book day before
    if ($depHour <= 18) {
        if ($now >= $depDateStart) {
            return ['valid' => false, 'reason' => 'BLOCKED: Must book day before (12AM-6PM departure)'];
        }
        return ['valid' => true, 'reason' => 'OK: Booking allowed for 12AM-6PM departure'];
    }

    // 7:00 PM to 11:59 PM (19:00+) - same day booking with 5:00 PM cutoff
    $depDateEnd = new DateTime($depDate . ' 23:59:59');
    if ($now > $depDateEnd) {
        return ['valid' => false, 'reason' => 'BLOCKED: Departure date has passed'];
    }

    $isSameDay = $now->format('Y-m-d') === $depDate;
    if ($isSameDay) {
        $cutoff = new DateTime($depDate . ' 17:00:00'); // 5:00 PM
        if ($now >= $cutoff) {
            return ['valid' => false, 'reason' => 'BLOCKED: Cutoff time (5:00 PM) has passed for 7PM-11PM departure'];
        }
    }

    return ['valid' => true, 'reason' => 'OK: Booking allowed for 7PM-11PM departure (before 5PM cutoff)'];
}

// Test scenarios for today (March 15, 2026)
echo PHP_EOL . '=== Test Cases for ' . date('Y-m-d') . ' ===' . PHP_EOL;
echo 'Current Time: ' . date('H:i:s') . PHP_EOL;

// Test 1: Today, 2:00 PM departure (in 12AM-6PM window) - should FAIL (must book day before)
echo PHP_EOL . 'Test 1 - Today at 2:00 PM departure (12AM-6PM):' . PHP_EOL;
$result = testCargoCutoff(date('Y-m-d'), '14:00:00');
echo '  Result: ' . ($result['valid'] ? '✓ PASS' : '✗ FAIL') . PHP_EOL;
echo '  Reason: ' . $result['reason'] . PHP_EOL;

// Test 2: Today, 8:00 PM departure (in 7PM-11PM window) before 5PM cutoff
$currentTime = new DateTime();
$cutoffTime = new DateTime(date('Y-m-d') . ' 17:00:00');
if ($currentTime < $cutoffTime) {
    echo PHP_EOL . 'Test 2 - Today at 8:00 PM departure (7PM-11PM), before 5PM cutoff:' . PHP_EOL;
    $result = testCargoCutoff(date('Y-m-d'), '20:00:00');
    echo '  Result: ' . ($result['valid'] ? '✓ PASS' : '✗ FAIL') . PHP_EOL;
    echo '  Reason: ' . $result['reason'] . PHP_EOL;
} else {
    echo PHP_EOL . 'Test 2 - Skipped (current time is after 5PM cutoff)' . PHP_EOL;
}

// Test 3: Tomorrow, 2:00 PM departure (12AM-6PM) - should PASS (day before)
echo PHP_EOL . 'Test 3 - Tomorrow at 2:00 PM departure (12AM-6PM):' . PHP_EOL;
$tomorrow = date('Y-m-d', strtotime('+1 day'));
$result = testCargoCutoff($tomorrow, '14:00:00');
echo '  Result: ' . ($result['valid'] ? '✓ PASS' : '✗ FAIL') . PHP_EOL;
echo '  Reason: ' . $result['reason'] . PHP_EOL;

// Test 4: Tomorrow, 8:00 PM departure (7PM-11PM) - should PASS
echo PHP_EOL . 'Test 4 - Tomorrow at 8:00 PM departure (7PM-11PM):' . PHP_EOL;
$result = testCargoCutoff($tomorrow, '20:00:00');
echo '  Result: ' . ($result['valid'] ? '✓ PASS' : '✗ FAIL') . PHP_EOL;
echo '  Reason: ' . $result['reason'] . PHP_EOL;

// Test 5: 2 days from now, 2:00 PM departure (12AM-6PM) - should PASS
echo PHP_EOL . 'Test 5 - In 2 days at 2:00 PM departure (12AM-6PM):' . PHP_EOL;
$inTwoDays = date('Y-m-d', strtotime('+2 days'));
$result = testCargoCutoff($inTwoDays, '14:00:00');
echo '  Result: ' . ($result['valid'] ? '✓ PASS' : '✗ FAIL') . PHP_EOL;
echo '  Reason: ' . $result['reason'] . PHP_EOL;

echo PHP_EOL . '=== Summary ===' . PHP_EOL;
echo 'Rules:' . PHP_EOL;
echo '  1. 12:00 AM - 6:00 PM departures: Must book the day BEFORE' . PHP_EOL;
echo '  2. 7:00 PM - 11:59 PM departures: Can book same day until 5:00 PM' . PHP_EOL;
?>