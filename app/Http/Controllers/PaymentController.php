<?php

namespace App\Http\Controllers;

use App\Jobs\SendTicketEmail;
use App\Jobs\SendCargoPaymentConfirmationEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Create a PayMongo source for GCash and return checkout URL
     */
    public function createSource(Request $request)
    {
        $bookingRef = $request->input('booking_ref_no');
        if (!$bookingRef) {
            return response()->json(['success' => false, 'message' => 'Missing booking reference'], 422);
        }

        $payment = DB::table('payment')->where('booking_ref_no', $bookingRef)->first();
        if (!$payment) {
            return response()->json(['success' => false, 'message' => 'Payment record not found'], 404);
        }

        $amountPhp = (float) $payment->total_amount;
        $amount = (int) round($amountPhp * 100); // cents/centavos

        $successUrl = url('/paymongo/return?booking_ref_no=' . $bookingRef);
        $failedUrl = url('/paymongo/failed?booking_ref_no=' . $bookingRef);

        // Try to get billing name/email from the first passenger on this booking
        $firstPassenger = DB::table('passenger')
            ->join('passenger_ticket', 'passenger.passenger_id', '=', 'passenger_ticket.passenger_id')
            ->where('passenger_ticket.booking_ref_no', $bookingRef)
            ->select('passenger.*')
            ->first();

        $billingName = 'Passenger';
        $billingEmail = 'no-reply+' . $bookingRef . '@example.invalid';
        if ($firstPassenger) {
            $billingName = trim(($firstPassenger->passenger_firstname ?? '') . ' ' . ($firstPassenger->passenger_lastname ?? '')) ?: $billingName;
            if (!empty($firstPassenger->passenger_email)) {
                $billingEmail = $firstPassenger->passenger_email;
            }
        }

        $payload = [
            'data' => [
                'attributes' => [
                    'type' => 'gcash',
                    'amount' => $amount,
                    'currency' => 'PHP',
                    'redirect' => [
                        'success' => $successUrl,
                        'failed' => $failedUrl,
                    ],
                    // billing info (required by PayMongo)
                    'billing' => [
                        'email' => $billingEmail,
                        'name' => $billingName,
                    ],
                ],
            ],
        ];

        $secret = env('PAYMONGO_SECRET');
        if (!$secret) {
            return response()->json(['success' => false, 'message' => 'PayMongo secret not configured'], 500);
        }

        try {
            $response = Http::withBasicAuth($secret, '')->timeout(10)->post('https://api.paymongo.com/v1/sources', $payload);
            if ($response->failed()) {
                \Log::error('PayMongo create source failed', ['status' => $response->status(), 'body' => $response->body()]);
                return response()->json(['success' => false, 'message' => 'Failed to create PayMongo source'], 500);
            }

            $body = $response->json();
            $sourceId = $body['data']['id'] ?? null;
            $checkoutUrl = $body['data']['attributes']['redirect']['checkout_url'] ?? null;

            // Store source id in payment.transaction_code for later matching by webhook
            // Also update mode_of_payment to GCash for online payments
            if ($sourceId) {
                DB::table('payment')->where('payment_id', $payment->payment_id)->update([
                    'transaction_code' => $sourceId,
                    'mode_of_payment' => 'Gcash',
                    'updated_at' => now(),
                ]);
            }

            return response()->json(['success' => true, 'checkout_url' => $checkoutUrl]);
        } catch (\Exception $e) {
            \Log::error('PayMongo create exception: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Exception creating PayMongo source'], 500);
        }
    }

    /**
     * Handle PayMongo webhook events
     */
    public function webhook(Request $request)
    {
        // Optional signature verification (placeholder - adjust to PayMongo spec if different)
        if ($secretSig = env('PAYMONGO_WEBHOOK_SECRET')) {
            $provided = $request->header('Paymongo-Signature');
            if (!$provided || !Str::contains($provided, $secretSig)) {
                Log::warning('PayMongo signature mismatch', ['header' => $provided]);
                return response()->json(['error' => 'invalid signature'], 401);
            }
        }

        $payload = $request->all();
        Log::info('PayMongo webhook raw', $payload);

        // Events come as: data:{ id, type:event, attributes:{ type: <event_type>, data: { id, type, attributes:{...} } } }
        $event = $payload['data'] ?? [];
        $eventAttributes = $event['attributes'] ?? [];
        $eventType = $eventAttributes['type'] ?? null; // e.g. source.chargeable, payment.paid
        $resource = $eventAttributes['data'] ?? []; // the actual source/payment resource
        $resourceId = $resource['id'] ?? null;
        $resourceType = $resource['type'] ?? null; // 'source' or 'payment'
        $resourceAttrs = $resource['attributes'] ?? [];
        $secret = env('PAYMONGO_SECRET');

        // SOURCE CHARGEABLE -> attempt charge
        if ($eventType === 'source.chargeable' && $resourceType === 'source' && $resourceId) {
            $payment = DB::table('payment')->where('transaction_code', $resourceId)->first();
            if ($payment && strtolower($payment->payment_status) === 'pending' && $secret) {
                $amountPhp = (float) $payment->total_amount;
                $amount = (int) round($amountPhp * 100);
                try {
                    $chargePayload = [
                        'data' => [
                            'attributes' => [
                                'amount' => $amount,
                                'currency' => 'PHP',
                                'source' => ['id' => $resourceId, 'type' => 'source'],
                                'description' => 'Booking #' . $payment->booking_ref_no,
                                'statement_descriptor' => 'Booking ' . $payment->booking_ref_no,
                            ],
                        ],
                    ];
                    $chargeResp = Http::withBasicAuth($secret, '')->timeout(10)->post('https://api.paymongo.com/v1/payments', $chargePayload);
                    if ($chargeResp->successful()) {
                        $chargeJson = $chargeResp->json();
                        $chargeStatus = $chargeJson['data']['attributes']['status'] ?? null;
                        Log::info('PayMongo charge success', ['status' => $chargeStatus]);
                        if ($chargeStatus === 'paid') {
                            // Check if this is a cargo or passenger booking
                            $booking = DB::table('booking')->where('booking_ref_no', $payment->booking_ref_no)->first();
                            $isCargo = $booking && strtolower($booking->booking_type ?? '') === 'cargo';
                            
                            DB::table('payment')->where('payment_id', $payment->payment_id)->update([
                                'payment_status' => $isCargo ? 'Initial' : 'Completed',
                                'updated_at' => now(),
                            ]);
                            DB::table('booking')->where('booking_ref_no', $payment->booking_ref_no)->update([
                                'booking_status' => 'Confirmed',
                                'updated_at' => now(),
                            ]);

                            if ($isCargo) {
                                // Send cargo payment confirmation with Freight Receipt PDF
                                SendCargoPaymentConfirmationEmail::dispatch($payment->booking_ref_no);
                                Log::info('CargoPaymentConfirmationEmail dispatched for booking: ' . $payment->booking_ref_no);
                            } else {
                                // Send passenger ticket email
                                SendTicketEmail::dispatch($payment->booking_ref_no);
                            }
                        }
                    } else {
                        Log::error('PayMongo charge failure', ['status' => $chargeResp->status(), 'body' => $chargeResp->body()]);
                    }
                } catch (\Exception $e) {
                    Log::error('PayMongo charge exception', ['message' => $e->getMessage()]);
                }
            }
        }

        // PAYMENT EVENTS
        if (Str::startsWith((string) $eventType, 'payment.') && $resourceType === 'payment' && $resourceId) {
            $status = $resourceAttrs['status'] ?? null; // expected: paid|succeeded|failed|canceled
            $sourceId = $resourceAttrs['source']['id'] ?? null;
            if ($sourceId) {
                $payment = DB::table('payment')->where('transaction_code', $sourceId)->first();
                if ($payment && $status) {
                    if (in_array($status, ['paid', 'succeeded'])) {
                        // Check if this is a cargo or passenger booking
                        $booking = DB::table('booking')->where('booking_ref_no', $payment->booking_ref_no)->first();
                        $isCargo = $booking && strtolower($booking->booking_type ?? '') === 'cargo';
                        
                        DB::table('payment')->where('payment_id', $payment->payment_id)->update([
                            'payment_status' => $isCargo ? 'Initial' : 'Completed',
                            'updated_at' => now(),
                        ]);
                        DB::table('booking')->where('booking_ref_no', $payment->booking_ref_no)->update([
                            'booking_status' => 'Confirmed',
                            'updated_at' => now(),
                        ]);

                        if ($isCargo) {
                            // Send cargo payment confirmation with Freight Receipt PDF
                            SendCargoPaymentConfirmationEmail::dispatch($payment->booking_ref_no);
                            Log::info('CargoPaymentConfirmationEmail dispatched for booking: ' . $payment->booking_ref_no);
                        } else {
                            // Send passenger ticket email
                            SendTicketEmail::dispatch($payment->booking_ref_no);
                        }
                    } elseif (in_array($status, ['failed', 'canceled'])) {
                        DB::table('payment')->where('payment_id', $payment->payment_id)->update([
                            'payment_status' => 'Canceled',
                            'updated_at' => now(),
                        ]);
                        DB::table('booking')->where('booking_ref_no', $payment->booking_ref_no)->update([
                            'booking_status' => 'Canceled',
                            'updated_at' => now(),
                        ]);

                        // Check if this is a passenger booking
                        $booking = DB::table('booking')->where('booking_ref_no', $payment->booking_ref_no)->first();
                        if ($booking && strtolower($booking->booking_type ?? '') !== 'cargo') {
                            // Get all passengers for this booking
                            $passengerIds = DB::table('passenger_ticket')
                                ->where('booking_ref_no', $payment->booking_ref_no)
                                ->pluck('passenger_id')
                                ->toArray();

                            // Delete passengers if they only belong to this booking
                            foreach ($passengerIds as $passengerId) {
                                $otherBookings = DB::table('passenger_ticket')
                                    ->where('passenger_id', $passengerId)
                                    ->where('booking_ref_no', '!=', $payment->booking_ref_no)
                                    ->count();

                                if ($otherBookings == 0) {
                                    DB::table('passenger')->where('passenger_id', $passengerId)->delete();
                                }
                            }

                            // Delete passenger tickets when payment fails
                            DB::table('passenger_ticket')->where('booking_ref_no', $payment->booking_ref_no)->delete();
                        }
                    }
                }
            }
        }

        return response()->json(['received' => true]);
    }

    /**
     * Redirect endpoint for PayMongo success return.
     */
    public function redirectReturn(Request $request)
    {
        $bookingRef = $request->query('booking_ref_no');
        Log::info('PayMongo redirectReturn called', ['booking_ref_no' => $bookingRef]);
        if (!$bookingRef) {
            return redirect()->route('homepage');
        }

        // Try to verify payment status immediately by querying PayMongo using stored transaction_code (source id)
        $payment = DB::table('payment')->where('booking_ref_no', $bookingRef)->first();
        $secret = env('PAYMONGO_SECRET');
        $status = null; // Initialize status variable
        if ($payment && $payment->transaction_code && $secret) {
            try {
                $sourceId = $payment->transaction_code;
                Log::info('Querying PayMongo for source', ['source_id' => $sourceId]);
                $response = Http::withBasicAuth($secret, '')->timeout(10)->get("https://api.paymongo.com/v1/sources/{$sourceId}");
                Log::info('PayMongo source response', ['status_code' => $response->status(), 'ok' => $response->ok()]);
                if ($response->ok()) {
                    $body = $response->json();
                    $status = $body['data']['attributes']['status'] ?? null;
                    Log::info('PayMongo source status', ['status' => $status]);
                    // If PayMongo reports a paid/succeeded status, mark completed
                    if (in_array($status, ['paid', 'succeeded'])) {
                        DB::table('payment')->where('payment_id', $payment->payment_id)->update([
                            'payment_status' => 'Initial',
                            'updated_at' => now(),
                        ]);
                        DB::table('booking')->where('booking_ref_no', $bookingRef)->update([
                            'booking_status' => 'Confirmed',
                            'updated_at' => now(),
                        ]);

                        // Check if this is a cargo or passenger booking
                        $booking = DB::table('booking')->where('booking_ref_no', $bookingRef)->first();
                        if ($booking && strtolower($booking->booking_type ?? '') === 'cargo') {
                            // For staff-approved cargo bookings (booking_status already 'Confirmed'), don't send payment confirmation
                            // For user cargo bookings (booking_status was 'Pending'), send the confirmation
                            if (strtolower($booking->booking_status ?? '') !== 'confirmed') {
                                // Send cargo payment confirmation with Freight Receipt PDF
                                SendCargoPaymentConfirmationEmail::dispatch($bookingRef);
                                Log::info('CargoPaymentConfirmationEmail dispatched for booking: ' . $bookingRef);
                            }
                        } else {
                            // Send passenger ticket email
                            SendTicketEmail::dispatch($bookingRef);
                        }
                    } elseif (in_array($status, ['failed', 'canceled'])) {
                        // If payment failed or was canceled, update booking status
                        DB::table('payment')->where('payment_id', $payment->payment_id)->update([
                            'payment_status' => 'Canceled',
                            'updated_at' => now(),
                        ]);
                        DB::table('booking')->where('booking_ref_no', $bookingRef)->update([
                            'booking_status' => 'Canceled',
                            'updated_at' => now(),
                        ]);

                        // Get all passengers for this booking
                        $passengerIds = DB::table('passenger_ticket')
                            ->where('booking_ref_no', $bookingRef)
                            ->pluck('passenger_id')
                            ->toArray();

                        // Delete passengers if they only belong to this booking
                        foreach ($passengerIds as $passengerId) {
                            $otherBookings = DB::table('passenger_ticket')
                                ->where('passenger_id', $passengerId)
                                ->where('booking_ref_no', '!=', $bookingRef)
                                ->count();

                            if ($otherBookings == 0) {
                                DB::table('passenger')->where('passenger_id', $passengerId)->delete();
                            }
                        }

                        // Delete passenger tickets when payment fails
                        DB::table('passenger_ticket')->where('booking_ref_no', $bookingRef)->delete();
                    }
                    // If status is "chargeable", don't try to charge here - let webhook handle it
                    // Just return and let the user see the success/error in the redirect

                }
            } catch (\Exception $e) {
                Log::warning('PayMongo redirect verification failed: ' . $e->getMessage());
            }
        }

        // If payment succeeded, redirect to homepage with success message
        Log::info('PayMongo redirectReturn final check', ['status' => $status, 'is_paid_or_succeeded' => !empty($status) && in_array($status, ['paid', 'succeeded'])]);

        if (!empty($status) && in_array($status, ['paid', 'succeeded'])) {
            Log::info('Redirecting to homepage with success message', ['booking_ref_no' => $bookingRef]);
            return redirect()->route('homepage')->with('success', "Your booking is confirmed! Booking Reference: {$bookingRef}. Please check your email (including spam folder) for your ticket details.");
        }

        // If status is "chargeable", webhook will handle charging - show success message
        if (!empty($status) && $status === 'chargeable') {
            Log::info('Payment chargeable - waiting for webhook to charge', ['booking_ref_no' => $bookingRef]);

            // Check if cargo booking
            $booking = DB::table('booking')->where('booking_ref_no', $bookingRef)->first();
            $isCargo = $booking && strtolower($booking->booking_type ?? '') === 'cargo';

            // Update both payment and booking to mark as confirmed/completed
            DB::table('payment')->where('payment_id', $payment->payment_id)->update([
                'payment_status' => $isCargo ? 'Initial' : 'Completed',
                'updated_at' => now(),
            ]);
            DB::table('booking')->where('booking_ref_no', $bookingRef)->update([
                'booking_status' => 'Confirmed',
                'updated_at' => now(),
            ]);

            // Send ticket email immediately (backup - in case webhook is delayed)
            SendTicketEmail::dispatch($bookingRef);

            Log::info('Before redirect with success message', ['booking_ref_no' => $bookingRef]);
            $response = redirect()->route('homepage')->with('success', "Your booking is confirmed! Booking Reference: {$bookingRef}. Please check your email (including spam folder) for your ticket details.");
            Log::info('After redirect with success message', ['booking_ref_no' => $bookingRef]);
            return $response;
        }

        // Fallback: check DB in case the webhook already processed the payment
        // (source status becomes 'consumed' after webhook charges it, which falls through above)
        $freshPayment = DB::table('payment')->where('booking_ref_no', $bookingRef)->first();
        $freshBooking = DB::table('booking')->where('booking_ref_no', $bookingRef)->first();
        if (
            $freshPayment && strtolower($freshPayment->payment_status) === 'completed' &&
            $freshBooking && strtolower($freshBooking->booking_status) === 'confirmed'
        ) {
            Log::info('redirectReturn: payment already confirmed by webhook', ['booking_ref_no' => $bookingRef]);
            return redirect()->route('homepage')->with('success', "Your booking is confirmed! Booking Reference: {$bookingRef}. Please check your email (including spam folder) for your ticket details.");
        }

        // If payment failed or status unknown, redirect to homepage with error
        Log::info('Redirecting to homepage with error message', ['status' => $status, 'booking_ref_no' => $bookingRef]);
        return redirect()->route('homepage')->with('error', 'Payment could not be completed. Please try again.');
    }

    /**
     * Show cargo payment page
     */
    public function showCargoPayment($bookingRef)
    {
        // Get booking details
        $booking = DB::table('booking')->where('booking_ref_no', $bookingRef)->first();
        
        if (!$booking) {
            return redirect()->route('homepage')->with('error', 'Booking not found.');
        }

        // Check if it's a cargo booking
        if (strtolower($booking->booking_type ?? '') !== 'cargo') {
            return redirect()->route('homepage')->with('error', 'This payment page is for cargo bookings only.');
        }

        // Get payment record
        $payment = DB::table('payment')->where('booking_ref_no', $bookingRef)->first();
        
        if (!$payment) {
            return redirect()->route('homepage')->with('error', 'Payment record not found.');
        }

        // Get sender info
        $sender = DB::table('sender')->where('sender_id', $booking->sender_id)->first();
        
        // Get voyage info
        $voyage = DB::table('voyage')->where('voyage_id', $booking->voyage_id)->first();

        return view('payments.cargo_payment', [
            'booking' => $booking,
            'payment' => $payment,
            'sender' => $sender,
            'voyage' => $voyage,
        ]);
    }

    /**
     * Process cargo payment via PayMongo
     */
    public function processCargoPayment(Request $request, $bookingRef)
    {
        $payment = DB::table('payment')->where('booking_ref_no', $bookingRef)->first();
        
        if (!$payment) {
            return response()->json(['success' => false, 'message' => 'Payment record not found'], 404);
        }

        // Check if payment is already completed
        if (strtolower($payment->payment_status) === 'completed') {
            return response()->json(['success' => false, 'message' => 'Payment already completed'], 400);
        }

        $amountPhp = (float) $payment->total_amount;
        $amount = (int) round($amountPhp * 100); // cents/centavos

        $successUrl = url('/paymongo/return?booking_ref_no=' . $bookingRef);
        $failedUrl = url('/paymongo/failed?booking_ref_no=' . $bookingRef);

        // Get sender info for billing
        $booking = DB::table('booking')->where('booking_ref_no', $bookingRef)->first();
        $sender = DB::table('sender')->where('sender_id', $booking->sender_id ?? null)->first();

        $billingName = $sender->sender_name ?? 'Customer';
        $billingEmail = $sender->sender_email ?? 'no-reply+' . $bookingRef . '@example.invalid';

        $payload = [
            'data' => [
                'attributes' => [
                    'type' => 'gcash',
                    'amount' => $amount,
                    'currency' => 'PHP',
                    'redirect' => [
                        'success' => $successUrl,
                        'failed' => $failedUrl,
                    ],
                    'billing' => [
                        'email' => $billingEmail,
                        'name' => $billingName,
                    ],
                ],
            ],
        ];

        $secret = env('PAYMONGO_SECRET');
        if (!$secret) {
            return response()->json(['success' => false, 'message' => 'PayMongo secret not configured'], 500);
        }

        try {
            $response = Http::withBasicAuth($secret, '')->timeout(10)->post('https://api.paymongo.com/v1/sources', $payload);
            if ($response->failed()) {
                Log::error('PayMongo create source failed for cargo payment', ['status' => $response->status(), 'body' => $response->body()]);
                return response()->json(['success' => false, 'message' => 'Failed to create payment source'], 500);
            }

            $body = $response->json();
            $sourceId = $body['data']['id'] ?? null;
            $checkoutUrl = $body['data']['attributes']['redirect']['checkout_url'] ?? null;

            // Store source id in payment.transaction_code for later matching by webhook
            // Also update mode_of_payment to GCash for online payments
            if ($sourceId) {
                DB::table('payment')->where('payment_id', $payment->payment_id)->update([
                    'transaction_code' => $sourceId,
                    'mode_of_payment' => 'Gcash',
                    'updated_at' => now(),
                ]);
            }

            return response()->json(['success' => true, 'checkout_url' => $checkoutUrl]);
        } catch (\Exception $e) {
            Log::error('PayMongo create exception for cargo payment: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Exception creating payment source'], 500);
        }
    }
}
