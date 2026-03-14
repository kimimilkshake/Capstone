<?php

// Include Laravel paths
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';

// Boot the application
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Voyage;
use Carbon\Carbon;

echo "╔════════════════════════════════════════════════════════════════╗" . PHP_EOL;
echo "║   STAFF PASSENGER BOOKING - DETAILED DEBUG                     ║" . PHP_EOL;
echo "║   Examining the exact query and results                        ║" . PHP_EOL;
echo "╚════════════════════════════════════════════════════════════════╝" . PHP_EOL;

$now = Carbon::now();
$today = today();
$tomorrow = today()->addDay();
$sevenDaysFromNow = today()->addDays(7);
$twoHoursFromNow = now()->addHours(2);

echo PHP_EOL . "═══ CURRENT TIME CONTEXT ═══" . PHP_EOL;
echo "Now: " . $now->format('Y-m-d H:i:s') . PHP_EOL;
echo "Today: " . $today->format('Y-m-d') . PHP_EOL;
echo "Tomorrow: " . $tomorrow->format('Y-m-d') . PHP_EOL;
echo "7 Days from now: " . $sevenDaysFromNow->format('Y-m-d') . PHP_EOL;
echo "2 Hours from now (time): " . $twoHoursFromNow->format('H:i:s') . PHP_EOL;

try {
    echo PHP_EOL . "═══ ALL SCHEDULED VOYAGES IN DATABASE ═══" . PHP_EOL;

    $allVoyages = Voyage::where('voyage_status', 'Scheduled')->get();

    foreach ($allVoyages as $voyage) {
        echo PHP_EOL . "Voyage: " . $voyage->voyage_code . PHP_EOL;
        echo "  Departure: " . $voyage->voyage_departure_date . " at " . $voyage->voyage_estimated_TD . PHP_EOL;
        echo "  Status: " . $voyage->voyage_status . PHP_EOL;
    }

    echo PHP_EOL . "═══ TESTING EACH FILTER CONDITION ═══" . PHP_EOL;

    foreach ($allVoyages as $voyage) {
        echo PHP_EOL . "Voyage: " . $voyage->voyage_code . PHP_EOL;

        $depDate = $voyage->voyage_departure_date;
        $depTime = $voyage->voyage_estimated_TD;

        // Check status
        $statusPass = $voyage->voyage_status === 'Scheduled';
        echo "  Status = 'Scheduled': " . ($statusPass ? "✓" : "✗") . " ($voyage->voyage_status)" . PHP_EOL;

        // Check date range
        $datePass = Carbon::parse($depDate)->isBetween(today(), today()->addDays(7));
        echo "  Within 7 days (between today and +7 days): " . ($datePass ? "✓" : "✗") . " ($depDate)" . PHP_EOL;

        // Check tomorrow onwards OR today with 2hr buffer
        $isTomorrow = Carbon::parse($depDate)->isAfter(today()->endOfDay());
        $isToday = Carbon::parse($depDate)->isToday();
        $timePass = false;
        $timeReason = "";

        if ($isTomorrow) {
            $timePass = true;
            $timeReason = "Tomorrow or later";
        } elseif ($isToday) {
            $depDateTime = Carbon::parse($depDate . ' ' . $depTime);
            $timePass = $depDateTime->isAfter(now()->addHours(2));
            $timeReason = "Today, and departure at " . $depTime . " is " . ($timePass ? ">" : "<") . " 2hr-from-now (" . now()->addHours(2)->format('H:i:s') . ")";
        }
        echo "  Tomorrow+ OR (Today AND time > 2hr-from-now): " . ($timePass ? "✓" : "✗") . " (" . $timeReason . ")" . PHP_EOL;

        $willShow = $statusPass && $datePass && $timePass;
        echo "  WILL BE SHOWN: " . ($willShow ? "✓ YES" : "✗ NO") . PHP_EOL;
    }

    echo PHP_EOL . "═══ RUNNING ACTUAL STAFF QUERY ═══" . PHP_EOL;

    $staffVoyages = Voyage::with(['routePort', 'vessel.accommodations'])
        ->where('voyage_status', 'Scheduled')
        ->where(function ($query) {
            $query->whereDate('voyage_departure_date', '>', today())
                ->orWhere(function ($q) {
                    $q->whereDate('voyage_departure_date', '=', today())
                        ->where('voyage_estimated_TD', '>', now()->addHours(2)->format('H:i:s'));
                });
        })
        ->whereDate('voyage_departure_date', '<=', today()->addDays(7))
        ->orderBy('voyage_departure_date', 'asc')
        ->orderBy('voyage_estimated_TD', 'asc')
        ->get();

    echo "Query returned: " . $staffVoyages->count() . " voyages" . PHP_EOL;

    foreach ($staffVoyages as $voyage) {
        echo "  ✓ " . $voyage->voyage_code . " (" . $voyage->voyage_departure_date . " " . $voyage->voyage_estimated_TD . ")" . PHP_EOL;
    }

    // Get the SQL query
    echo PHP_EOL . "═══ ACTUAL SQL QUERY BEING RUN ═══" . PHP_EOL;
    $query = Voyage::with(['routePort', 'vessel.accommodations'])
        ->where('voyage_status', 'Scheduled')
        ->where(function ($query) {
            $query->whereDate('voyage_departure_date', '>', today())
                ->orWhere(function ($q) {
                    $q->whereDate('voyage_departure_date', '=', today())
                        ->where('voyage_estimated_TD', '>', now()->addHours(2)->format('H:i:s'));
                });
        })
        ->whereDate('voyage_departure_date', '<=', today()->addDays(7))
        ->orderBy('voyage_departure_date', 'asc')
        ->orderBy('voyage_estimated_TD', 'asc');

    echo $query->toSql() . PHP_EOL;
    echo PHP_EOL . "Bindings:" . PHP_EOL;
    foreach ($query->getBindings() as $idx => $binding) {
        echo "  $" . ($idx + 1) . ": " . $binding . PHP_EOL;
    }

    echo PHP_EOL . "╔════════════════════════════════════════════════════════════════╗" . PHP_EOL;
    echo "║                    DEBUG COMPLETE                          ║" . PHP_EOL;
    echo "╚════════════════════════════════════════════════════════════════╝" . PHP_EOL;

    echo PHP_EOL;

} catch (\Exception $e) {
    echo PHP_EOL . "✗ Error: " . $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}
?>