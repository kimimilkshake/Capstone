<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Passenger;
use App\Models\PassengerTicket;
use App\Models\Payment;
use App\Models\Voyage;
use App\Models\Accommodation;
use App\Models\Notification;
use App\Jobs\SendTicketEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StaffPassengerController extends Controller
{
    /**
     * Show passenger booking form for staff
     */
    public function create()
    {
        $voyages = Voyage::with(['routePort', 'vessel.accommodations'])
            ->where('voyage_status', 'Scheduled')
            ->orderBy('voyage_departure_date', 'asc')
            ->get();

        // Get vessel cot plan for first voyage if available
        $selectedVoyage = $voyages->first();
        $cotPlanUrl = $selectedVoyage && $selectedVoyage->vessel && $selectedVoyage->vessel->vessel_cot_plan_url
            ? asset('storage/' . $selectedVoyage->vessel->vessel_cot_plan_url)
            : asset('images/sample-cot-plan.jpg');

        return view('authorized.staff.passengerbooking', compact('voyages', 'cotPlanUrl'));
    }

    /**
     * Store new passenger booking from staff
     */
    public function store(Request $request)
    {
        $request->validate([
            'voyage_id' => 'required|exists:voyage,voyage_id',
            'payment_mode' => 'required|in:Physical,GCash',
            'passengers' => 'required|array|min:1',
            'passengers.*.first_name' => 'required|string|max:255',
            'passengers.*.last_name' => 'required|string|max:255',
            'passengers.*.age' => 'required|integer|min:0',
            'passengers.*.gender' => 'required|in:Male,Female',
            'passengers.*.type' => 'required|in:Regular,Student,Senior Citizen,PWD',
            'passengers.*.accommodation_id' => 'required|exists:accommodation,accommodation_id',
            'passengers.*.cot_number' => 'required|integer',
            'passengers.*.province' => 'required|string',
            'passengers.*.city' => 'required|string',
            'passengers.*.barangay' => 'required|string',
            'passengers.*.contact_number' => 'required|string',
            'passengers.*.email' => 'nullable|email',
        ]);

        $voyage = Voyage::findOrFail($request->voyage_id);
        $passengers = $request->passengers;
        $paymentMode = $request->payment_mode;

        // Validate cot availability
        foreach ($passengers as $passengerData) {
            $cotBooked = DB::table('passenger_ticket')
                ->join('booking', 'passenger_ticket.booking_ref_no', '=', 'booking.booking_ref_no')
                ->where('passenger_ticket.voyage_id', $voyage->voyage_id)
                ->where('passenger_ticket.pt_cot_no', $passengerData['cot_number'])
                ->whereRaw("LOWER(booking.booking_status) <> ?", ['canceled'])
                ->exists();

            if ($cotBooked) {
                return back()->withErrors(['error' => "Cot {$passengerData['cot_number']} is already booked."])->withInput();
            }
        }

        DB::beginTransaction();
        try {
            // Create booking
            $booking = Booking::create([
                'booking_type' => 'passenger',
                'booking_status' => $paymentMode === 'Physical' ? 'Confirmed' : 'Pending',
                'voyage_id' => $voyage->voyage_id,
            ]);

            $totalAmount = 0;
            $firstPassengerEmail = null;

            // Create passengers and tickets
            foreach ($passengers as $index => $passengerData) {
                // Create passenger
                $passenger = Passenger::create([
                    'passenger_firstname' => $passengerData['first_name'],
                    'passenger_midinitial' => $passengerData['middle_initial'] ?? null,
                    'passenger_lastname' => $passengerData['last_name'],
                    'passenger_suffix' => $passengerData['suffix'] ?? null,
                    'passenger_age' => $passengerData['age'],
                    'passenger_gender' => strtoupper(substr($passengerData['gender'], 0, 1)),
                    'passenger_type' => $passengerData['type'],
                    'passenger_address' => "{$passengerData['barangay']}, {$passengerData['city']}, {$passengerData['province']}",
                    'passenger_contactno' => $passengerData['contact_number'],
                    'passenger_email' => $passengerData['email'] ?? null,
                ]);

                // Store first passenger's email for ticket email
                if ($index === 0 && !empty($passengerData['email'])) {
                    $firstPassengerEmail = $passengerData['email'];
                }

                // Get accommodation price
                $accommodation = Accommodation::find($passengerData['accommodation_id']);
                $basePrice = $accommodation->accommodation_regular_price ?? 500;

                // Calculate price with discounts
                $price = $this->calculateDiscountedPrice(
                    $basePrice,
                    $passengerData['type'],
                    $voyage->routePort->route_origin ?? '',
                    $voyage->routePort->route_destination ?? ''
                );

                $totalAmount += $price;

                // Create passenger ticket
                PassengerTicket::create([
                    'booking_ref_no' => $booking->booking_ref_no,
                    'passenger_id' => $passenger->passenger_id,
                    'voyage_id' => $voyage->voyage_id,
                    'pt_cot_no' => $passengerData['cot_number'],
                    'pt_ticket_price' => $price,
                    'pt_valid_until' => $voyage->voyage_departure_date,
                ]);
            }

            // Create payment
            Payment::create([
                'booking_ref_no' => $booking->booking_ref_no,
                'mode_of_payment' => $paymentMode === 'Physical' ? 'Cash' : 'Gcash',
                'payment_date' => $paymentMode === 'Physical' ? now() : null,
                'total_amount' => $totalAmount,
                'payment_status' => $paymentMode === 'Physical' ? 'Completed' : 'Pending',
            ]);

            // Note: Notification is not created for passenger bookings as the schema only supports cargo notifications

            DB::commit();

            // Send ticket email if payment is completed and email is provided
            if ($paymentMode === 'Physical' && $firstPassengerEmail) {
                SendTicketEmail::dispatch($booking->booking_ref_no, $firstPassengerEmail);
            }

            $message = $paymentMode === 'Physical' 
                ? "Booking confirmed! Reference: {$booking->booking_ref_no}. Payment received via cash." . ($firstPassengerEmail ? " Ticket email sent to {$firstPassengerEmail}." : "")
                : "Booking created! Reference: {$booking->booking_ref_no}. Awaiting GCash payment.";

            return redirect()->route('staff.dashboard')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create booking: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Calculate discounted price based on passenger type and route
     */
    private function calculateDiscountedPrice($basePrice, $passengerType, $routeFrom, $routeTo)
    {
        $isBoholCebuRoute = (
            (stripos($routeFrom, 'bohol') !== false && stripos($routeTo, 'cebu') !== false) ||
            (stripos($routeFrom, 'cebu') !== false && stripos($routeTo, 'bohol') !== false)
        );

        if (!$isBoholCebuRoute) {
            return $basePrice;
        }

        $discount = 0;
        switch ($passengerType) {
            case 'Senior Citizen':
            case 'PWD':
                $discount = 0.20;
                break;
            case 'Student':
                $discount = 0.20;
                break;
        }

        return $basePrice * (1 - $discount);
    }

    /**
     * Get available cots by accommodation for a voyage (AJAX endpoint)
     */
    public function getAvailableCots(Request $request)
    {
        $voyageId = $request->query('voyage_id');

        if (!$voyageId) {
            return response()->json(['success' => false, 'message' => 'Missing voyage_id'], 422);
        }

        $voyage = Voyage::with('vessel.accommodations')->find($voyageId);

        if (!$voyage) {
            return response()->json(['success' => false, 'message' => 'Voyage not found'], 404);
        }

        // Get booked cots
        $bookedCots = DB::table('passenger_ticket')
            ->join('booking', 'passenger_ticket.booking_ref_no', '=', 'booking.booking_ref_no')
            ->where('passenger_ticket.voyage_id', $voyageId)
            ->whereRaw("LOWER(booking.booking_status) <> ?", ['canceled'])
            ->pluck('passenger_ticket.pt_cot_no')
            ->map(fn($cot) => (int) $cot)
            ->unique()
            ->values()
            ->all();

        $result = [];
        foreach ($voyage->vessel->accommodations as $accommodation) {
            $cotRange = $accommodation->accommodation_cot_range;
            $availableCots = [];

            if ($cotRange) {
                $ranges = array_map('trim', explode(',', $cotRange));

                foreach ($ranges as $range) {
                    if (strpos($range, '-') !== false) {
                        list($start, $end) = explode('-', $range);
                        $start = (int) trim($start);
                        $end = (int) trim($end);

                        for ($i = $start; $i <= $end; $i++) {
                            if (!in_array($i, $bookedCots)) {
                                $availableCots[] = $i;
                            }
                        }
                    }
                }

                sort($availableCots);
            }

            $result[] = [
                'accommodation_id' => $accommodation->accommodation_id,
                'accommodation_name' => $accommodation->accommodation_name,
                'accommodation_price' => $accommodation->accommodation_regular_price,
                'cot_range' => $cotRange,
                'available_cots' => $availableCots
            ];
        }

        return response()->json(['success' => true, 'accommodations' => $result]);
    }
}
