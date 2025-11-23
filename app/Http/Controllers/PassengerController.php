<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Passenger;
use App\Models\CargoItem; // ✅ Add this
use App\Models\Voyage;
use Carbon\Carbon;

class PassengerController extends Controller
{
    public function index(Request $request)
    {
        $routeFrom = $request->query('route_from');
        $routeTo = $request->query('route_to');
        $departureDate = $request->query('departure_date');
        $voyageId = $request->query('voyage_id');
        $type = $request->query('type'); // 👈 booking type from radio buttons

        // Get voyage information from voyage table with relationships
        $voyage = Voyage::with(['vessel.accommodations', 'routePort'])
            ->where('voyage_id', $voyageId)
            ->first();

        if (!$voyage) {
            return redirect()->route('bookingtype')->with('error', 'Voyage not found.');
        }

        $vesselName = $voyage->vessel->vessel_name ?? 'Unknown Vessel';
        $departureTime = $voyage->voyage_estimated_TD;
        $portOfOrigin = $voyage->routePort->port_origin_name ?? 'Unknown Port';
        $accommodations = $voyage->vessel->accommodations ?? collect();

        if ($departureTime) {
            $departureTime = Carbon::parse($departureTime)->format('g:i A');
        }

        // ✅ Add this: fetch cargo items for the Blade
        $cargoItems = $type === 'cargo' ? CargoItem::all() : collect();

        // ✅ If user selected "cargo", load passenger.cargobooking
        // Otherwise load passenger.passengerbooking
        $view = $type === 'cargo'
            ? 'passenger.cargobooking'
            : 'passenger.passengerbooking';

        return view($view, compact(
            'routeFrom',
            'routeTo',
            'departureDate',
            'vesselName',
            'departureTime',
            'portOfOrigin',
            'cargoItems' // ✅ Pass it to Blade
            'voyage',
            'accommodations'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'passenger_firstname' => 'required|string|max:100',
            'passenger_midinitial' => 'nullable|string|max:5',
            'passenger_lastname' => 'required|string|max:100',
            'passenger_suffix' => 'nullable|string|max:10',
            'passenger_age' => 'required|integer|min:0',
            'passenger_gender' => 'required|string|in:M,F',
            'passenger_type' => 'required|string',
            'passenger_address' => 'required|string|max:255',
            'passenger_contactno' => 'required|string|max:20',
            'passenger_email' => 'required|email|max:255',
            'passenger_idnumber' => 'required|string|max:50',
        ]);

        Passenger::create($validated);

        return redirect()->route('bookingtype')->with('success', 'Passenger booked successfully!');
    }

    public function storeCargo(Request $request)
    {
        // 1. Validate fields
        $request->validate([
            'sender_firstname' => 'required',
            'sender_lastname' => 'required',
            'sender_contact' => 'required',
            'sender_email' => 'nullable|email',

            'consignee' => 'required',
            'receiver_contact' => 'required',

            'cargo_description' => 'required',
            'cargo_quantity' => 'required|integer|min:1',
        ]);

        // 2. Create full sender name
        $senderFullName = trim(
            $request->sender_firstname . ' ' .
            ($request->sender_mi ? $request->sender_mi . '. ' : '') .
            $request->sender_lastname . ' ' .
            ($request->sender_suffix ?? '')
        );

        // 3. Insert Sender
        $senderId = \DB::table('sender')->insertGetId([
            'sender_name' => $senderFullName,
            'sender_contactno' => $request->sender_contact,
            'sender_email' => $request->sender_email,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 4. Insert Consignee
        $consigneeId = \DB::table('consignee')->insertGetId([
            'consignee_name' => $request->consignee,
            'consignee_contactno' => $request->receiver_contact,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 5. Find or Create Cargo Item Record
        $cargoItemId = \DB::table('cargo_item')->insertGetId([
            'cargo_item_classification' => 'General Cargo',
            'cargo_item_description' => $request->cargo_description,
            'cargo_item_freight' => 0,
            'cargo_item_arrastre' => 0,
            'cargo_item_type' => 'Type A',
            'cargo_item_volume' => 0,
            'cargo_item_weight' => 0,
            'cargo_item_length' => 0,
            'cargo_item_height' => 0,
            'cargo_item_width' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 6. Identify voyage (your form does NOT pass voyage_id yet)
        $voyageId = null;

        // 7. Create Cargo Receipt (actual cargo booking)
        $cargoReceiptId = \DB::table('cargo_receipt')->insertGetId([
            'sender_id' => $senderId,
            'consignee_id' => $consigneeId,
            'cargo_item_id' => $cargoItemId,
            'voyage_id' => $voyageId,
            'payment_id' => null,
            'booking_ref_no' => null,
            'cargo_item_qty' => $request->cargo_quantity,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 8. Auto-create a Notification
        \DB::table('notification')->insert([
            'cargo_receipt_id' => $cargoReceiptId,
            'payment_id' => null,
            'notification_message' => 'Cargo booking submitted. Waiting for approval.',
            'notification_type' => 'Cargo Booking Approval',
            'notification_status' => 'Pending',
            'notification_created' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 9. Redirect to success page
        return redirect()->route('cargobooking.success');
    }
}
