<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Sender;
use App\Models\Consignee;
use App\Models\CargoItem;
use App\Models\Voyage;

class StaffCargoController extends Controller
{
    // Show Cargo Booking Form
    public function createBooking()
    {
        $voyages = Voyage::all();
        $cargoItems = CargoItem::all();

        return view('authorized.staff.cargobooking', compact('voyages', 'cargoItems'));
    }

    // Store Cargo Booking
    public function storeBooking(Request $request)
    {
        $request->validate([
            'sender_name' => 'required|string',
            'sender_contactno' => 'required|string',
            'sender_email' => 'nullable|email',
            'consignee_name' => 'required|string',
            'consignee_contactno' => 'required|string',
            'voyage_id' => 'required|exists:voyage,voyage_id',
            'cargo_item_id' => 'required|exists:cargo_item,cargo_item_id',
            'cargo_item_qty' => 'required|integer|min:1',
        ]);

        // Create sender & consignee
        $sender = Sender::create([
            'sender_name' => $request->sender_name,
            'sender_contactno' => $request->sender_contactno,
            'sender_email' => $request->sender_email,
        ]);

        $consignee = Consignee::create([
            'consignee_name' => $request->consignee_name,
            'consignee_contactno' => $request->consignee_contactno,
        ]);

        // Create booking (auto-increment BIGINT booking_ref_no)
        Booking::create([
            'booking_type' => 'cargo',
            'booking_status' => 'Pending',
            'voyage_id' => $request->voyage_id,
            'sender_id' => $sender->sender_id,
            'consignee_id' => $consignee->consignee_id,
            'cargo_item_id' => $request->cargo_item_id,
            'cargo_item_qty' => $request->cargo_item_qty,
        ]);

        return redirect()->route('staff.cargo_booking.create')
                         ->with('success', 'Cargo booking submitted successfully!');
    }

// Review Cargo Bookings
public function reviewBookings(Request $request)
{
    $cargoBookings = Booking::with(['sender', 'consignee', 'voyage', 'cargoItem'])
        ->where('booking_type', 'cargo')
        ->when($request->search, function($query, $search) {
            $query->where('booking_ref_no', 'like', "%{$search}%");
        })
        ->when($request->date, function($query, $date) {
            $query->whereDate('created_at', $date);
        })
        ->orderBy('booking_ref_no', 'desc')
        ->paginate(10);

    // ✅ Return the view and pass the bookings
    return view('authorized.staff.reviewcargobookings', compact('cargoBookings'));
}


    // Approve Booking
    public function approveBooking($bookingRefNo)
    {
        $booking = Booking::findOrFail($bookingRefNo);
        $booking->booking_status = 'Confirmed';
        $booking->save();

        return back()->with('success', 'Booking approved successfully!');
    }

    // Reject Booking
    public function rejectBooking($bookingRefNo)
    {
        $booking = Booking::findOrFail($bookingRefNo);
        $booking->booking_status = 'Cancelled';
        $booking->save();

        return back()->with('success', 'Booking cancelled successfully!');
    }
}
