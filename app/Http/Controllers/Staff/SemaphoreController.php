<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Jobs\SendSemaphoreSmsJob;
use App\Models\Passenger; // use your Passenger model

class SemaphoreController extends Controller
{
    public function show()
    {
        return view('authorized.staff.semaphore');
    }

    public function send(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:640',
        ]);

        $message = $request->input('message');

        // Fetch recipients from passengers table using passenger_contactno
        $numbers = Passenger::whereNotNull('passenger_contactno')
            ->pluck('passenger_contactno')
            ->map(function ($p) {
                // Normalize: remove non-digit characters and possible leading zeros/plus handling
                $n = preg_replace('/\D+/', '', $p);
                // Optional: if numbers are local and missing country code, add it here
                // Example for Philippines: if length == 10 and starts with 9 -> prepend 63
                if (strlen($n) === 10 && preg_match('/^9\d+$/', $n)) {
                    $n = '63' . $n;
                }
                return $n;
            })
            ->filter()    // drop empty
            ->unique()    // remove duplicates
            ->values()
            ->toArray();

        if (count($numbers) === 0) {
            return back()->withErrors(['recipients' => 'No recipients found for this action.']);
        }

        // Chunk and dispatch queued jobs (tune chunkSize for your Semaphore limits)
        $chunkSize = 100;
        foreach (array_chunk($numbers, $chunkSize) as $chunk) {
            SendSemaphoreSmsJob::dispatch($chunk, $message);
        }

        return back()->with('status', 'Messages queued for sending (' . count($numbers) . ' recipients).');
    }
}
