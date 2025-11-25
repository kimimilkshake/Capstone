<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; 
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
            'cargoItems', // ✅ Pass it to Blade
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

    public function showCargoBookingForm()
    {
    $cargoItems = CargoItem::all();

    return view('passenger.cargobooking', compact('cargoItems'));
    }

   // Step 1: POST form → save to session and create temporary booking
    public function confirmCargo(Request $request)
    {
        $request->validate([
            'sender_firstname' => 'required|string|max:255',
            'sender_lastname' => 'required|string|max:255',
            'sender_contact' => 'required|string|max:20',
            'consignee_firstname' => 'required|string|max:255',
            'consignee_lastname' => 'required|string|max:255',
            'consignee_contact' => 'required|string|max:20',
            'voyage_id' => 'required|exists:voyage,voyage_id',
            'cargo_item_id.*' => 'required|exists:cargo_item,cargo_item_id',
            'cargo_quantity.*' => 'required|integer|min:1',
            'cargo_weight.*' => 'required|numeric|min:0',
        ]);

        // Store temporary booking in DB (status = Pending)
        DB::beginTransaction();
        try {
            $senderId = DB::table('sender')->insertGetId([
                'sender_name' => $request->sender_firstname . ' ' . $request->sender_lastname,
                'sender_contactno' => $request->sender_contact,
                'sender_email' => $request->sender_email ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $consigneeId = DB::table('consignee')->insertGetId([
                'consignee_name' => $request->consignee_firstname . ' ' . $request->consignee_lastname,
                'consignee_contactno' => $request->consignee_contact,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $bookingId = DB::table('booking')->insertGetId([
                'booking_date' => now(),
                'booking_type' => 'cargo',
                'booking_status' => 'Pending',
                'voyage_id' => $request->voyage_id,
                'sender_id' => $senderId,
                'consignee_id' => $consigneeId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($request->cargo_item_id as $index => $itemId) {
                $picturePath = null;
                if ($request->hasFile('cargo_picture.' . $index)) {
                    $picturePath = $request->file('cargo_picture')[$index]->store('cargo_pictures', 'public');
                }

                DB::table('cargo_booking')->insert([
                    'booking_ref_no' => $bookingId,
                    'cargo_item_id' => $itemId,
                    'quantity' => $request->cargo_quantity[$index],
                    'weight' => $request->cargo_weight[$index],
                    'length' => $request->cargo_length[$index] ?? 0,
                    'width' => $request->cargo_width[$index] ?? 0,
                    'height' => $request->cargo_height[$index] ?? 0,
                    'cargo_picture' => $picturePath,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();
            return redirect()->route('cargobooking.show', ['booking_ref_no' => $bookingId]);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to save booking: ' . $e->getMessage());
        }
    }

    // Step 2: GET confirmation page
 // Show confirmation page
public function showCargoConfirmation($bookingRef)
{
    // Fetch cargo items with freight & arrastre rates from cargo_item
    $cargoItems = \DB::table('cargo_booking')
        ->join('cargo_item', 'cargo_booking.cargo_item_id', '=', 'cargo_item.cargo_item_id')
        ->select(
            'cargo_booking.*',
            'cargo_item.cargo_item_description',
            'cargo_item.cargo_item_classification',
            'cargo_item.cargo_item_freight as freight',
            'cargo_item.cargo_item_arrastre as arrastre'
        )
        ->where('booking_ref_no', $bookingRef)
        ->get();

    $booking = \DB::table('booking')->where('booking_ref_no', $bookingRef)->first();
    $sender = \DB::table('sender')->where('sender_id', $booking->sender_id)->first();
    $consignee = \DB::table('consignee')->where('consignee_id', $booking->consignee_id)->first();

    return view('passenger.cargobooking_confirm', compact('booking', 'sender', 'consignee', 'cargoItems'));
}


// Cancel booking
public function cancelCargo($bookingRef)
{
    \DB::table('booking')->where('booking_ref_no', $bookingRef)->update([
        'booking_status' => 'Canceled',
        'updated_at' => now(),
    ]);

    return redirect()->route('cargobooking')->with('success', 'Cargo booking canceled.');
}


    // Step 4: Finalize booking (staff approval)
    public function finalizeCargo($bookingId)
    {

        return redirect()->route('cargobooking.success')->with('success', 'Cargo booking submitted!');
    }
}