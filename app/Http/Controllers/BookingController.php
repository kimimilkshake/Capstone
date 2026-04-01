<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Jobs\CancelBookingHold;
use App\Jobs\SendTicketEmail;
use App\Models\Promo;
use App\Models\PassengerTicket;
use App\Models\Passenger;
use App\Helpers\CotPlanHelper;
use App\Services\TicketCopyService;
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
        $promoId = $data['promo_id'] ?? null;
        $promoDiscountRate = $data['promo_discount_rate'] ?? 0;

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

        // Fetch route_rate and route_category_id from voyage → route_port → route_category
        $rcRow = DB::table('voyage')
            ->join('route_port', 'voyage.route_port_id', '=', 'route_port.route_port_id')
            ->join('route_category', 'route_port.route_category_id', '=', 'route_category.route_category_id')
            ->where('voyage.voyage_id', $voyage->voyage_id)
            ->select('route_category.route_rate', 'route_port.route_category_id')
            ->first();
        $routeRate = $rcRow->route_rate ?? 0;
        $routeCategoryId = $rcRow->route_category_id ?? null;

        // Simple pricing: attempt to use accommodation price; fallback to flat price
        $flatPrice = 500.00;

        DB::beginTransaction();
        try {
            // Server-side validation: ensure cot numbers selected are unique within this booking
            $selectedCots = array_map(function ($p) {
                return isset($p['cot_number']) && $p['cot_number'] !== '' ? (int) $p['cot_number'] : null;
            }, $passengers);
            // Filter nulls before array_count_values (PHP 8+ throws ValueError on null values)
            $nonNullCots = array_filter($selectedCots, fn($c) => $c !== null);
            $cotCounts = array_count_values($nonNullCots);
            foreach ($cotCounts as $cot => $count) {
                if ($count > 1) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => "Duplicate cot selection detected: cot {$cot} selected {$count} times."], 422);
                }
            }

            // Server-side validation: ensure selected cots are not already booked for this voyage
            foreach ($selectedCots as $cot) {
                if ($cot === null)
                    continue;
                $exists = DB::table('passenger_ticket')
                    ->join('booking', 'passenger_ticket.booking_ref_no', '=', 'booking.booking_ref_no')
                    ->where('passenger_ticket.voyage_id', $voyage->voyage_id)
                    ->where('passenger_ticket.pt_cot_no', $cot)
                    ->whereRaw("LOWER(booking.booking_status) <> ?", ['canceled'])
                    ->exists();
                if ($exists) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => "Cot {$cot} is already booked for this voyage. Please select another cot."], 422);
                }
            }
            // Create booking (auto-increment id booking_ref_no)
            $bookingId = DB::table('booking')->insertGetId([
                'voyage_id' => $voyage->voyage_id,
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

            foreach ($passengers as $index => $p) {
                // Insert or create passenger record
                $passengerId = DB::table('passenger')->insertGetId([
                    'passenger_firstname' => $p['first_name'] ?? null,
                    'passenger_midinitial' => $p['middle_initial'] ?? null,
                    'passenger_lastname' => $p['last_name'] ?? null,
                    'passenger_suffix' => $p['suffix'] ?? null,
                    'passenger_age' => $p['age'] ?? 0,
                    'passenger_gender' => strtoupper(substr($p['gender'] ?? 'M', 0, 1)),
                    'passenger_type' => $p['type'] ?? 'Regular',
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

                // Apply route rate surcharge
                $basePrice = $basePrice * (1 + ($routeRate / 100));

                // Apply discounts based on passenger type and route
                $price = $this->calculateDiscountedPrice($basePrice, $p['type'] ?? 'Regular', $routeCategoryId);

                // Apply promo discount if applicable PER PASSENGER
                $passengerPromoId = $p['promo_id'] ?? null;
                $passengerPromoRate = $p['promo_discount_rate'] ?? 0;

                if ($passengerPromoId && $passengerPromoRate > 0) {
                    $promoDiscount = $price * ($passengerPromoRate / 100);
                    $price = $price - $promoDiscount;
                }

                $totalAmount += $price;

                // Create passenger_ticket with pt_valid_until_ts = now + 5 minutes
                $validUntil = Carbon::now()->addMinutes(5);
                $ptId = DB::table('passenger_ticket')->insertGetId([
                    'passenger_id' => $passengerId,
                    'voyage_id' => $voyage->voyage_id,
                    'promo_id' => $passengerPromoId,
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
            \Log::error('Booking store failed: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
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

        // If booking is already canceled or confirmed, redirect
        if ($booking->booking_status !== 'Pending') {
            return redirect()->route('bookingtype');
        }

        // Prevent browser caching of this page
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        $payment = DB::table('payment')->where('booking_ref_no', $bookingRef)->first();

        // Use model to load promo relationships
        $tickets = \App\Models\PassengerTicket::where('booking_ref_no', $bookingRef)
            ->with('promo')
            ->get();

        // Fetch route_rate and route_category_id for the booking's voyage
        $rcRow = DB::table('voyage')
            ->join('route_port', 'voyage.route_port_id', '=', 'route_port.route_port_id')
            ->join('route_category', 'route_port.route_category_id', '=', 'route_category.route_category_id')
            ->where('voyage.voyage_id', $booking->voyage_id)
            ->select('route_category.route_rate', 'route_port.route_category_id')
            ->first();
        $routeRate = $rcRow->route_rate ?? 0;
        $routeCategoryId = $rcRow->route_category_id ?? null;

        // Build passenger-type discount map for this route category
        $typeDiscounts = [];
        if ($routeCategoryId) {
            $rows = DB::table('route_category_passenger_discounts')
                ->where('route_category_id', $routeCategoryId)
                ->get();
            foreach ($rows as $r) {
                $typeDiscounts[$r->passenger_type] = (float) $r->discount_rate;
            }
        }

        // Fetch vessel accommodations to reverse-look base price per cot
        $voyageRow = DB::table('voyage')->where('voyage_id', $booking->voyage_id)->first();
        $accommodations = $voyageRow
            ? DB::table('accommodation')->where('vessel_id', $voyageRow->vessel_id)->get()
            : collect();

        // Helper: find accommodation by cot number
        $findAccom = function (int $cotNo) use ($accommodations) {
            foreach ($accommodations as $accom) {
                $ranges = array_map('trim', explode(',', $accom->accommodation_cot_range));
                foreach ($ranges as $range) {
                    if (strpos($range, '-') !== false) {
                        [$start, $end] = explode('-', $range);
                        if ($cotNo >= (int) $start && $cotNo <= (int) $end) {
                            return $accom;
                        }
                    }
                }
            }
            return null;
        };

        // join passenger data
        $passengers = [];
        foreach ($tickets as $t) {
            $p = \App\Models\Passenger::where('passenger_id', $t->passenger_id)->first();
            $accom = $findAccom((int) $t->pt_cot_no);
            $pType = $p ? ($p->passenger_type ?? 'Regular') : 'Regular';
            $passengers[] = [
                'ticket' => $t,
                'passenger' => $p,
                'accommodation_name' => $accom ? $accom->accommodation_name : null,
                'accommodation_base_price' => $accom ? (float) $accom->accommodation_regular_price : null,
                'route_rate' => (float) $routeRate,
                'type_discount_rate' => $typeDiscounts[$pType] ?? 0,
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

        // Get route_rate for this voyage
        $routeRate = DB::table('voyage')
            ->join('route_port', 'voyage.route_port_id', '=', 'route_port.route_port_id')
            ->join('route_category', 'route_port.route_category_id', '=', 'route_category.route_category_id')
            ->where('voyage.voyage_id', $voyageId)
            ->value('route_category.route_rate') ?? 0;

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

        // Get vessel COT plan for bunk type information
        $vesselPlan = CotPlanHelper::getVesselPlan($voyage->vessel_id);

        $result = [];
        foreach ($accommodations as $accommodation) {
            // Parse cot range (e.g., "1-50" or "1-50, 60-70" for comma-separated ranges)
            $cotRange = $accommodation->accommodation_cot_range;
            $availableCots = [];

            if ($cotRange) {
                // Split by comma to handle multiple ranges
                $ranges = array_map('trim', explode(',', $cotRange));

                foreach ($ranges as $range) {
                    if (strpos($range, '-') !== false) {
                        list($start, $end) = explode('-', $range);
                        $start = (int) trim($start);
                        $end = (int) trim($end);

                        // Generate all cots in range, excluding booked ones
                        for ($i = $start; $i <= $end; $i++) {
                            if (!in_array($i, $bookedCots)) {
                                // Determine bunk type
                                $bunkType = $this->determineBunkType($vesselPlan, $accommodation->accommodation_id, $i, $accommodation->accommodation_name);
                                $availableCots[] = [
                                    'number' => $i,
                                    'bunk_type' => $bunkType
                                ];
                            }
                        }
                    }
                }

                // Sort the available cots for better UX
                usort($availableCots, function ($a, $b) {
                    return $a['number'] - $b['number'];
                });
            }

            $adjustedPrice = round((float) $accommodation->accommodation_regular_price * (1 + ($routeRate / 100)), 2);

            $result[] = [
                'accommodation_id' => $accommodation->accommodation_id,
                'accommodation_name' => $accommodation->accommodation_name,
                'accommodation_price' => $adjustedPrice,
                'cot_range' => $cotRange,
                'cot_plan_url' => !empty($accommodation->accommodation_cot_plan_url)
                    ? asset('files/' . $accommodation->accommodation_cot_plan_url)
                    : null,
                'available_cots' => $availableCots
            ];
        }

        return response()->json(['success' => true, 'accommodations' => $result]);
    }

    /**
     * Determine if a COT number is a lower or upper bunk
     */
    private function determineBunkType($vesselPlan, $accommodationId, $cotNumber, $accommodationName = null)
    {
        if (!$vesselPlan || !isset($vesselPlan['accommodations'])) {
            return null;
        }

        foreach ($vesselPlan['accommodations'] as $acc) {
            // Match by name first (DB IDs may differ from JSON IDs after seeding)
            $matchesName = $accommodationName && isset($acc['accommodation_name'])
                && strtolower(trim($acc['accommodation_name'])) === strtolower(trim($accommodationName));
            $matchesId = $acc['accommodation_id'] == $accommodationId;

            if ($matchesName || $matchesId) {
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
     * Calculate discounted price using per-route-category DB discounts.
     */
    private function calculateDiscountedPrice($basePrice, $passengerType, $routeCategoryId)
    {
        if ($routeCategoryId) {
            $discount = DB::table('route_category_passenger_discounts')
                ->where('route_category_id', $routeCategoryId)
                ->where('passenger_type', $passengerType)
                ->value('discount_rate');

            if ($discount !== null) {
                return $basePrice * (1 - ($discount / 100));
            }
        }

        // No discount configured — regular price
        return $basePrice;
    }

    /**
     * Return passenger type discounts for a voyage's route category (public API)
     */
    public function voyagePassengerDiscounts($voyageId)
    {
        $routeCategoryId = DB::table('voyage')
            ->join('route_port', 'voyage.route_port_id', '=', 'route_port.route_port_id')
            ->where('voyage.voyage_id', $voyageId)
            ->value('route_port.route_category_id');

        if (!$routeCategoryId) {
            return response()->json([]);
        }

        $discounts = DB::table('route_category_passenger_discounts')
            ->where('route_category_id', $routeCategoryId)
            ->pluck('discount_rate', 'passenger_type');

        return response()->json($discounts);
    }

    /**
     * Validate promo code
     */
    public function validatePromo(Request $request)
    {
        try {
            $promoCode = strtoupper(trim($request->input('promo_code', '')));

            if (empty($promoCode)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Promo code is required'
                ]);
            }

            $promo = DB::table('promo')
                ->where('promo_code', $promoCode)
                ->where('promo_status', 'Active')
                ->first();

            if (!$promo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Promo code not found or inactive'
                ]);
            }

            // Check if promo is within active date range
            $today = Carbon::now()->toDateString();
            if ($today < $promo->promo_start_date || $today > $promo->promo_end_date) {
                return response()->json([
                    'success' => false,
                    'message' => 'Promo code has expired or not yet active'
                ]);
            }

            // Promo is valid
            return response()->json([
                'success' => true,
                'promo' => [
                    'promo_id' => $promo->promo_id,
                    'promo_code' => $promo->promo_code,
                    'promo_name' => $promo->promo_name,
                    'promo_description' => $promo->promo_description,
                    'promo_discount_rate' => (float) $promo->promo_discount_rate
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error validating promo code: ' . $e->getMessage()
            ], 500);
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

                // Get all passengers for this booking
                $passengerIds = DB::table('passenger_ticket')
                    ->where('booking_ref_no', $bookingRef)
                    ->pluck('passenger_id')
                    ->toArray();

                // Delete passenger_ticket records first (required before deleting passenger rows)
                DB::table('passenger_ticket')->where('booking_ref_no', $bookingRef)->delete();

                // Delete passengers that no longer belong to any booking
                foreach ($passengerIds as $passengerId) {
                    $otherBookings = DB::table('passenger_ticket')
                        ->where('passenger_id', $passengerId)
                        ->count();

                    if ($otherBookings == 0) {
                        DB::table('passenger')->where('passenger_id', $passengerId)->delete();
                    }
                }
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

    public function requestTicketCopy(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'departure_date' => 'required|date',
                'route_from' => 'required|string',
                'route_to' => 'required|string'
            ]);

            $service = new TicketCopyService();
            $result = $service->requestTicketCopy(
                $request->email,
                $request->departure_date,
                $request->input('route_from'),
                $request->input('route_to')
            );

            return response()->json($result, $result['success'] ? 200 : 404);

        } catch (\Exception $e) {
            \Log::error("BookingController::requestTicketCopy error: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }
}
