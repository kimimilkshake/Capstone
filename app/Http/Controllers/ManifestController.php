<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Vessel;
use App\Models\VesselRoute;
use App\Models\Voyage;
use App\Models\Booking;
use App\Models\Passenger;
use App\Models\CargoBooking;
use App\Models\CargoReceipt;
use App\Models\CargoItem;
use App\Models\Payment;

class ManifestController extends Controller
{
    /**
     * Show the manifest for a voyage with server-side filter options.
     *
     * - Uses passenger_ticket.voyage_id (if present) to load passengers.
     * - Uses cargo_receipt.voyage_id (if present) to load cargos, or falls back
     *   to cargo_booking joined by booking_ref_no, or to booking rows marked cargo.
     *
     * Query params:
     * - show_passenger (boolean)
     * - show_cargo (boolean)
     */
    public function show(Request $request, $voyageId)
    {
        // Determine whether the user explicitly submitted the filter form
        $hasFilterParams = $request->hasAny(['show_passenger', 'show_cargo']);

        // If the user submitted the form, respect submitted values (0/1). Otherwise, default to showing both.
        $showPassenger = $hasFilterParams ? $request->boolean('show_passenger', false) : true;
        $showCargo     = $hasFilterParams ? $request->boolean('show_cargo', false) : true;

        // Load voyage header info
        $voyage = Voyage::with(['routePort', 'vessel'])->findOrFail($voyageId);

        $passengers = collect();
        $bookingRefs = collect($passengers)->pluck('booking_ref')->filter()->unique()->values()->toArray(); $bookingsByRef = Booking::whereIn('booking_ref_no', $bookingRefs)->get()->keyBy('booking_ref_no'); // pass compact('bookingsByRef') to the view
        $cargos = collect();

        // --------------------
        // PASSENGERS: prefer passenger_ticket.voyage_id
        // --------------------
        if ($showPassenger) {
            if (Schema::hasTable('passenger_ticket') && Schema::hasColumn('passenger_ticket', 'voyage_id')) {
                // join passenger_ticket -> passenger (use voyage_id, since your data has that)
                $passengers = DB::table('passenger_ticket as pt')
                    ->join('passenger as p', 'p.passenger_id', '=', 'pt.passenger_id')
                    ->where('pt.voyage_id', $voyage->voyage_id)
                    ->select(
                        'p.*',
                        'pt.passenger_ticket_id',
                        'pt.booking_ref_no as booking_ref',
                        'pt.pt_ticket_price',
                        'pt.pt_cot_no',
                        'pt.created_at as ticket_created_at'
                    )
                    ->get();
            } else {
                // Fallback: try to find passenger data on booking rows or bookings that link to passenger model
                $bookingTable = (new Booking)->getTable();

                if (Schema::hasColumn($bookingTable, 'voyage_id')) {
                    // permissive: consider bookings that are either marked passenger or contain passenger fields
                    $bookings = Booking::where('voyage_id', $voyage->voyage_id)
                        ->when(Schema::hasColumn($bookingTable, 'booking_type'), function ($q) {
                            $q->where('booking_type', 'passenger');
                        }, function ($q) {
                            // if no booking_type column, no-op - we'll rely on passenger fields
                        })
                        ->when(Schema::hasColumn($bookingTable, 'passenger_firstname'), function ($q) {
                            $q->orWhereNotNull('passenger_firstname');
                        })
                        ->when(Schema::hasColumn($bookingTable, 'passenger_lastname'), function ($q) {
                            $q->orWhereNotNull('passenger_lastname');
                        })
                        ->get();

                    if ($bookings->isNotEmpty()) {
                        // If bookings reference passenger_id, load Passenger models
                        if (Schema::hasColumn($bookingTable, 'passenger_id') && class_exists(Passenger::class)) {
                            $ids = $bookings->pluck('passenger_id')->filter()->unique();
                            if ($ids->isNotEmpty()) {
                                $passengers = Passenger::whereIn((new Passenger)->getKeyName(), $ids)->get();
                            }
                        }

                        // If still empty, map booking rows that contain passenger fields to stdClass objects
                        if ($passengers->isEmpty()) {
                            $passengers = $bookings->map(function ($b) {
                                return (object) [
                                    'booking_ref' => $b->booking_ref_no ?? $b->getKey(),
                                    'passenger_firstname' => $b->passenger_firstname ?? null,
                                    'passenger_midinitial' => $b->passenger_midinitial ?? null,
                                    'passenger_lastname' => $b->passenger_lastname ?? null,
                                    'passenger_age' => $b->passenger_age ?? null,
                                    'passenger_gender' => $b->passenger_gender ?? null,
                                ];
                            });
                        }
                    }
                }
            }
        }

        // --------------------
        // CARGOS
        // --------------------
        if ($showCargo) {
            $cargos = CargoReceipt::where('voyage_id', $voyage->voyage_id)
    ->with(['booking', 'cargoBooking', 'cargoItem', 'sender', 'consignee', 'payment'])
    ->get();

        }

        // Render the view
        return view('authorized.admin.adminmanifest', compact('voyage', 'showPassenger', 'showCargo', 'passengers', 'cargos'));
    }
}