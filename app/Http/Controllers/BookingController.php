<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function store(Request $request)
    {
        // Decode the JSON data sent from JS
        $data = $request->all();

        // You can inspect it for now
        // (In real use, you’d insert this into your database)
        \Log::info('Booking Data:', $data);

        // Example success response
        return response()->json([
            'success' => true,
            'payment_url' => url('/passenger/confirmbooking') // replace with your actual payment route
        ]);
    }
}
