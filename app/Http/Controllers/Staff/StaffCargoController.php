<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\CargoBooking;
use App\Models\CargoItem;
use App\Models\CargoReceipt;
use App\Models\RoutePort;
use App\Models\Voyage;
use App\Models\Sender;
use App\Models\Consignee;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Mail\CargoBookingApproved;
use App\Mail\CargoBookingRejected;
use Illuminate\Support\Facades\Mail;


class StaffCargoController extends Controller
{
    /**
     * Check if user is staff
     */
    private function isStaff()
    {
        return auth()->guard('staff')->check();
    }

    /**
     * Check if user is admin
     */
    private function isAdmin()
    {
        return auth()->guard('admin')->check();
    }

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

    // Create notification for new cargo booking
    Notification::create([
        'cargo_receipt_id' => null,
        'payment_id' => null,
        'booking_ref_no' => $booking->booking_ref_no,
        'notification_message' => "New cargo booking #{$booking->booking_ref_no} from {$sender->sender_name} is pending review",
        'notification_type' => 'cargo booking approval',
        'notification_status' => 'approved',
        'notification_created' => now(),
    ]);

    // Return with booking reference
    return redirect()->back()->with('success', 
        "Success! Your booking_ref_no: {$booking->booking_ref_no} is being queued for approval."
    );
}

    /**
     * Show only pending cargo bookings
     */
public function pending(Request $request)
{
    $search = $request->input('search');

    $bookings = Booking::where('booking_status', 'Pending')
        ->where('booking_type', 'Cargo')
        ->with(['sender', 'consignee', 'voyage', 'cargoBookings'])
        ->when($search, function($query, $search) {
            $query->where('booking_ref_no', 'like', "%{$search}%")
                  ->orWhereHas('sender', function($q) use ($search) {
                      $q->where('sender_name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('consignee', function($q) use ($search) {
                      $q->where('consignee_name', 'like', "%{$search}%");
                  });
        })
        ->orderBy('created_at', 'desc')
        ->paginate(10);

    return view('authorized.staff.pendingcargo', compact('bookings'));
}

    /**
     * Show full booking (read-only)
     */
    public function show($id)
    {
        // Check if admin is trying to access
        if ($this->isAdmin()) {
            return redirect()->back()->with('error', 'Please use a Staff account to approve Cargo Bookings');
        }

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
    $booking = Booking::with([
        'sender',
        'consignee',
        'voyage.routePort',
        'cargoBookings.cargoItem'
    ])->where('booking_ref_no', $id)->firstOrFail();

    // For dropdowns (classification + descriptions)
    $classifications = CargoItem::select('cargo_item_classification')
        ->distinct()
        ->pluck('cargo_item_classification');

    $descriptions = CargoItem::select('cargo_item_description')
        ->distinct()
        ->pluck('cargo_item_description');

    // For cargo item lookup (same as your original)
    $cargoItems = CargoItem::all();
    
    // Load all routes for the Route Destination dropdown
    $routes = RoutePort::all(); // or whatever your model is called

    return view('authorized.staff.editcargo', compact(
        'booking',
        'cargoItems',
        'classifications',
        'descriptions'
    ));
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
    $booking = Booking::with(['sender', 'consignee', 'cargoBookings.cargoItem', 'voyage'])->where('booking_ref_no', $id)->firstOrFail();
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

    // Create notification for approved cargo booking
    $senderName = $booking->sender ? $booking->sender->sender_name : 'Customer';
    Notification::create([
        'cargo_receipt_id' => null,
        'payment_id' => null,
        'booking_ref_no' => $booking->booking_ref_no,
        'notification_message' => "Cargo booking #{$booking->booking_ref_no} from {$senderName} has been approved",
        'notification_type' => 'cargo booking approval',
        'notification_status' => 'approved',
        'notification_created' => now(),
    ]);

    // Send email
    Mail::to($booking->sender->sender_email)
        ->send(new \App\Mail\CargoBookingApproved(
            $booking,
            $booking->sender,
            $booking->consignee,
            $booking->cargoBookings
        ));

    return redirect()->route('cargo.bookings.pending')
        ->with('success', 'Booking approved, added to cargo receipts, and email sent.');
}


    /**
     * Reject a booking
     */
public function reject($id)
{
    $booking = Booking::with(['sender', 'consignee', 'cargoBookings.cargoItem', 'voyage'])->where('booking_ref_no', $id)->firstOrFail();
    $booking->booking_status = 'Canceled';
    $booking->save();

        // Create notification for rejected cargo booking
        $senderName = $booking->sender ? $booking->sender->sender_name : 'Customer';
        Notification::create([
            'cargo_receipt_id' => null,
            'payment_id' => null,
            'booking_ref_no' => $booking->booking_ref_no,
            'notification_message' => "Cargo booking #{$booking->booking_ref_no} from {$senderName} has been rejected",
            'notification_type' => 'cargo booking approval',
            'notification_status' => 'rejected',
            'notification_created' => now(),
        ]);

            Mail::to($booking->sender->sender_email)
        ->send(new \App\Mail\CargoBookingRejected(
            $booking,
            $booking->sender,
            $booking->consignee,
            $booking->cargoBookings
        ));

    // Send rejection email
    Mail::to($booking->sender->sender_email)
        ->send(new \App\Mail\CargoBookingRejected(
            $booking,
            $booking->sender,
            $booking->consignee,
            $booking->cargoBookings
        ));

    return redirect()->route('cargo.bookings.pending')
        ->with('success', 'Booking has been canceled and email sent to the sender.');
}


    
}
