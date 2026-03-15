<?php
use Illuminate\Support\Facades\DB;

echo "=== CHECKING AVAILABLE BOOKINGS FOR TESTING ===\n\n";

// Get recent bookings with passengers
$bookings = DB::table('booking as b')
    ->leftJoin('passenger_ticket as pt', 'b.booking_ref_no', '=', 'pt.booking_ref_no')
    ->leftJoin('passenger as p', 'pt.passenger_id', '=', 'p.passenger_id')
    ->leftJoin('payment as pay', 'b.booking_ref_no', '=', 'pay.booking_ref_no')
    ->where('b.booking_status', 'Confirmed')
    ->where('pay.payment_status', 'Completed')
    ->select(
        'b.booking_ref_no',
        'b.booking_status',
        'pay.payment_status',
        'p.passenger_id',
        'p.passenger_firstname',
        'p.passenger_lastname',
        'p.passenger_email',
        'pt.voyage_id',
        DB::raw('COUNT(pt.passenger_id) OVER (PARTITION BY b.booking_ref_no) as passenger_count')
    )
    ->orderByDesc('b.created_at')
    ->limit(20)
    ->get();

if ($bookings->isEmpty()) {
    echo "❌ No confirmed bookings with completed payments found\n";
} else {
    echo "✓ Found " . $bookings->count() . " booking records\n\n";

    $groupedByBooking = [];
    foreach ($bookings as $record) {
        $bookingRef = $record->booking_ref_no;
        if (!isset($groupedByBooking[$bookingRef])) {
            $groupedByBooking[$bookingRef] = [
                'status' => $record->booking_status,
                'payment' => $record->payment_status,
                'passengers' => [],
                'voyage_ids' => []
            ];
        }
        if ($record->passenger_id) {
            $groupedByBooking[$bookingRef]['passengers'][] = [
                'id' => $record->passenger_id,
                'name' => $record->passenger_firstname . ' ' . $record->passenger_lastname,
                'email' => $record->passenger_email
            ];
            $groupedByBooking[$bookingRef]['voyage_ids'][] = $record->voyage_id;
        }
    }

    foreach ($groupedByBooking as $bookingRef => $booking) {
        $pCount = count($booking['passengers']);
        echo "Booking #{$bookingRef} ($pCount passengers)\n";
        echo "  Status: {$booking['status']} | Payment: {$booking['payment']}\n";
        echo "  Voyage IDs: " . implode(', ', array_unique($booking['voyage_ids'])) . "\n";
        foreach ($booking['passengers'] as $passenger) {
            echo "    - {$passenger['name']} ({$passenger['id']}) - {$passenger['email']}\n";
        }
        echo "\n";
    }
}
