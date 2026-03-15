<?php
require 'bootstrap/app.php';
$app = require 'bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$bookings = \App\Models\Booking::where('booking_status', 'Confirmed')
    ->whereNotNull('voyage_id')
    ->limit(5)
    ->get();

foreach ($bookings as $booking) {
    $passenger = \DB::table('passenger')
        ->join('passenger_ticket', 'passenger.passenger_id', '=', 'passenger_ticket.passenger_id')
        ->where('passenger_ticket.booking_ref_no', $booking->booking_ref_no)
        ->select('passenger.passenger_email')
        ->first();
    
    if ($passenger) {
        echo "Booking {$booking->booking_ref_no}: email={$passenger->passenger_email}\n";
        \App\Jobs\SendTicketEmail::dispatch($booking->booking_ref_no);
        echo "  → Job dispatched\n";
        break;
    }
}
