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
use App\Helpers\CotPlanHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Traits\StaffGuard;

class StaffPassengerController extends Controller
{
    use StaffGuard;

    public function __construct()
    {
        $this->ensureStaff();
    }

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
            'payment_mode' => 'required|in:Physical,GCash,Pending',
            'passengers' => 'required|array|min:1',
            'passengers.*.first_name' => 'required|string|max:255',
            'passengers.*.last_name' => 'required|string|max:255',
            'passengers.*.age' => 'required|integer|min:0',
            'passengers.*.gender' => 'required|in:Male,Female',
            'passengers.*.type' => 'required|in:Regular,Student,Senior Citizen,PWD,Uniformed Personnel,3 to 11 years old,Below 3 years old',
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
            ]);

            $totalAmount = 0;
            $firstPassengerEmail = null;
            $passengerDetails = [];

            // Create payment first (we'll update total_amount later)
            $payment = Payment::create([
                'booking_ref_no' => $booking->booking_ref_no,
                'mode_of_payment' => $paymentMode === 'Pending' ? 'Cash' : ($paymentMode === 'Physical' ? 'Cash' : 'Gcash'),
                'payment_date' => $paymentMode === 'Physical' ? now() : null,
                'total_amount' => 0, // Will update after calculating
                'payment_status' => $paymentMode === 'Physical' ? 'Completed' : 'Pending',
            ]);

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

                // Store passenger details for response
                $passengerDetails[] = [
                    'type' => $passengerData['type'],
                    'price' => $price,
                    'name' => "{$passengerData['first_name']} {$passengerData['last_name']}"
                ];

                // Create passenger ticket with payment_id
                $validUntil = \Carbon\Carbon::now()->addMinutes(5);
                PassengerTicket::create([
                    'booking_ref_no' => $booking->booking_ref_no,
                    'passenger_id' => $passenger->passenger_id,
                    'voyage_id' => $voyage->voyage_id,
                    'payment_id' => $payment->payment_id,
                    'pt_cot_no' => $passengerData['cot_number'],
                    'pt_ticket_price' => $price,
                    'pt_valid_until' => $validUntil->toDateString(),
                    'pt_valid_until_ts' => $validUntil->toDateTimeString(),
                ]);
            }

            // Update payment with total amount
            $payment->update(['total_amount' => $totalAmount]);

            // Note: Notification is not created for passenger bookings as the schema only supports cargo notifications

            DB::commit();

            // Send ticket emails to all unique passenger emails if payment is Physical (Cash)
            if ($paymentMode === 'Physical') {
                $uniqueEmails = collect($passengers)
                    ->pluck('email')
                    ->filter()
                    ->unique()
                    ->values();

                foreach ($uniqueEmails as $email) {
                    SendTicketEmail::dispatch($booking->booking_ref_no, $email);
                }
            }

            $message = $paymentMode === 'Physical'
                ? "Booking confirmed! Reference: {$booking->booking_ref_no}. Payment received via cash. Ticket emails sent to all passengers."
                : "Reservation created! Reference: {$booking->booking_ref_no}. Complete payment to confirm booking.";

            // Redirect to reservation page for Pending payment mode
            if ($paymentMode === 'Pending') {
                return redirect()->route('staff.passenger_booking.reservation', $booking->booking_ref_no);
            }

            return redirect()->route('staff.dashboard')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();

            // If booking was created but error occurred, try to cancel it
            if (isset($booking) && $booking->booking_ref_no) {
                try {
                    $booking->update(['booking_status' => 'Canceled']);
                } catch (\Exception $cancelError) {
                    // Ignore cancel error, focus on original error
                }
            }

            return back()->withErrors(['error' => 'Failed to create booking: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Show reservation page with timer
     */
    public function showReservation($bookingRef)
    {
        $booking = Booking::where('booking_ref_no', $bookingRef)
            ->first();

        if (!$booking) {
            return redirect()->route('staff.passenger_booking.create')
                ->withErrors(['error' => 'Reservation not found.']);
        }

        // If booking is already canceled or confirmed, redirect
        if ($booking->booking_status !== 'Pending') {
            return redirect()->route('staff.passenger_booking.create');
        }

        // Prevent browser caching of this page
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Get passenger details and calculate total
        $passengerTickets = DB::table('passenger_ticket')
            ->join('passenger', 'passenger_ticket.passenger_id', '=', 'passenger.passenger_id')
            ->where('passenger_ticket.booking_ref_no', $bookingRef)
            ->select(
                'passenger.passenger_firstname',
                'passenger.passenger_lastname',
                'passenger.passenger_type',
                'passenger_ticket.pt_ticket_price'
            )
            ->get();

        $passengers = [];
        $totalAmount = 0;

        foreach ($passengerTickets as $ticket) {
            $passengers[] = [
                'name' => $ticket->passenger_firstname . ' ' . $ticket->passenger_lastname,
                'type' => $ticket->passenger_type,
                'price' => $ticket->pt_ticket_price
            ];
            $totalAmount += $ticket->pt_ticket_price;
        }

        $bookingRefNo = $bookingRef;

        return view('authorized.staff.reservation', compact('bookingRefNo', 'passengers', 'totalAmount', 'booking'));
    }

    /**
     * Calculate discounted price based on passenger type and route
     */
    private function calculateDiscountedPrice($basePrice, $passengerType, $routeFrom, $routeTo)
    {
        // Check if route is Bohol-Cebu or Cebu-Bohol (case insensitive)
        $isBoholCebuRoute = (
            (stripos($routeFrom, 'bohol') !== false && stripos($routeTo, 'cebu') !== false) ||
            (stripos($routeFrom, 'cebu') !== false && stripos($routeTo, 'bohol') !== false)
        );

        switch ($passengerType) {
            case 'Regular':
                return $basePrice; // No discount

            case 'Student':
            case 'Uniformed Personnel':
                return $basePrice * 0.80; // 20% discount

            case 'Senior Citizen':
            case 'PWD':
                return $basePrice * 0.80; // 20% discount

            case '3 to 11 years old':
                return $basePrice * 0.50; // Half fare

            case 'Below 3 years old':
                if ($isBoholCebuRoute) {
                    return 0; // Free for Bohol-Cebu/Cebu-Bohol routes
                }
                return $basePrice * 0.75; // 25% discount for other routes

            default:
                return $basePrice; // Default to regular price
        }
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

        // Get vessel COT plan for bunk type information
        $vesselPlan = CotPlanHelper::getVesselPlan($voyage->vessel_id);

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
                                // Determine bunk type
                                $bunkType = $this->determineBunkType($vesselPlan, $accommodation->accommodation_id, $i);
                                $availableCots[] = [
                                    'number' => $i,
                                    'bunk_type' => $bunkType
                                ];
                            }
                        }
                    }
                }

                usort($availableCots, function ($a, $b) {
                    return $a['number'] - $b['number'];
                });
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

    /**
     * Determine if a COT number is a lower or upper bunk
     */
    private function determineBunkType($vesselPlan, $accommodationId, $cotNumber)
    {
        if (!$vesselPlan || !isset($vesselPlan['accommodations'])) {
            return null;
        }

        foreach ($vesselPlan['accommodations'] as $acc) {
            if ($acc['accommodation_id'] == $accommodationId) {
                // Expand "all" keyword to actual COT numbers
                $lowerBunks = $this->expandBunkArray($acc['lower_bunks'] ?? [], $acc['cot_range'] ?? '');
                $upperBunks = $this->expandBunkArray($acc['upper_bunks'] ?? [], $acc['cot_range'] ?? '');

                if (in_array($cotNumber, $lowerBunks)) {
                    return 'lower';
                }
                if (in_array($cotNumber, $upperBunks)) {
                    return 'upper';
                }
                break;
            }
        }

        return null;
    }

    /**
     * Expand bunk array, handling the special "all" keyword
     * If "all" is in the array, expands to all COT numbers in the range
     */
    private function expandBunkArray($bunkArray, $cotRange)
    {
        // Check if "all" or ["all"] is in the array
        if (in_array('all', $bunkArray) || in_array('[all]', $bunkArray)) {
            // Parse cot range and return all COT numbers
            $allCots = [];
            if ($cotRange) {
                $ranges = array_map('trim', explode(',', $cotRange));
                foreach ($ranges as $range) {
                    if (strpos($range, '-') !== false) {
                        list($start, $end) = explode('-', $range);
                        $start = (int) trim($start);
                        $end = (int) trim($end);
                        for ($i = $start; $i <= $end; $i++) {
                            $allCots[] = $i;
                        }
                    } else {
                        $allCots[] = (int) trim($range);
                    }
                }
            }
            return $allCots;
        }

        // Otherwise, return the array with values converted to integers
        return array_map(fn($val) => (int) $val, $bunkArray);
    }

    /**
     * Cancel reservation
     */
    public function cancelReservation($bookingRef)
    {
        try {
            $booking = Booking::where('booking_ref_no', $bookingRef)->first();

            if (!$booking) {
                return redirect()->route('staff.passenger_booking.create')
                    ->withErrors(['error' => 'Booking not found']);
            }

            $booking->update(['booking_status' => 'Canceled']);

            // Also cancel the payment
            Payment::where('booking_ref_no', $bookingRef)
                ->update(['payment_status' => 'Canceled']);

            return redirect()->route('staff.passenger_booking.create')
                ->with('success', 'Reservation cancelled successfully.');
        } catch (\Exception $e) {
            return redirect()->route('staff.passenger_booking.create')
                ->withErrors(['error' => 'Error cancelling reservation: ' . $e->getMessage()]);
        }
    }

    /**
     * Complete booking with Cash payment
     */
    public function completeCash($bookingRef)
    {
        try {
            DB::beginTransaction();

            $booking = Booking::where('booking_ref_no', $bookingRef)->first();

            if (!$booking) {
                return redirect()->route('staff.passenger_booking.create')
                    ->withErrors(['error' => 'Booking not found']);
            }

            // Check if booking is still pending
            if ($booking->booking_status !== 'Pending') {
                DB::rollBack();
                return redirect()->route('staff.passenger_booking.create');
            }

            // Update booking status
            $booking->update(['booking_status' => 'Confirmed']);

            // Update payment
            $payment = Payment::where('booking_ref_no', $bookingRef)->first();
            if ($payment) {
                $payment->update([
                    'mode_of_payment' => 'Cash',
                    'payment_status' => 'Completed',
                    'payment_date' => now()
                ]);
            }

            // Get all unique passenger emails
            $passengerEmails = DB::table('passenger')
                ->join('passenger_ticket', 'passenger.passenger_id', '=', 'passenger_ticket.passenger_id')
                ->where('passenger_ticket.booking_ref_no', $bookingRef)
                ->whereNotNull('passenger.passenger_email')
                ->where('passenger.passenger_email', '!=', '')
                ->select('passenger.passenger_email')
                ->distinct()
                ->pluck('passenger_email');

            // Send ticket email to all unique emails
            foreach ($passengerEmails as $email) {
                SendTicketEmail::dispatch($bookingRef, $email);
            }

            DB::commit();

            $message = "Booking #{$bookingRef} confirmed! Cash payment received." .
                ($passengerEmails->count() > 0 ? " Ticket emails sent to all passengers." : "");

            return redirect()->route('staff.dashboard')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('staff.passenger_booking.reservation', $bookingRef)
                ->withErrors(['error' => 'Error completing booking: ' . $e->getMessage()]);
        }
    }

    /**
     * Complete booking with GCash payment
     */
    public function completeGcash($bookingRef)
    {
        try {
            DB::beginTransaction();

            $booking = Booking::where('booking_ref_no', $bookingRef)->first();

            if (!$booking) {
                return redirect()->route('staff.passenger_booking.create')
                    ->withErrors(['error' => 'Booking not found']);
            }

            // Check if booking is still pending
            if ($booking->booking_status !== 'Pending') {
                DB::rollBack();
                return redirect()->route('staff.passenger_booking.create');
            }

            // Update booking status
            $booking->update(['booking_status' => 'Confirmed']);

            // Update payment
            $payment = Payment::where('booking_ref_no', $bookingRef)->first();
            if ($payment) {
                $payment->update([
                    'mode_of_payment' => 'Gcash',
                    'payment_status' => 'Completed',
                    'payment_date' => now()
                ]);
            }

            // Get all unique passenger emails
            $passengerEmails = DB::table('passenger')
                ->join('passenger_ticket', 'passenger.passenger_id', '=', 'passenger_ticket.passenger_id')
                ->where('passenger_ticket.booking_ref_no', $bookingRef)
                ->whereNotNull('passenger.passenger_email')
                ->where('passenger.passenger_email', '!=', '')
                ->select('passenger.passenger_email')
                ->distinct()
                ->pluck('passenger_email');

            // Send ticket email to all unique emails
            foreach ($passengerEmails as $email) {
                SendTicketEmail::dispatch($bookingRef, $email);
            }

            DB::commit();

            $message = "Booking #{$bookingRef} confirmed! GCash payment received." .
                ($passengerEmails->count() > 0 ? " Ticket emails sent to all passengers." : "");

            return redirect()->route('staff.dashboard')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('staff.passenger_booking.reservation', $bookingRef)
                ->withErrors(['error' => 'Error completing booking: ' . $e->getMessage()]);
        }
    }
}
