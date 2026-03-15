<?php

namespace App\Http\Controllers;

use App\Jobs\SendTicketEmail;
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
            if ($sourceId) {
                DB::table('payment')->where('payment_id', $payment->payment_id)->update([
                    'transaction_code' => $sourceId,
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
                            DB::table('payment')->where('payment_id', $payment->payment_id)->update([
                                'payment_status' => 'Completed',
                                'updated_at' => now(),
                            ]);
                            DB::table('booking')->where('booking_ref_no', $payment->booking_ref_no)->update([
                                'booking_status' => 'Confirmed',
                                'updated_at' => now(),
                            ]);

                            // Send ticket email
                            SendTicketEmail::dispatch($payment->booking_ref_no);
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
                        DB::table('payment')->where('payment_id', $payment->payment_id)->update([
                            'payment_status' => 'Completed',
                            'updated_at' => now(),
                        ]);
                        DB::table('booking')->where('booking_ref_no', $payment->booking_ref_no)->update([
                            'booking_status' => 'Confirmed',
                            'updated_at' => now(),
                        ]);

                        // Send ticket email
                        SendTicketEmail::dispatch($payment->booking_ref_no);
                    } elseif (in_array($status, ['failed', 'canceled'])) {
                        DB::table('payment')->where('payment_id', $payment->payment_id)->update([
                            'payment_status' => 'Canceled',
                            'updated_at' => now(),
                        ]);
                        DB::table('booking')->where('booking_ref_no', $payment->booking_ref_no)->update([
                            'booking_status' => 'Canceled',
                            'updated_at' => now(),
                        ]);

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
                            'payment_status' => 'Completed',
                            'updated_at' => now(),
                        ]);
                        DB::table('booking')->where('booking_ref_no', $bookingRef)->update([
                            'booking_status' => 'Confirmed',
                            'updated_at' => now(),
                        ]);

                        // Send ticket email
                        SendTicketEmail::dispatch($bookingRef);
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
            return redirect()->route('homepage', ['payment_success' => $bookingRef])->with('success', "Payment successful! Your booking reference is: {$bookingRef}");
        }

        // If payment failed or status unknown, redirect to homepage with error
        Log::info('Redirecting to homepage with error message', ['status' => $status, 'booking_ref_no' => $bookingRef]);
        return redirect()->route('homepage', ['payment_error' => 1])->with('error', 'Payment could not be completed. Please try again.');
    }
}
