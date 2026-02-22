<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\CargoBooking;
use App\Models\CargoItem;
use App\Models\CargoClassification;
use App\Models\MeasurementUnit;
use App\Models\CargoReceipt;
use App\Models\Payment;
use App\Models\RoutePort;
use App\Models\Voyage;
use App\Models\Sender;
use App\Models\Consignee;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Mail\CargoBookingApproved;
use App\Mail\CargoBookingRejected;
use Illuminate\Support\Facades\Mail;
use App\Services\BillOfLadingPdf;
use App\Models\BillOfLading;


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

        if (!$this->isStaff()) {
            abort(403);
        }

        // Show only voyages scheduled for today (departures today)
        $today = \Carbon\Carbon::today()->toDateString();
        $voyages = Voyage::with('routePort')
            ->whereDate('voyage_departure_date', $today)
            ->get();
        $cargoItems = collect(CargoItem::all()); // <-- wrap in collect()
        $cargoClassifications = CargoClassification::orderBy('cargo_classification_name')->get();
        return view('authorized.staff.cargobooking', compact('voyages', 'cargoItems', 'cargoClassifications'));
    }


    /**
     * Store new cargo booking
     */
public function store(Request $request)
{
    if (!$this->isStaff()) {
        abort(403);
    }

    // Validate input
    $request->validate([
        'sender_firstname' => 'required|string|max:255',
        'sender_lastname' => 'required|string|max:255',
        'sender_contact' => 'required|string|max:20',
        'sender_email' => 'required|email',
        'sender_tin' => 'nullable|string|max:50',
        'consignee_firstname' => 'required|string|max:255',
        'consignee_lastname' => 'required|string|max:255',
        'consignee_contact' => 'required|string|max:20',
        'voyage_id' => 'required|exists:voyage,voyage_id',
        'cargo_classification.*' => 'required|string',
        'cargo_item_id.*' => 'required|exists:cargo_item,cargo_item_id',
        'cargo_quantity.*' => 'required|integer|min:1',
        'cargo_weight.*' => 'required|numeric|min:0',
        'cargo_length.*' => 'required|numeric|min:0',
        'cargo_width.*' => 'required|numeric|min:0',
        'cargo_height.*' => 'required|numeric|min:0',
        'measurement_unit.*' => 'nullable|string|in:cm,in',
        'cargo_picture.*' => 'nullable|image|max:2048',
    ]);

    // Verify voyage date within next 30 days and not in the past
    $voyage = Voyage::find($request->voyage_id);
        if ($voyage) {
            $dep = \Carbon\Carbon::parse($voyage->voyage_departure_date)->startOfDay();
            $today = \Carbon\Carbon::today();
            $max = $today->copy()->addDays(30);
            if ($dep->lt($today) || $dep->gt($max)) {
                return back()->withErrors(['voyage_id' => 'Selected voyage must be within the next 30 days and not before today.'])->withInput();
            }
        }

        // Create Sender
        $sender = Sender::create([
            'sender_name' => $request->sender_firstname . ' ' . $request->sender_lastname,
            'sender_contactno' => $request->sender_contact,
            'sender_email' => $request->sender_email,
            'sender_tin' => $request->sender_tin ?? null,
        ]);

        // Create Consignee (no TIN)
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
        $cargoItem = CargoItem::find($cargoId);
        $cargoBooking = new CargoBooking();
        $cargoBooking->booking_ref_no = $booking->booking_ref_no;
        $cargoBooking->cargo_item_id = $cargoId;
        $cargoBooking->route_code_id = $cargoItem ? $cargoItem->route_code_id : null;
        $cargoBooking->quantity = $request->cargo_quantity[$index];
        $cargoBooking->weight = $request->cargo_weight[$index];
        $cargoBooking->length = $request->cargo_length[$index];
        $cargoBooking->width = $request->cargo_width[$index];
        $cargoBooking->height = $request->cargo_height[$index];

        // Save cargo_classification_id from selected name
        $classificationName = $request->cargo_classification[$index] ?? null;
        if ($classificationName) {
            $classification = \App\Models\CargoClassification::where('cargo_classification_name', $classificationName)->first();
            if ($classification) {
                $cargoBooking->cargo_classification_id = $classification->cargo_classification_id;
            }
        }

        // Store measurement unit if provided
        if ($request->has("measurement_unit.$index") && $request->measurement_unit[$index]) {
            $measurementUnit = MeasurementUnit::where('measurement_unit_name', $request->measurement_unit[$index])->first();
            if ($measurementUnit) {
                $cargoBooking->measurement_unit_id = $measurementUnit->measurement_unit_id;
            }
        }

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
    if (!$this->isStaff()) {
        abort(403);
    }

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

        $payment = Payment::where('booking_ref_no', $id)->first();

        return view('authorized.staff.showcargo', compact('booking', 'payment'));
    }

    /**
     * Edit cargo item details only
     */
public function edit($id)
{
    if (!$this->isStaff()) {
        abort(403);
    }

    $booking = Booking::with([
        'sender',
        'consignee',
        'voyage.routePort',
        'cargoBookings.cargoItem'
    ])->where('booking_ref_no', $id)->firstOrFail();

    // For dropdowns (use cargo_classification table)
    $cargoClassifications = CargoClassification::orderBy('cargo_classification_name')->get();

    // For cargo item lookup
    $cargoItems = CargoItem::all();
    
    // Load all routes for the Route Destination dropdown
    $routes = RoutePort::all(); // or whatever your model is called

    return view('authorized.staff.editcargo', compact(
        'booking',
        'cargoItems',
        'cargoClassifications'
    ));
}


    /**
     * Update cargo item details
     */
    public function update(Request $request, $id)
    {
        if (!$this->isStaff()) {
            abort(403);
        }

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
    if (!$this->isStaff()) {
        abort(403);
    }
    
    $booking = Booking::with(['sender', 'consignee', 'cargoBookings.cargoItem', 'voyage'])->where('booking_ref_no', $id)->firstOrFail();
    $booking->booking_status = 'Confirmed';
    $booking->save();

    // Calculate total cost
    $totalCost = 0;
    foreach ($booking->cargoBookings as $cargo) {
        $freight = $cargo->cargoItem->cargo_item_freight;
        $arrastre = $cargo->cargoItem->cargo_item_arrastre;
        $cbm = ($cargo->length * $cargo->width * $cargo->height) / 1000000;
        $subtotal = ($freight + $arrastre) * $cbm * $cargo->quantity;
        $totalCost += $subtotal;
    }

    // Create payment record with mode='Cash' and status='Completed'
    $payment = Payment::create([
        'booking_ref_no' => $booking->booking_ref_no,
        'mode_of_payment' => 'Cash',
        'payment_status' => 'Completed',
        'total_amount' => $totalCost,
        'payment_date' => now(),
    ]);

    // Move cargo items to cargo_receipt and create bill of lading
    $cargoReceiptIds = [];
    foreach ($booking->cargoBookings as $cargo) {
        $receipt = new CargoReceipt();
        $receipt->booking_ref_no = $booking->booking_ref_no;
        $receipt->sender_id = $booking->sender_id;
        $receipt->consignee_id = $booking->consignee_id;
        $receipt->cargo_item_id = $cargo->cargo_item_id ?? null;
        $receipt->voyage_id = $booking->voyage_id;
        $receipt->cargo_item_qty = $cargo->quantity;
        $receipt->save();
        
        $cargoReceiptIds[] = $receipt->cargo_receipt_id;
    }

    // Create bill of lading records with voyage details (vessel name, loading port, unloading port)
    $staffId = auth()->guard('staff')->user()->staff_id ?? 1; // fallback if needed
    $voyage = $booking->voyage;
    
    foreach ($cargoReceiptIds as $receiptId) {
        BillOfLading::create([
            'cargo_receipt_id' => $receiptId,
            'staff_id' => $staffId,
            'bl_date_issued' => now()->toDateString(),
            'bl_loading_port' => $voyage->loading_port ?? 'Not specified',
            'bl_unloading_port' => $voyage->unloading_port ?? 'Not specified',
        ]);
    }

    // Create notification for approved cargo booking
    $senderName = $booking->sender ? $booking->sender->sender_name : 'Customer';
    Notification::create([
        'cargo_receipt_id' => null,
        'payment_id' => $payment->payment_id ?? null,
        'booking_ref_no' => $booking->booking_ref_no,
        'notification_message' => "Cargo booking #{$booking->booking_ref_no} from {$senderName} has been approved",
        'notification_type' => 'cargo booking approval',
        'notification_status' => 'approved',
        'notification_created' => now(),
    ]);

    // Send email with payment details
    Mail::to($booking->sender->sender_email)
        ->send(new \App\Mail\CargoBookingApproved(
            $booking,
            $booking->sender,
            $booking->consignee,
            $booking->cargoBookings,
            $payment
        ));

    return redirect()->route('cargo.bookings.pending')
        ->with('success', 'Booking approved, payment recorded, and email sent.');
}


    /**
     * Reject a booking
     */
public function reject(Request $request, $id)
{
    if (!$this->isStaff()) {
        abort(403);
    }

    $request->validate(['reason' => 'required|string|max:1000']);

    $booking = Booking::with(['sender', 'consignee', 'cargoBookings.cargoItem', 'voyage'])->where('booking_ref_no', $id)->firstOrFail();
    $booking->booking_status = 'Canceled';
    $booking->save();

    $reason = $request->input('reason');

    // Create notification for rejected cargo booking with reason
    $senderName = $booking->sender ? $booking->sender->sender_name : 'Customer';
    Notification::create([
        'cargo_receipt_id' => null,
        'payment_id' => null,
        'booking_ref_no' => $booking->booking_ref_no,
        'notification_message' => "Cargo booking #{$booking->booking_ref_no} from {$senderName} has been rejected: {$reason}",
        'notification_type' => 'cargo booking approval',
        'notification_status' => 'rejected',
        'notification_created' => now(),
    ]);

    // Send rejection email (include reason)
    Mail::to($booking->sender->sender_email)
        ->send(new \App\Mail\CargoBookingRejected(
            $booking,
            $booking->sender,
            $booking->consignee,
            $booking->cargoBookings,
            $reason
        ));

    return redirect()->route('cargo.bookings.pending')
        ->with('success', 'Booking has been canceled and email sent to the sender.');
}


    
        /**
         * Return the Bill of Lading PDF for a booking (inline view)
         */
        public function bolPdf($id)
        {
            $booking = Booking::with(['sender', 'consignee', 'cargoBookings.cargoItem', 'voyage'])
                ->where('booking_ref_no', $id)
                ->firstOrFail();

            $pdf = BillOfLadingPdf::generate($booking);

            return response($pdf, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="bill_of_lading_' . $id . '.pdf"');
        }

    /**
     * Display the Bill of Lading in a formatted HTML view (printable)
     */
    public function bolView($id)
    {
        $booking = Booking::with(['sender', 'consignee', 'cargoBookings.cargoItem', 'voyage'])
            ->where('booking_ref_no', $id)
            ->firstOrFail();

        return view('authorized.staff.bill_of_lading', compact('booking'));
    }

}
