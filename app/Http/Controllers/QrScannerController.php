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

    /**
     * Verify cargo payment using QR code data
     * QR code contains: booking_ref, payment_id, amount, verified, type
     */
    public function verifyCargoPayment(Request $request)
    {
        if (!auth()->guard('staff')->check()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }

        $qrData = trim((string) $request->input('qr_data'));

        if (!$qrData) {
            return response()->json(['success' => false, 'message' => 'Invalid QR code data.'], 400);
        }

        try {
            // Try to decode QR data as JSON
            $data = json_decode($qrData, true);
            
            // If not JSON, treat as booking reference
            if (!$data) {
                $bookingRefNo = $qrData;
            } else {
                $bookingRefNo = $data['booking_ref'] ?? null;
                $paymentId = $data['payment_id'] ?? null;
            }

            if (!$bookingRefNo) {
                return response()->json(['success' => false, 'message' => 'Invalid QR code format.'], 400);
            }

            // Get the booking
            $booking = DB::table('booking')
                ->where('booking_ref_no', $bookingRefNo)
                ->first();

            if (!$booking) {
                return response()->json(['success' => false, 'message' => 'Booking not found.'], 404);
            }

            // Check if it's a cargo booking
            if (strtolower($booking->booking_type ?? '') !== 'cargo') {
                return response()->json(['success' => false, 'message' => 'This QR code is not for cargo payment verification.'], 400);
            }

            // Get the payment record
            $payment = DB::table('payment')
                ->where('booking_ref_no', $bookingRefNo)
                ->first();

            if (!$payment) {
                return response()->json(['success' => false, 'message' => 'Payment record not found.'], 404);
            }

            // Check if payment is completed
            if (strtolower($payment->payment_status) !== 'completed') {
                return response()->json([
                    'success' => false, 
                    'message' => 'Payment not completed. Status: ' . ($payment->payment_status ?? 'Unknown')
                ], 400);
            }

            // Get sender information
            $sender = DB::table('sender')
                ->where('sender_id', $booking->sender_id)
                ->first();

            // Get voyage information
            $voyage = DB::table('voyage')
                ->where('voyage_id', $booking->voyage_id)
                ->first();

            // Return success with booking details
            return response()->json([
                'success' => true,
                'message' => 'Payment verified successfully!',
                'booking' => [
                    'ref_no' => $booking->booking_ref_no,
                    'sender_name' => $sender->sender_name ?? 'Unknown',
                    'departure_date' => $voyage ? \Carbon\Carbon::parse($voyage->voyage_departure_date)->format('M d, Y') : 'N/A',
                    'loading_port' => $voyage->loading_port ?? 'N/A',
                    'unloading_port' => $voyage->unloading_port ?? 'N/A',
                ],
                'payment' => [
                    'amount' => number_format((float) $payment->total_amount, 2),
                    'status' => $payment->payment_status,
                    'date' => $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y H:i') : 'N/A',
                ]
            ]);
        } catch (\Throwable $exception) {
            Log::error('Cargo payment verification failed', [
                'qr_data' => $qrData,
                'error' => $exception->getMessage(),
            ]);

            return response()->json(['success' => false, 'message' => 'Unable to verify payment status.'], 500);
        }
    }
}
