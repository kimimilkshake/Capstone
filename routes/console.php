<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\Booking;
use App\Models\Payment;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Clean up expired pending reservations every 5 minutes
Schedule::call(function () {
    $fiveMinutesAgo = now()->subMinutes(5);

    // Get all expired pending bookings
    $expiredBookings = Booking::where('booking_status', 'Pending')
        ->where('created_at', '<=', $fiveMinutesAgo)
        ->get();

    foreach ($expiredBookings as $booking) {
        // Cancel the booking
        $booking->booking_status = 'Canceled';
        $booking->save();

        // Cancel the associated payment
        Payment::where('booking_ref_no', $booking->booking_ref_no)
            ->update(['payment_status' => 'Canceled']);
    }
})->everyFiveMinutes();

// Prune completed queue batches daily
Schedule::command('queue:prune-batches')->dailyAt('02:00');

// Flush failed jobs daily
Schedule::command('queue:flush')->dailyAt('02:05');
