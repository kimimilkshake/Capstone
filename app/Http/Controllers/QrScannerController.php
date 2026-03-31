<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QrScannerController extends Controller
{
    public function index()
    {
        if (!auth()->guard('staff')->check()) {
            return redirect()->route('scanner.login.form');
        }

        return view('authorized.staff.qr_scanner');
    }

    public function boardPassenger(Request $request)
    {
        if (!auth()->guard('staff')->check()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $bookingRefNo = trim((string) $request->input('booking_ref_no'));
        $passengerId = trim((string) $request->input('passenger_id'));

        if (!$bookingRefNo || !$passengerId) {
            return response()->json(['success' => false, 'message' => 'Invalid QR code data.'], 400);
        }

        try {
            $booking = DB::table('booking')
                ->where('booking_ref_no', $bookingRefNo)
                ->whereIn('booking_status', ['Confirmed', 'Boarded'])
                ->first();

            if (!$booking) {
                return response()->json(['success' => false, 'message' => 'No Passenger found on the manifest.'], 404);
            }

            $passenger = DB::table('passenger_ticket')
                ->where('booking_ref_no', $bookingRefNo)
                ->where('passenger_id', $passengerId)
                ->first();

            if (!$passenger) {
                return response()->json(['success' => false, 'message' => 'No Passenger found on the manifest.'], 404);
            }

            $passengerInfo = DB::table('passenger')
                ->where('passenger_id', $passengerId)
                ->select('passenger_firstname', 'passenger_lastname')
                ->first();

            $passengerName = $passengerInfo
                ? trim($passengerInfo->passenger_firstname . ' ' . $passengerInfo->passenger_lastname)
                : 'Unknown';

            if ($passenger->pt_boarded_at) {
                return response()->json(['success' => true, 'message' => $passengerName . ' is already listed as Boarded.', 'passenger_name' => $passengerName]);
            }

            DB::transaction(function () use ($bookingRefNo, $passenger) {
                DB::table('passenger_ticket')
                    ->where('passenger_ticket_id', $passenger->passenger_ticket_id)
                    ->update([
                        'pt_boarded_at' => now(),
                        'updated_at' => now(),
                    ]);

                $hasUnboardedPassengers = DB::table('passenger_ticket')
                    ->where('booking_ref_no', $bookingRefNo)
                    ->whereNull('pt_boarded_at')
                    ->exists();

                DB::table('booking')
                    ->where('booking_ref_no', $bookingRefNo)
                    ->update([
                        'booking_status' => $hasUnboardedPassengers ? 'Confirmed' : 'Boarded',
                        'updated_at' => now(),
                    ]);
            });

            return response()->json(['success' => true, 'message' => $passengerName . ' is now listed as Boarded.', 'passenger_name' => $passengerName]);
        } catch (\Throwable $exception) {
            Log::error('QR boarding failed', [
                'booking_ref_no' => $bookingRefNo,
                'passenger_id' => $passengerId,
                'error' => $exception->getMessage(),
            ]);

            return response()->json(['success' => false, 'message' => 'Unable to update passenger boarding status.'], 500);
        }
    }
}
