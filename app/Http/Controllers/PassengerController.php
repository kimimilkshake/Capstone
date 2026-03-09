<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Passenger;
use App\Models\CargoItem; // ✅ Add this
use App\Models\CargoClassification;
use App\Models\MeasurementUnit;
use App\Models\Voyage;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

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

        // Get cot plan image URL
        $cotPlanUrl = $voyage->vessel && $voyage->vessel->vessel_cot_plan_url
            ? asset('storage/' . $voyage->vessel->vessel_cot_plan_url)
            : asset('images/sample-cot-plan.jpg');

        if ($departureTime) {
            $departureTime = Carbon::parse($departureTime)->format('g:i A');
        }

        // ✅ Add this: fetch cargo items and classifications for the Blade
        $cargoItems = $type === 'cargo' ? CargoItem::with('measurementUnit')->get() : collect();
        $cargoClassifications = $type === 'cargo' ? CargoClassification::orderBy('cargo_classification_name')->get() : collect();
        $measurementUnits = $type === 'cargo'
            ? MeasurementUnit::whereNotNull('measurement_unit_abbreviation')
                ->orderBy('measurement_unit_name')
                ->get()
            : collect();

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
            'cargoClassifications', // ✅ Pass classifications
            'measurementUnits',
            'voyage',
            'accommodations',
            'cotPlanUrl'
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
            'sender_email' => 'required|email',
            'sender_tin' => 'nullable|string|max:50',
            'consignee_firstname' => 'required|string|max:255',
            'consignee_lastname' => 'required|string|max:255',
            'consignee_contact' => 'required|string|max:20',
            'voyage_id' => 'required|exists:voyage,voyage_id',
            'cargo_classification.*' => 'required|integer|exists:cargo_classification,cargo_classification_id',
            'cargo_item_id.*' => 'required|exists:cargo_item,cargo_item_id',
            'cargo_quantity.*' => 'required|integer|min:1',
            'cargo_weight.*' => 'required|numeric|min:0.01',
            'cargo_length.*' => 'required|numeric|min:0.01',
            'cargo_width.*' => 'required|numeric|min:0.01',
            'cargo_height.*' => 'required|numeric|min:0.01',
            'measurement_unit.*' => ['nullable', 'string', Rule::exists('measurement_unit', 'measurement_unit_abbreviation')],
            'cargo_cbm.*' => 'nullable|numeric|min:0',
            'cargo_picture.*' => 'nullable|image|max:2048',
        ]);

        // Validate voyage date within 30 days and not in the past
        $voyage = Voyage::find($request->voyage_id);
        if ($voyage) {
            $dep = \Carbon\Carbon::parse($voyage->voyage_departure_date)->startOfDay();
            $today = \Carbon\Carbon::today();
            $max = $today->copy()->addDays(30);
            if ($dep->lt($today) || $dep->gt($max)) {
                return back()->with('error', 'Selected voyage must be within the next 30 days and not before today.');
            }
        }

        // Store temporary booking in DB (status = Pending)
        DB::beginTransaction();
        try {
            $senderId = DB::table('sender')->insertGetId([
                'sender_name' => $request->sender_firstname . ' ' . $request->sender_lastname,
                'sender_contactno' => $request->sender_contact,
                'sender_email' => $request->sender_email ?? null,
                'sender_tin' => $request->sender_tin ?? null,
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
                $cargoItem = CargoItem::find($itemId);
                $picturePath = null;
                if ($request->hasFile('cargo_picture.' . $index)) {
                    $picturePath = $request->file('cargo_picture')[$index]->store('cargo_pictures', 'public');
                }

                $measurementUnitId = null;
                if ($request->has("measurement_unit.$index") && $request->measurement_unit[$index]) {
                    $measurementUnit = MeasurementUnit::where('measurement_unit_abbreviation', $request->measurement_unit[$index])->first();
                    if ($measurementUnit) {
                        $measurementUnitId = $measurementUnit->measurement_unit_id;
                    }
                }

                if (!$measurementUnitId && $cargoItem && $cargoItem->measurement_unit_id) {
                    $measurementUnitId = $cargoItem->measurement_unit_id;
                }

                $unitAbbreviation = $request->measurement_unit[$index] ?? 'cm';
                $length = (float) $request->cargo_length[$index];
                $width = (float) $request->cargo_width[$index];
                $height = (float) $request->cargo_height[$index];

                if ($unitAbbreviation === 'in') {
                    $length *= 2.54;
                    $width *= 2.54;
                    $height *= 2.54;
                }

                $computedCbm = ($length * $width * $height) / 1000000;
                $postedCbm = $request->cargo_cbm[$index] ?? null;
                $finalCbm = is_numeric($postedCbm) ? (float) $postedCbm : $computedCbm;

                DB::table('cargo_booking')->insert([
                    'booking_ref_no' => $bookingId,
                    'cargo_item_id' => $itemId,
                    'cargo_classification_id' => $request->cargo_classification[$index] ?? null,
                    'route_code_id' => $cargoItem ? $cargoItem->route_code_id : null,
                    'measurement_unit_id' => $measurementUnitId,
                    'with_measurement' => $cargoItem ? $cargoItem->cargo_item_measure_required : null,
                    'quantity' => $request->cargo_quantity[$index],
                    'weight' => $request->cargo_weight[$index],
                    'length' => $request->cargo_length[$index],
                    'width' => $request->cargo_width[$index],
                    'height' => $request->cargo_height[$index],
                    'cbm' => round($finalCbm, 4),
                    'cargo_picture' => $picturePath,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Create notification for new cargo booking
            $senderName = $request->sender_firstname . ' ' . $request->sender_lastname;
            Notification::create([
                'cargo_receipt_id' => null,
                'payment_id' => null,
                'booking_ref_no' => $bookingId,
                'notification_message' => "New cargo booking #{$bookingId} from {$senderName} is pending review",
                'notification_type' => 'cargo booking approval',
                'notification_status' => 'approved',
                'notification_created' => now(),
            ]);

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
        // Fetch cargo items with freight rates from cargo_item
        $cargoItems = \DB::table('cargo_booking')
            ->join('cargo_item', 'cargo_booking.cargo_item_id', '=', 'cargo_item.cargo_item_id')
            ->leftJoin('measurement_unit', 'cargo_booking.measurement_unit_id', '=', 'measurement_unit.measurement_unit_id')
            ->leftJoin('cargo_classification', 'cargo_booking.cargo_classification_id', '=', 'cargo_classification.cargo_classification_id')
            ->select(
                'cargo_booking.*',
                'cargo_item.cargo_item_description',
                'cargo_item.cargo_item_freight as freight',
                'measurement_unit.measurement_unit_abbreviation',
                'cargo_classification.cargo_classification_name'
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
