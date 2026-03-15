use Illuminate\Support\Facades\DB;

echo "\n=== PASSENGER BOOKINGS CHECK ===\n";

// Bookings without passengers
$noPass = DB::table('booking as b')->leftJoin('passenger_ticket as pt', 'b.booking_ref_no', '=', 'pt.booking_ref_no')->whereNull('pt.booking_ref_no')->count();
echo "Bookings with NO passengers: $noPass\n";

// Duplicate passengers in bookings
$dups = DB::table('passenger_ticket')->select('booking_ref_no', 'passenger_id', DB::raw('COUNT(*) as c'))->groupBy('booking_ref_no', 'passenger_id')->having(DB::raw('COUNT(*)'), '>', 1)->count();
echo "Duplicate passengers: $dups\n";

// Orphaned passengers
$orphanPass = DB::table('passenger as p')->leftJoin('passenger_ticket as pt', 'p.passenger_id', '=', 'pt.passenger_id')->whereNull('pt.passenger_id')->count();
echo "Orphaned passengers: $orphanPass\n";

// Payment-booking mismatch
$payMeta = DB::table('payment as p')->leftJoin('booking as b', 'p.booking_ref_no', '=', 'b.booking_ref_no')->whereNull('b.booking_ref_no')->count();
echo "Payments with NO booking: $payMeta\n";

// Total stats
$totalBookings = DB::table('booking')->count();
$confirmedBookings = DB::table('booking')->where('booking_status', 'Confirmed')->count();
$pendingBookings = DB::table('booking')->where('booking_status', 'Pending')->count();
echo "\nTotal bookings: $totalBookings (Confirmed: $confirmedBookings, Pending: $pendingBookings)\n";

// Recent bookings
echo "\nRecent 5 bookings:\n";
$recent = DB::table('booking')->orderBy('created_at', 'desc')->limit(5)->get(['booking_ref_no', 'booking_status', 'payment_status']);
foreach ($recent as $b) {
    $pc = DB::table('passenger_ticket')->where('booking_ref_no', $b->booking_ref_no)->count();
    echo "  $b->booking_ref_no | $b->booking_status | $b->payment_status | Passengers: $pc\n";
}

echo "\n=== CARGO BOOKINGS CHECK ===\n";

// Cargo without voyage
$cargoNoVoy = DB::table('cargo_booking as cb')->leftJoin('voyage as v', 'cb.voyage_id', '=', 'v.voyage_id')->whereNull('v.voyage_id')->count();
echo "Cargo bookings with NO voyage: $cargoNoVoy\n";

// Unapproved cargo
$unapproved = DB::table('cargo_booking')->whereNull('approved_by_staff_id')->count();
echo "Unapproved cargo bookings: $unapproved\n";

// Total stats
$totalCargo = DB::table('cargo_booking')->count();
$approvedCargo = DB::table('cargo_booking')->whereNotNull('approved_by_staff_id')->count();
echo "Total cargo: $totalCargo (Approved: $approvedCargo)\n";

// Recent cargo
echo "\nRecent 5 cargo bookings:\n";
$recentCargo = DB::table('cargo_booking')->orderBy('created_at', 'desc')->limit(5)->get(['cargo_booking_id', 'voyage_id', 'approved_by_staff_id']);
foreach ($recentCargo as $c) {
    $status = $c->approved_by_staff_id ? 'Approved' : 'Pending';
    $items = DB::table('cargo_item')->where('cargo_booking_id', $c->cargo_booking_id)->count();
    echo "  Cargo ID: $c->cargo_booking_id | Voyage: $c->voyage_id | $status | Items: $items\n";
}

echo "\n=== BILL OF LADING CHECK ===\n";

// BOL count
$bolCount = DB::table('bill_of_lading')->count();
echo "Total BOL records: $bolCount\n";

// BOL without cargo
$bolNoCargo = DB::table('bill_of_lading as bol')->leftJoin('cargo_booking as cb', 'bol.cargo_booking_id', '=', 'cb.cargo_booking_id')->whereNull('cb.cargo_booking_id')->count();
echo "BOL with NO cargo booking: $bolNoCargo\n";

// Recent BOL
if ($bolCount > 0) {
    echo "\nRecent 5 BOL:\n";
    $recentBOL = DB::table('bill_of_lading')->orderBy('created_at', 'desc')->limit(5)->get(['bill_of_lading_id', 'cargo_booking_id', 'voyage_id']);
    foreach ($recentBOL as $bol) {
        echo "  BOL ID: $bol->bill_of_lading_id | Cargo: $bol->cargo_booking_id | Voyage: $bol->voyage_id\n";
    }
}

echo "\n=== AUTOPLACEMENT / VOYAGE CHECK ===\n";

// Voyages without vessel
$voyNoVessel = DB::table('voyage as v')->leftJoin('vessel as ve', 'v.vessel_id', '=', 've.vessel_id')->whereNull('ve.vessel_id')->count();
echo "Voyages with NO vessel: $voyNoVessel\n";

// Vessels with hatches
$vessels = DB::table('vessel')->join('hatch', 'vessel.vessel_id', '=', 'hatch.vessel_id')->distinct('vessel.vessel_id')->count();
echo "Vessels with configured hatches: $vessels\n";

// Total stats
$totalVoyages = DB::table('voyage')->count();
$totalRoutes = DB::table('route')->count();
echo "Total voyages: $totalVoyages | Total routes: $totalRoutes\n";

// Recent voyages
echo "\nRecent 5 voyages:\n";
$recentVoy = DB::table('voyage')->orderBy('voyage_departure_date', 'desc')->limit(5)->get(['voyage_id', 'vessel_id', 'voyage_departure_date', 'voyage_status']);
foreach ($recentVoy as $v) {
    echo "  Voyage: $v->voyage_id | Vessel: $v->vessel_id | Departure: $v->voyage_departure_date | $v->voyage_status\n";
}

echo "\n✓ SYSTEM CHECK COMPLETE\n";
