<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Jobs\CancelBookingHold;
use Carbon\Carbon;

class BookingController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->all();

        // Expecting passengers array and voyage info
        $passengers = $data['passengers'] ?? [];
        $routeFrom = $data['route_from'] ?? null;
        $routeTo = $data['route_to'] ?? null;
        $departureDate = $data['departure_date'] ?? null;
        $voyageId = $data['voyage_id'] ?? null;

        if (empty($passengers) || !$routeFrom || !$routeTo || !$departureDate) {
            return response()->json(['success' => false, 'message' => 'Missing booking data.'], 422);
        }

        // Find voyage using voyage_id if provided, otherwise find by route_port and date
        $voyage = null;
        if ($voyageId) {
            $voyage = DB::table('voyage')->where('voyage_id', $voyageId)->first();
        }

        if (!$voyage) {
            // Find route_port: map route origin/destination to route_port_id then voyage
            $routePort = DB::table('route_port')
                ->where('route_origin', $routeFrom)
                ->where('route_destination', $routeTo)
                ->first();

            if (!$routePort) {
                return response()->json(['success' => false, 'message' => 'No matching route found.'], 422);
            }

            $voyage = DB::table('voyage')
                ->where('route_port_id', $routePort->route_port_id)
                ->whereDate('voyage_departure_date', $departureDate)
                ->first();
        }

        if (!$voyage) {
            return response()->json(['success' => false, 'message' => 'No voyage found for selected date. Please contact administrator.'], 422);
        }

        // Simple pricing: attempt to use accommodation price; fallback to flat price
        $flatPrice = 500.00;

        // Server-side validation: ensure cot numbers selected are unique within this booking
        $selectedCots = array_map(function ($p) {
            return isset($p['cot_number']) ? (int) $p['cot_number'] : null;
        }, $passengers);
        $cotCounts = array_count_values($selectedCots);
        foreach ($cotCounts as $cot => $count) {
            if ($cot !== null && $count > 1) {
                return response()->json(['success' => false, 'message' => "Duplicate cot selection detected: cot {$cot} selected {$count} times."], 422);
            }
        }

        // Server-side validation: ensure selected cots are not already booked for this voyage
        foreach ($selectedCots as $cot) {
            if ($cot === null)
                continue;
            // Check passenger_ticket entries for this voyage and cot where booking is not canceled
            $exists = DB::table('passenger_ticket')
                ->join('booking', 'passenger_ticket.booking_ref_no', '=', 'booking.booking_ref_no')
                ->where('passenger_ticket.voyage_id', $voyage->voyage_id)
                ->where('passenger_ticket.pt_cot_no', $cot)
                ->whereRaw("LOWER(booking.booking_status) <> ?", ['canceled'])
                ->exists();
            if ($exists) {
                return response()->json(['success' => false, 'message' => "Cot {$cot} is already booked for this voyage. Please select another cot."], 422);
            }
        }

        DB::beginTransaction();
        try {
            // Create booking (auto-increment id booking_ref_no)
            $bookingId = DB::table('booking')->insertGetId([
                'booking_date' => now(),
                'booking_status' => 'Pending',
                'booking_type' => 'passenger',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $totalAmount = 0;
            $passengerTicketIds = [];

            // Create payment placeholder (Pending)
            $paymentId = DB::table('payment')->insertGetId([
                'booking_ref_no' => $bookingId,
                'mode_of_payment' => 'Gcash',
                'payment_date' => now(),
                'total_amount' => 0, // update after calculating
                'payment_status' => 'Pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($passengers as $p) {
                // Insert or create passenger record
                $passengerId = DB::table('passenger')->insertGetId([
                    'passenger_firstname' => $p['first_name'] ?? null,
                    'passenger_midinitial' => $p['middle_initial'] ?? null,
                    'passenger_lastname' => $p['last_name'] ?? null,
                    'passenger_suffix' => $p['suffix'] ?? null,
                    'passenger_age' => $p['age'] ?? 0,
                    'passenger_gender' => strtoupper(substr($p['gender'] ?? 'M', 0, 1)),
                    'passenger_type' => $p['type'] ?? 'Regular', // Use the new enum values directly
                    'passenger_address' => $p['address'] ?? null,
                    'passenger_contactno' => $p['contact_number'] ?? null,
                    'passenger_email' => $p['email'] ?? null,
                    'passenger_idnumber' => $p['id_number'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Determine base price from accommodation
                $basePrice = $flatPrice;
                try {
                    $accom = DB::table('accommodation')
                        ->where('vessel_id', $voyage->vessel_id)
                        ->where('accommodation_name', $p['accommodation_type'])
                        ->first();
                    if ($accom && isset($accom->accommodation_regular_price)) {
                        $basePrice = (float) $accom->accommodation_regular_price;
                    }
                } catch (\Exception $e) {
                    // ignore and use flat price
                }

                // Apply discounts based on passenger type and route
                $price = $this->calculateDiscountedPrice($basePrice, $p['type'] ?? 'Regular', $routeFrom, $routeTo);

                $totalAmount += $price;

                // Create passenger_ticket with pt_valid_until_ts = now + 5 minutes
                $validUntil = Carbon::now()->addMinutes(5);
                $ptId = DB::table('passenger_ticket')->insertGetId([
                    'passenger_id' => $passengerId,
                    'voyage_id' => $voyage->voyage_id,
                    'promo_id' => null,
                    'payment_id' => $paymentId,
                    'booking_ref_no' => $bookingId,
                    // keep legacy date column (pt_valid_until) as date for backwards compat
                    'pt_valid_until' => $validUntil->toDateString(),
                    'pt_valid_until_ts' => $validUntil->toDateTimeString(),
                    'pt_cot_no' => (int) ($p['cot_number'] ?? 0),
                    'pt_ticket_price' => $price,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $passengerTicketIds[] = $ptId;
            }

            // Update payment total amount
            DB::table('payment')->where('payment_id', $paymentId)->update([
                'total_amount' => $totalAmount,
                'updated_at' => now(),
            ]);

            DB::commit();

            // Dispatch cancel job (delayed 5 minutes) to auto-cancel if unpaid
            CancelBookingHold::dispatch($bookingId)->delay(now()->addMinutes(5));

            // Return booking ref and redirect URL
            return response()->json([
                'success' => true,
                'booking_ref_no' => $bookingId,
                'redirect_url' => url("/passenger/confirmbooking/{$bookingId}"),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Booking store failed: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error while creating booking.'], 500);
        }
    }

    /**
     * Show confirm booking page
     */
    public function confirm($bookingRef)
    {
        // Load booking, payment, tickets and passengers
        $booking = DB::table('booking')->where('booking_ref_no', $bookingRef)->first();
        if (!$booking) {
            abort(404, 'Booking not found');
        }

        $payment = DB::table('payment')->where('booking_ref_no', $bookingRef)->first();

        $tickets = DB::table('passenger_ticket')
            ->where('booking_ref_no', $bookingRef)
            ->get();

        // join passenger data
        $passengers = [];
        foreach ($tickets as $t) {
            $p = DB::table('passenger')->where('passenger_id', $t->passenger_id)->first();
            $passengers[] = [
                'ticket' => $t,
                'passenger' => $p,
            ];
        }

        // Compute earliest pt_valid_until_ts across tickets and send epoch ms to the view
        $validUntilMs = null;
        foreach ($tickets as $t) {
            if (!empty($t->pt_valid_until_ts)) {
                try {
                    $dt = Carbon::parse($t->pt_valid_until_ts);
                    $ms = $dt->timestamp * 1000;
                    if (is_null($validUntilMs) || $ms < $validUntilMs)
                        $validUntilMs = $ms;
                } catch (\Exception $e) {
                    // ignore parse errors
                }
            }
        }

        return view('passenger.confirmbooking', compact('booking', 'payment', 'passengers', 'validUntilMs'));
    }

    /**
     * Return unavailable cot numbers for a voyage.
     * Accepts either voyage_id or route_from, route_to, departure_date.
     */
    public function unavailableCots(Request $request)
    {
        $voyageId = $request->query('voyage_id');
        if (!$voyageId) {
            $routeFrom = $request->query('route_from');
            $routeTo = $request->query('route_to');
            $departureDate = $request->query('departure_date');
            if (!$routeFrom || !$routeTo || !$departureDate) {
                return response()->json(['success' => false, 'message' => 'Missing parameters'], 422);
            }

            $routePort = DB::table('route_port')
                ->where('route_origin', $routeFrom)
                ->where('route_destination', $routeTo)
                ->first();

            if (!$routePort)
                return response()->json(['success' => true, 'unavailable' => []]);

            $voyage = DB::table('voyage')
                ->where('route_port_id', $routePort->route_port_id)
                ->whereDate('voyage_departure_date', $departureDate)
                ->first();

            if (!$voyage)
                return response()->json(['success' => true, 'unavailable' => []]);

            $voyageId = $voyage->voyage_id;
        }

        // Find passenger_ticket rows for this voyage where associated booking is not canceled
        $rows = DB::table('passenger_ticket')
            ->join('booking', 'passenger_ticket.booking_ref_no', '=', 'booking.booking_ref_no')
            ->where('passenger_ticket.voyage_id', $voyageId)
            ->whereRaw("LOWER(booking.booking_status) <> ?", ['canceled'])
            ->select('passenger_ticket.pt_cot_no')
            ->get();

        $cots = $rows->pluck('pt_cot_no')->unique()->values()->all();

        return response()->json(['success' => true, 'unavailable' => $cots]);
    }

    /**
     * Get available cots per accommodation for a specific voyage
     */
    public function getAvailableCotsByAccommodation(Request $request)
    {
        $voyageId = $request->query('voyage_id');

        if (!$voyageId) {
            return response()->json(['success' => false, 'message' => 'Missing voyage_id'], 422);
        }

        // Get the voyage with its vessel and accommodations
        $voyage = DB::table('voyage')
            ->where('voyage_id', $voyageId)
            ->first();

        if (!$voyage) {
            return response()->json(['success' => false, 'message' => 'Voyage not found'], 404);
        }

        // Get accommodations for this vessel with their cot ranges
        $accommodations = DB::table('accommodation')
            ->where('vessel_id', $voyage->vessel_id)
            ->get();

        // Get all booked cots for this voyage (exclude canceled bookings)
        $bookedCots = DB::table('passenger_ticket')
            ->join('booking', 'passenger_ticket.booking_ref_no', '=', 'booking.booking_ref_no')
            ->where('passenger_ticket.voyage_id', $voyageId)
            ->whereRaw("LOWER(booking.booking_status) <> ?", ['canceled'])
            ->pluck('passenger_ticket.pt_cot_no')
            ->map(function ($cot) {
                return (int) $cot;
            })
            ->unique()
            ->values()
            ->all();

        $result = [];
        foreach ($accommodations as $accommodation) {
            // Parse cot range (e.g., "1-50" or "51-100")
            $cotRange = $accommodation->accommodation_cot_range;
            $availableCots = [];

            if ($cotRange && strpos($cotRange, '-') !== false) {
                list($start, $end) = explode('-', $cotRange);
                $start = (int) trim($start);
                $end = (int) trim($end);

                // Generate all cots in range, excluding booked ones
                for ($i = $start; $i <= $end; $i++) {
                    if (!in_array($i, $bookedCots)) {
                        $availableCots[] = $i;
                    }
                }
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
     * Cancel a booking immediately
     */
    public function cancel($bookingRef)
    {
        try {
            DB::transaction(function () use ($bookingRef) {
                $booking = DB::table('booking')->where('booking_ref_no', $bookingRef)->first();
                if (!$booking) {
                    throw new \Exception('Booking not found');
                }

                // Check if already confirmed - cannot cancel confirmed bookings
                if (strtolower($booking->booking_status) === 'confirmed') {
                    throw new \Exception('Cannot cancel a confirmed booking');
                }

                // Check if already canceled
                if (strtolower($booking->booking_status) === 'canceled') {
                    throw new \Exception('Booking is already canceled');
                }

                $payment = DB::table('payment')->where('booking_ref_no', $bookingRef)->first();

                // Check if payment is completed - cannot cancel if payment is completed
                if ($payment && strtolower($payment->payment_status) === 'completed') {
                    throw new \Exception('Cannot cancel booking with completed payment');
                }

                // Cancel the booking
                DB::table('booking')->where('booking_ref_no', $bookingRef)->update([
                    'booking_status' => 'Canceled',
                    'updated_at' => now(),
                ]);

                // Cancel the payment if exists
                if ($payment) {
                    DB::table('payment')->where('payment_id', $payment->payment_id)->update([
                        'payment_status' => 'Canceled',
                        'updated_at' => now(),
                    ]);
                }

                // Free up the reserved tickets
                DB::table('passenger_ticket')->where('booking_ref_no', $bookingRef)->update([
                    'booking_ref_no' => null,
                    'payment_id' => null,
                    'pt_valid_until_ts' => null,
                    'updated_at' => now(),
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Booking canceled successfully',
                'redirect_url' => route('bookingtype')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
