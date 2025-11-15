<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

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
            $response = Http::withBasicAuth($secret, '')->post('https://api.paymongo.com/v1/sources', $payload);
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
        $payload = $request->all();
        \Log::info('PayMongo webhook', $payload);

        $type = $payload['type'] ?? null;
        $data = $payload['data'] ?? null;

        // Try to extract a source id from payload to match our payment.transaction_code
        $sourceId = null;
        if (isset($data['id'])) {
            $sourceId = $data['id'];
        }
        // Some events include source inside attributes
        if (!$sourceId && isset($data['attributes']['source']['id'])) {
            $sourceId = $data['attributes']['source']['id'];
        }

        // If event indicates payment succeeded, update our DB
        if ($type && str_contains($type, 'payment') && isset($data['attributes']['status'])) {
            $status = $data['attributes']['status'];
            if ($status === 'paid' || $status === 'succeeded') {
                // find payment by transaction_code (source id)
                if ($sourceId) {
                    $payment = DB::table('payment')->where('transaction_code', $sourceId)->first();
                    if ($payment) {
                        DB::table('payment')->where('payment_id', $payment->payment_id)->update([
                            'payment_status' => 'Completed',
                            'updated_at' => now(),
                        ]);

                        DB::table('booking')->where('booking_ref_no', $payment->booking_ref_no)->update([
                            'booking_status' => 'Confirmed',
                            'updated_at' => now(),
                        ]);
                    }
                }
            } elseif ($status === 'failed' || $status === 'canceled') {
                if ($sourceId) {
                    $payment = DB::table('payment')->where('transaction_code', $sourceId)->first();
                    if ($payment) {
                        DB::table('payment')->where('payment_id', $payment->payment_id)->update([
                            'payment_status' => 'Canceled',
                            'updated_at' => now(),
                        ]);
                        DB::table('booking')->where('booking_ref_no', $payment->booking_ref_no)->update([
                            'booking_status' => 'Canceled',
                            'updated_at' => now(),
                        ]);
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
        if (!$bookingRef) {
            return redirect()->route('homepage');
        }

        // Try to verify payment status immediately by querying PayMongo using stored transaction_code (source id)
        $payment = DB::table('payment')->where('booking_ref_no', $bookingRef)->first();
        $secret = env('PAYMONGO_SECRET');
        if ($payment && $payment->transaction_code && $secret) {
            try {
                $sourceId = $payment->transaction_code;
                $response = Http::withBasicAuth($secret, '')->get("https://api.paymongo.com/v1/sources/{$sourceId}");
                if ($response->ok()) {
                    $body = $response->json();
                    $status = $body['data']['attributes']['status'] ?? null;
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
                    }
                }
            } catch (\Exception $e) {
                \Log::warning('PayMongo redirect verification failed: ' . $e->getMessage());
            }
        }

        // If payment succeeded, show a short success prompt then redirect to homepage.
        if (!empty($status) && in_array($status, ['paid', 'succeeded'])) {
            return view('payments.success', ['bookingRef' => $bookingRef]);
        }

        // Otherwise redirect to confirmbooking which will show updated booking/payment status
        return redirect()->to(url('/passenger/confirmbooking/' . $bookingRef));
    }
}
