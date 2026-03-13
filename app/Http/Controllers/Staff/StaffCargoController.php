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
use App\Services\CargoAutoPlacementService;
use App\Services\BillOfLadingPdf;
use App\Models\BillOfLading;
use App\Http\Controllers\Traits\StaffGuard;
use Illuminate\Validation\Rule;


class StaffCargoController extends Controller
{
    use StaffGuard;

    public function __construct()
    {
        // Note: Individual methods will handle their own auth checks to support both admin and staff
    }

    /**
     * Show cargo booking form
     */
    public function create()
    {
        // Only staff can create cargo bookings
        if (!auth()->guard('staff')->check()) {
            abort(403);
        }

        // Show voyages within next 8 days (within the week)
        $startDate = \Carbon\Carbon::now()->startOfDay();
        $endDate = \Carbon\Carbon::now()->addDays(8)->endOfDay();

        $voyages = Voyage::with('routePort')
            ->where('voyage_status', 'Scheduled')
            ->whereBetween('voyage_departure_date', [$startDate, $endDate])
            ->orderBy('voyage_departure_date')
            ->orderBy('voyage_estimated_TD')
            ->get();

        $cargoItems = collect(CargoItem::with('measurementUnit')->get()); // <-- wrap in collect()
        $cargoClassifications = CargoClassification::orderBy('cargo_classification_name')->get();
        $measurementUnits = MeasurementUnit::whereNotNull('measurement_unit_abbreviation')
            ->orderBy('measurement_unit_name')
            ->get();

        return view('authorized.staff.cargobooking', compact('voyages', 'cargoItems', 'cargoClassifications', 'measurementUnits'));
    }


    /**
     * Store new cargo booking
     */
    public function store(Request $request)
    {
        // Only staff can store cargo bookings
        if (!auth()->guard('staff')->check()) {
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
            'cargo_classification.*' => 'required|integer|exists:cargo_classification,cargo_classification_id',
            'cargo_item_id.*' => 'required|exists:cargo_item,cargo_item_id',
            'cargo_quantity.*' => 'required|integer|min:1',
            'cargo_weight.*' => 'required|numeric|min:0',
            'cargo_length.*' => 'required|numeric|min:0',
            'cargo_width.*' => 'required|numeric|min:0',
            'cargo_height.*' => 'required|numeric|min:0',
            'measurement_unit.*' => ['nullable', 'string', Rule::exists('measurement_unit', 'measurement_unit_abbreviation')],
            'cargo_cbm.*' => 'nullable|numeric|min:0',
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
            $cargoBooking->cargo_classification_id = $request->cargo_classification[$index] ?? null;
            $cargoBooking->with_measurement = $cargoItem ? $cargoItem->cargo_item_measure_required : null;

            $measurementUnit = null;
            $requiresPredefinedMeasurement = $cargoItem
                && strcasecmp((string) $cargoItem->cargo_item_measure_required, 'Yes') === 0;

            // For predefined-measure cargo, always honor the cargo_item's configured unit.
            if ($requiresPredefinedMeasurement && $cargoItem && $cargoItem->measurement_unit_id) {
                $measurementUnit = MeasurementUnit::find((int) $cargoItem->measurement_unit_id);
            }

            // For manual-measure cargo, use the unit posted from the form.
            if (!$measurementUnit && $request->has("measurement_unit.$index") && $request->measurement_unit[$index]) {
                $postedUnit = strtolower(trim((string) $request->measurement_unit[$index]));
                $measurementUnit = MeasurementUnit::whereRaw('LOWER(measurement_unit_abbreviation) = ?', [$postedUnit])->first();
            }

            if (!$measurementUnit && $cargoItem && $cargoItem->measurement_unit_id) {
                $measurementUnit = MeasurementUnit::find((int) $cargoItem->measurement_unit_id);
            }

            if ($measurementUnit) {
                $cargoBooking->measurement_unit_id = $measurementUnit->measurement_unit_id;
            }

            $unitAbbreviation = strtolower($measurementUnit?->measurement_unit_abbreviation ?? 'cm');
            $length = (float) $request->cargo_length[$index];
            $width = (float) $request->cargo_width[$index];
            $height = (float) $request->cargo_height[$index];

            if ($unitAbbreviation === 'in') {
                $length *= 2.54;
                $width *= 2.54;
                $height *= 2.54;
            } elseif ($unitAbbreviation === 'mm') {
                $length *= 0.1;
                $width *= 0.1;
                $height *= 0.1;
            } elseif ($unitAbbreviation === 'm') {
                $length *= 100;
                $width *= 100;
                $height *= 100;
            }

            $computedCbm = ($length * $width * $height) / 1000000;
            $postedCbm = $request->cargo_cbm[$index] ?? null;
            $cargoBooking->cbm = round(is_numeric($postedCbm) ? (float) $postedCbm : $computedCbm, 4);

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
        return redirect()->back()->with(
            'success',
            "Success! Your booking_ref_no: {$booking->booking_ref_no} is being queued for approval."
        );
    }

    /**
     * Show only pending cargo bookings
     */
    public function pending(Request $request)
    {
        // Allow both admin and staff
        if (!auth()->guard('admin')->check() && !auth()->guard('staff')->check()) {
            abort(403);
        }

        $search = $request->input('search');
        $selectedStatus = $request->input('booking_status');
        $allowedStatuses = ['All', 'Pending', 'Confirmed', 'Rejected'];

        if (in_array($selectedStatus, ['Canceled', 'Cancelled'], true)) {
            $selectedStatus = 'Rejected';
        }

        if (!$selectedStatus || !in_array($selectedStatus, $allowedStatuses, true)) {
            $selectedStatus = 'Pending';
        }

        $bookings = Booking::whereRaw('LOWER(booking_type) = ?', ['cargo'])
            ->when($selectedStatus !== 'All', function ($query) use ($selectedStatus) {
                if ($selectedStatus === 'Rejected') {
                    $query->whereIn('booking_status', ['Canceled', 'Cancelled']);
                } else {
                    $query->where('booking_status', $selectedStatus);
                }
            })
            ->with(['sender', 'consignee', 'voyage', 'cargoBookings.approvedByStaff'])
            ->when($search, function ($query, $search) {
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery->where('booking_ref_no', 'like', "%{$search}%")
                        ->orWhereHas('sender', function ($q) use ($search) {
                            $q->where('sender_name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('consignee', function ($q) use ($search) {
                            $q->where('consignee_name', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        $view = auth()->guard('admin')->check()
            ? 'authorized.admin.pendingcargo'
            : 'authorized.staff.pendingcargo';

        return view($view, compact('bookings', 'selectedStatus', 'allowedStatuses'));
    }

    /**
     * Show full booking (read-only)
     */
    public function show($id)
    {
        // Allow both admin and staff
        if (!auth()->guard('admin')->check() && !auth()->guard('staff')->check()) {
            abort(403);
        }

        $booking = Booking::where('booking_ref_no', $id)
            ->with(['sender', 'consignee', 'voyage.routePort', 'cargoBookings.cargoItem', 'cargoBookings.cargoClassification', 'cargoBookings.measurementUnit', 'cargoBookings.approvedByStaff'])
            ->firstOrFail();

        $payment = Payment::where('booking_ref_no', $id)->first();

        $view = auth()->guard('admin')->check()
            ? 'authorized.admin.showcargo'
            : 'authorized.staff.showcargo';

        return view($view, compact('booking', 'payment'));
    }

    /**
     * Edit cargo item details only
     */
    public function edit($id)
    {
        // Allow both admin and staff
        if (!auth()->guard('admin')->check() && !auth()->guard('staff')->check()) {
            abort(403);
        }
        $booking = Booking::with([
            'sender',
            'consignee',
            'voyage.routePort',
            'cargoBookings.cargoItem',
            'cargoBookings.measurementUnit'
        ])->where('booking_ref_no', $id)->firstOrFail();

        // For dropdowns (use cargo_classification table)
        $cargoClassifications = CargoClassification::orderBy('cargo_classification_name')->get();

        // For cargo item lookup
        $cargoItems = CargoItem::all();

        // Measurement units for dropdown (show all units from DB)
        $measurementUnits = MeasurementUnit::whereNotNull('measurement_unit_abbreviation')
            ->orderBy('measurement_unit_name')
            ->get();

        // Load all routes for the Route Destination dropdown
        $routes = RoutePort::all(); // or whatever your model is called

        return view('authorized.staff.editcargo', compact(
            'booking',
            'cargoItems',
            'cargoClassifications',
            'measurementUnits'
        ));
    }


    /**
     * Update cargo item details
     */
    public function update(Request $request, $id)
    {
        // Allow both admin and staff
        if (!auth()->guard('admin')->check() && !auth()->guard('staff')->check()) {
            abort(403);
        }
        $request->validate([
            'cargo_booking_id.*' => 'required|integer|exists:cargo_booking,cargo_booking_id',
            'classification.*' => 'required|integer|exists:cargo_classification,cargo_classification_id',
            'description.*' => 'required|integer|exists:cargo_item,cargo_item_id',
            'quantity.*' => 'required|integer|min:1',
            'length.*' => 'required|numeric|min:0',
            'width.*' => 'required|numeric|min:0',
            'height.*' => 'required|numeric|min:0',
            'weight.*' => 'required|numeric|min:0',
            'measurement_unit.*' => 'required',
            'cbm.*' => 'nullable|numeric|min:0',
        ]);

        $booking = Booking::where('booking_ref_no', $id)->firstOrFail();

        $rowCount = count($request->cargo_booking_id ?? []);
        for ($index = 0; $index < $rowCount; $index++) {
            $cargoId = (int) ($request->cargo_booking_id[$index] ?? 0);
            $cargo = CargoBooking::where('booking_ref_no', $booking->booking_ref_no)
                ->where('cargo_booking_id', $cargoId)
                ->firstOrFail();

            $length = (float) $request->length[$index];
            $width = (float) $request->width[$index];
            $height = (float) $request->height[$index];
            $measurementUnitInput = $request->measurement_unit[$index] ?? $cargo->measurement_unit_id;
            $measurementUnit = null;

            if (is_numeric($measurementUnitInput)) {
                $measurementUnit = MeasurementUnit::find((int) $measurementUnitInput);
            }

            // Backward compatibility for forms still posting abbreviation values (e.g. "cm", "in").
            if (!$measurementUnit && is_string($measurementUnitInput)) {
                $measurementUnit = MeasurementUnit::whereRaw('LOWER(measurement_unit_abbreviation) = ?', [strtolower(trim($measurementUnitInput))])->first();
            }

            if (!$measurementUnit && $cargo->measurement_unit_id) {
                $measurementUnit = MeasurementUnit::find((int) $cargo->measurement_unit_id);
            }

            if (!$measurementUnit) {
                return back()
                    ->withErrors(['measurement_unit' => 'Invalid measurement unit selected for one or more cargo items.'])
                    ->withInput();
            }

            $unitAbbreviation = strtolower($measurementUnit?->measurement_unit_abbreviation ?? 'cm');

            $lengthCm = $length;
            $widthCm = $width;
            $heightCm = $height;

            if ($unitAbbreviation === 'in') {
                $lengthCm *= 2.54;
                $widthCm *= 2.54;
                $heightCm *= 2.54;
            } elseif ($unitAbbreviation === 'mm') {
                $lengthCm *= 0.1;
                $widthCm *= 0.1;
                $heightCm *= 0.1;
            } elseif ($unitAbbreviation === 'm') {
                $lengthCm *= 100;
                $widthCm *= 100;
                $heightCm *= 100;
            }

            $computedCbm = ($lengthCm * $widthCm * $heightCm) / 1000000;

            $cargo->update([
                'cargo_classification_id' => $request->classification[$index],
                'cargo_item_id' => $request->description[$index],
                'measurement_unit_id' => $measurementUnit?->measurement_unit_id,
                'quantity' => $request->quantity[$index],
                'length' => $request->length[$index],
                'width' => $request->width[$index],
                'height' => $request->height[$index],
                'weight' => $request->weight[$index],
                'cbm' => round($computedCbm, 4),
            ]);
        }

        return redirect()
            ->route('cargo.bookings.show', $id)
            ->with('success', 'Cargo item details updated successfully.');
    }

    /**
     * Approve a booking with auto-placement validation
     */
    public function approve($id)
    {
        // Allow both admin and staff
        if (!auth()->guard('admin')->check() && !auth()->guard('staff')->check()) {
            abort(403);
        }

        $booking = Booking::with([
            'sender',
            'consignee',
            'voyage',
            'cargoBookings.cargoItem',
            'cargoBookings.cargoClassification',
            'cargoBookings.measurementUnit'
        ])->where('booking_ref_no', $id)->firstOrFail();

        // Idempotency guard: avoid duplicate approval side effects (payment, BOL records, email sends).
        if (strcasecmp((string) $booking->booking_status, 'Pending') !== 0) {
            return redirect()->route('cargo.bookings.pending')
                ->with('success', "Booking #{$booking->booking_ref_no} is already processed.");
        }

        $existingCompletedPayment = Payment::where('booking_ref_no', $booking->booking_ref_no)
            ->where('payment_status', 'Completed')
            ->first();

        if ($existingCompletedPayment) {
            return redirect()->route('cargo.bookings.pending')
                ->with('success', "Booking #{$booking->booking_ref_no} is already processed.");
        }

        // Step 1: Validate cargo can fit in available hatches
        $cargoBookingIds = $booking->cargoBookings->pluck('cargo_booking_id')->toArray();
        $placementValidation = CargoAutoPlacementService::validateCargoPlacement(
            $booking->voyage_id,
            $cargoBookingIds
        );

        // If cargo cannot fit, return error
        if (!$placementValidation['success'] && !($placementValidation['skipValidation'] ?? false)) {
            return back()
                ->withErrors([
                    'placement' => $placementValidation['message'],
                    'unpacked_items' => !empty($placementValidation['unpackedItems'])
                        ? 'Items that cannot fit: ' . implode(', ', array_map(fn($item) => $item['name'] ?? $item['id'], $placementValidation['unpackedItems']))
                        : ''
                ])
                ->with('placement_data', $placementValidation);
        }

        // Step 2: Proceed with normal approval if placement is successful
        $booking->booking_status = 'Confirmed';
        $booking->save();

        // Calculate total cost
        $staffId = auth()->guard('staff')->user()->staff_id ?? (auth()->guard('admin')->user()->admin_id ?? null);
        $totalCost = 0;
        foreach ($booking->cargoBookings as $cargo) {
            $freight = $cargo->cargoItem->cargo_item_freight;
            $cbm = $cargo->cbm ?? (($cargo->length * $cargo->width * $cargo->height) / 1000000);
            $subtotal = $freight * $cbm * $cargo->quantity;
            $totalCost += $subtotal;

            $cargo->approved_by_staff_id = $staffId;
            $cargo->save();
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
            $receipt->cargo_booking_id = $cargo->cargo_booking_id;  // Link to the specific CargoBooking
            $receipt->sender_id = $booking->sender_id;
            $receipt->consignee_id = $booking->consignee_id;
            $receipt->cargo_item_id = $cargo->cargo_item_id ?? null;
            $receipt->voyage_id = $booking->voyage_id;
            $receipt->cargo_item_qty = $cargo->quantity;
            
            // Auto-assign hatch based on available weight capacity
            $voyage = $booking->voyage;
            $cargoWeight = $cargo->weight ?? 0;
            
            // Find best hatch with available capacity
            $bestHatch = null;
            if ($voyage && $voyage->vessel) {
                $hatches = $voyage->vessel->hatches;
                foreach ($hatches as $hatch) {
                    // Calculate current weight in this hatch
                    $currentWeight = \DB::table('cargo_receipt')
                        ->join('cargo_booking', 'cargo_receipt.cargo_booking_id', '=', 'cargo_booking.cargo_booking_id')
                        ->where('cargo_receipt.hatch_id', $hatch->hatch_id)
                        ->where('cargo_receipt.voyage_id', $voyage->voyage_id)
                        ->sum('cargo_booking.weight') ?? 0;
                    
                    $maxCapacity = ((float)$hatch->hatch_capacity_per_hold) * 1000; // Convert tons to kg
                    $availableCapacity = $maxCapacity - $currentWeight;
                    
                    // Check if cargo fits in this hatch
                    if ($cargoWeight <= $availableCapacity) {
                        $bestHatch = $hatch->hatch_id;
                        break; // Use first hatch that fits
                    }
                }
            }
            
            $receipt->hatch_id = $bestHatch; // Assign hatch_id
            $receipt->save();

            $cargoReceiptIds[] = $receipt->cargo_receipt_id;
        }

        // Create bill of lading records with voyage details (vessel name, loading port, unloading port)
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
            ->with('success', 'Booking approved! Cargo can fit in available hatches and has been added to auto-placement visualization.');
    }

    /**
     * API Endpoint: Validate if cargo can be placed in hatches
     * Called via AJAX before accepting a booking
     */
    public function validatePlacement(Request $request)
    {
        // Allow both admin and staff
        if (!auth()->guard('admin')->check() && !auth()->guard('staff')->check()) {
            abort(403);
        }

        $request->validate([
            'voyage_id' => 'required|exists:voyage,voyage_id',
            'cargo_booking_ids' => 'required|array',
            'cargo_booking_ids.*' => 'exists:cargo_booking,cargo_booking_id',
        ]);

        $result = CargoAutoPlacementService::validateCargoPlacement(
            $request->voyage_id,
            $request->cargo_booking_ids
        );

        return response()->json($result);
    }

    /**
     * Reject a booking
     */
    public function reject(Request $request, $id)
    {
        // Allow both admin and staff
        if (!auth()->guard('admin')->check() && !auth()->guard('staff')->check()) {
            abort(403);
        }

        $request->validate(['reason' => 'required|string|max:1000']);

        $booking = Booking::with([
            'sender',
            'consignee',
            'voyage',
            'cargoBookings.cargoItem',
            'cargoBookings.cargoClassification',
            'cargoBookings.measurementUnit'
        ])->where('booking_ref_no', $id)->firstOrFail();
        $staffId = auth()->guard('staff')->user()->staff_id ?? (auth()->guard('admin')->user()->admin_id ?? null);

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
        // Allow both admin and staff
        if (!auth()->guard('admin')->check() && !auth()->guard('staff')->check()) {
            abort(403);
        }
        $booking = Booking::with([
            'sender',
            'consignee',
            'cargoBookings.cargoItem',
            'cargoBookings.cargoClassification',
            'cargoBookings.measurementUnit',
            'voyage.vessel',
            'voyage.routePort'
        ])
            ->where('booking_ref_no', $id)
            ->firstOrFail();

        $pdf = BillOfLadingPdf::generate($booking);

        if ($pdf === null) {
            return response()->view('authorized.staff.bill_of_lading_pdf', compact('booking'));
        }

        return response($pdf, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="bill_of_lading_' . $id . '.pdf"');
    }

    /**
     * Display the Bill of Lading in a formatted HTML view (printable)
     */
    public function bolView($id)
    {
        // Allow both admin and staff
        if (!auth()->guard('admin')->check() && !auth()->guard('staff')->check()) {
            abort(403);
        }
        $booking = Booking::with([
            'sender',
            'consignee',
            'cargoBookings.cargoItem',
            'cargoBookings.cargoClassification',
            'cargoBookings.measurementUnit',
            'voyage.vessel',
            'voyage.routePort'
        ])
            ->where('booking_ref_no', $id)
            ->firstOrFail();

        return view('authorized.staff.bill_of_lading', compact('booking'));
    }

}
