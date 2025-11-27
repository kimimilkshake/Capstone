<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\CargoBooking;
use App\Models\CargoItem;
use App\Models\CargoReceipt;
use App\Models\Voyage;
use App\Models\Sender;
use App\Models\Consignee;
use Illuminate\Http\Request;

class StaffCargoController extends Controller
{
    /**
     * Show cargo booking form
     */
    public function create()
    {
    $voyages = Voyage::with('routePort')->get();
    $cargoItems = collect(CargoItem::all()); // <-- wrap in collect()
    return view('authorized.staff.cargobooking', compact('voyages', 'cargoItems'));
    }


    /**
     * Store new cargo booking
     */
public function store(Request $request)
{
    // Validate input
    $request->validate([
        'sender_firstname' => 'required|string|max:255',
        'sender_lastname' => 'required|string|max:255',
        'sender_contact' => 'required|string|max:20',
        'sender_email' => 'nullable|email',
        'consignee_firstname' => 'required|string|max:255',
        'consignee_lastname' => 'required|string|max:255',
        'consignee_contact' => 'required|string|max:20',
        'voyage_id' => 'required|exists:voyage,voyage_id',
        'cargo_item_id.*' => 'required|exists:cargo_item,cargo_item_id',
        'cargo_quantity.*' => 'required|integer|min:1',
        'cargo_length.*' => 'required|numeric|min:0',
        'cargo_width.*' => 'required|numeric|min:0',
        'cargo_height.*' => 'required|numeric|min:0',
        'cargo_weight.*' => 'required|numeric|min:0',
        'cargo_picture.*' => 'nullable|image|max:2048',
    ]);

// Create Sender
$sender = Sender::create([
    'sender_name' => $request->sender_firstname . ' ' . $request->sender_lastname,
    'sender_contactno' => $request->sender_contact,
    'sender_email' => $request->sender_email,
]);

// Create Consignee
$consignee = Consignee::create([
    'consignee_name' => $request->consignee_firstname . ' ' . $request->consignee_lastname,
    'consignee_contactno' => $request->consignee_contact,
]);


    // Create Booking (status: Pending)
    $booking = Booking::create([
        'booking_type' => 'Cargo',
        'booking_status' => 'Pending',
        'voyage_id' => $request->voyage_id,
        'sender_id' => $sender->sender_id,
        'consignee_id' => $consignee->consignee_id,
    ]);

    // Create Cargo Items
    foreach ($request->cargo_item_id as $index => $cargoId) {
        $cargoBooking = new CargoBooking();
        $cargoBooking->booking_ref_no = $booking->booking_ref_no;
        $cargoBooking->cargo_item_id = $cargoId;
        $cargoBooking->quantity = $request->cargo_quantity[$index];
        $cargoBooking->length = $request->cargo_length[$index];
        $cargoBooking->width = $request->cargo_width[$index];
        $cargoBooking->height = $request->cargo_height[$index];
        $cargoBooking->weight = $request->cargo_weight[$index];

        // Handle image upload
        if ($request->hasFile("cargo_picture.$index")) {
            $file = $request->file("cargo_picture.$index");
            $filename = time() . "_$index." . $file->getClientOriginalExtension();
            $file->storeAs('public/cargo_pictures', $filename);
            $cargoBooking->cargo_picture = $filename;
        }

        $cargoBooking->save();
    }

    // Return with booking reference
    return redirect()->back()->with('success', 
        "Success! Your booking_ref_no: {$booking->booking_ref_no} is being queued for approval."
    );
}

    /**
     * Show only pending cargo bookings
     */
    public function pending()
    {
        $bookings = Booking::where('booking_status', 'Pending')
            ->where('booking_type', 'Cargo')
            ->with(['sender', 'consignee', 'voyage', 'cargoBookings'])
            ->paginate(10);

        return view('authorized.staff.pendingcargo', compact('bookings'));
    }

    /**
     * Show full booking (read-only)
     */
    public function show($id)
    {
        $booking = Booking::where('booking_ref_no', $id)
            ->with(['sender', 'consignee', 'voyage', 'cargoBookings'])
            ->firstOrFail();

        return view('authorized.staff.showcargo', compact('booking'));
    }

    /**
     * Edit cargo item details only
     */
public function edit($id)
{
    $booking = Booking::where('booking_ref_no', $id)
        ->with('cargoBookings')
        ->firstOrFail();

    // Load all cargo items for the dropdown
    $cargoItems = CargoItem::all();

    return view('authorized.staff.editcargo', compact('booking', 'cargoItems'));
}

    /**
     * Update cargo item details
     */
    public function update(Request $request, $id)
    {
        $booking = Booking::where('booking_ref_no', $id)
            ->with('cargoBookings')
            ->firstOrFail();

        foreach ($booking->cargoBookings as $index => $cargo) {
            $cargo->update([
                'quantity' => $request->quantity[$index],
                'length'   => $request->length[$index],
                'width'    => $request->width[$index],
                'height'   => $request->height[$index],
                'weight'   => $request->weight[$index],
            ]);
        }

        return redirect()
            ->route('cargo.bookings.show', $id)
            ->with('success', 'Cargo item details updated successfully.');
    }

    /**
     * Approve a booking
     */
    public function approve($id)
    {
        $booking = Booking::where('booking_ref_no', $id)->firstOrFail();
        $booking->booking_status = 'Confirmed';
        $booking->save();

        // Move cargo items to cargo_receipt
        foreach ($booking->cargoBookings as $cargo) {
            $receipt = new CargoReceipt();
            $receipt->booking_ref_no = $booking->booking_ref_no;
            $receipt->sender_id = $booking->sender_id;
            $receipt->consignee_id = $booking->consignee_id;
            $receipt->cargo_item_id = $cargo->cargo_item_id ?? null;
            $receipt->voyage_id = $booking->voyage_id;
            $receipt->cargo_item_qty = $cargo->quantity;
            $receipt->save();
        }

        return redirect()->route('cargo.bookings.pending')
            ->with('success', 'Booking approved and added to cargo receipts.');
    }

    /**
     * Reject a booking
     */
    public function reject($id)
    {
        $booking = Booking::where('booking_ref_no', $id)->firstOrFail();
        $booking->booking_status = 'Canceled';
        $booking->save();

        return redirect()->route('cargo.bookings.pending')
            ->with('success', 'Booking has been canceled.');
    }
    public function getCargoItemsByVoyage($voyageId)
    {
    $voyage = Voyage::with('routePort')->findOrFail($voyageId);

    $cargoItems = CargoItem::where('route_port_id', $voyage->route_port_id)->get();

    return response()->json($cargoItems);
    }
}
