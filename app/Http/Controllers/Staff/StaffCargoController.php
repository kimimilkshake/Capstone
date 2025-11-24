<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\CargoBooking;
use App\Models\Voyage;
use App\Models\CargoItem;
use Carbon\Carbon;

class StaffCargoController extends Controller
{
    // Show create booking form
    public function createBooking()
    {
        $voyages = Voyage::with(['vessel', 'routePort'])->get();
        $cargoItems = CargoItem::all();
        return view('staff.cargo_booking.create', compact('voyages', 'cargoItems'));
    }

    // Store booking + cargo items
    public function storeBooking(Request $request)
    {
        $request->validate([
            'sender_firstname' => 'required|string|max:255',
            'sender_lastname'  => 'required|string|max:255',
            'sender_contact'   => 'required|string|max:20',
            'sender_email'     => 'nullable|email',
            'consignee_firstname' => 'required|string|max:255',
            'consignee_lastname'  => 'required|string|max:255',
            'consignee_contact'   => 'required|string|max:20',
            'cargo_description.*' => 'required|string',
            'cargo_quantity.*'    => 'required|integer|min:1',
            'cargo_weight.*'      => 'required|numeric|min:0',
            'cargo_length.*'      => 'required|numeric|min:0',
            'cargo_width.*'       => 'required|numeric|min:0',
            'cargo_height.*'      => 'required|numeric|min:0',
        ]);

        // 1️⃣ Create Booking
        $booking = Booking::create([
            'booking_date' => Carbon::now(),
            'booking_status' => 'Pending',
            'booking_type' => 'cargo',
            'sender_id' => null,
            'consignee_id' => null,
            'voyage_id' => $request->input('voyage_id'),
        ]);

        // 2️⃣ Create multiple cargo items
        foreach ($request->cargo_description as $index => $desc) {
            $picturePath = $request->file('cargo_picture')[$index] ?? null;
            if ($picturePath) {
                $picturePath = $picturePath->store('cargo_pictures', 'public');
            }

            CargoBooking::create([
                'booking_ref_no' => $booking->booking_ref_no,
                'cargo_item_id'  => $request->cargo_item_id[$index] ?? null,
                'quantity'       => $request->cargo_quantity[$index],
                'weight'         => $request->cargo_weight[$index],
                'length'         => $request->cargo_length[$index],
                'width'          => $request->cargo_width[$index],
                'height'         => $request->cargo_height[$index],
                'cargo_picture'  => $picturePath,
            ]);
        }

        return redirect()->route('staff.cargo_booking.create')
                         ->with('success', 'Cargo booking created successfully!');
    }

    // List all bookings for review
    public function reviewBookings()
    {
        $bookings = Booking::where('booking_type', 'cargo')
                            ->with('cargoBookings.cargoItem', 'voyage.vessel', 'voyage.routePort')
                            ->orderBy('booking_date', 'desc')
                            ->get();

        return view('staff.cargo_booking.review', compact('bookings'));
    }

    // Approve booking
    public function approveBooking($bookingRefNo)
    {
        $booking = Booking::findOrFail($bookingRefNo);
        $booking->booking_status = 'Confirmed';
        $booking->save();

        return back()->with('success', 'Booking approved successfully.');
    }

    // Reject booking
    public function rejectBooking($bookingRefNo)
    {
        $booking = Booking::findOrFail($bookingRefNo);
        $booking->booking_status = 'Canceled';
        $booking->save();

        return back()->with('success', 'Booking rejected successfully.');
    }
}
