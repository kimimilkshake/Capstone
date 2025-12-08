<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Jobs\SendSemaphoreSmsJob;
use App\Models\Passenger;
use App\Models\Booking;
use App\Models\CargoReceipt;
use App\Models\Sender;
use Illuminate\Support\Facades\DB;

class SemaphoreController extends Controller
{
    private const CHUNK_SIZE = 100;
    
    public function show($voyage)
    {
        return view('authorized.staff.semaphore', ['voyage_id' => $voyage]);
    }

    public function send(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:640',
            'voyage_id' => 'required|exists:voyage,voyage_id',
        ]);

        $numbers = $this->getRecipientNumbers($request->voyage_id);

        if (empty($numbers)) {
            return back()->withErrors(['recipients' => 'No recipients found.']);
        }

        $this->dispatchMessages($numbers, $request->message);

        return back()->with('status', 'Messages queued for sending (' . count($numbers) . ' recipients).');
    }

    private function getRecipientNumbers(int $voyageId): array
    {
        $passengerNumbers = $this->getPassengerNumbers($voyageId);
        $cargoNumbers = $this->getCargoSenderNumbers($voyageId);
        
        return collect($passengerNumbers)
            ->merge($cargoNumbers)
            ->map(fn($contact) => $this->normalizePhoneNumber($contact))
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }

    private function getPassengerNumbers(int $voyageId): array
    {
        return Booking::where('voyage_id', $voyageId)
            ->where('booking_type', 'passenger')
            ->join('passenger', 'booking.passenger_id', '=', 'passenger.passenger_id')
            ->whereNotNull('passenger.passenger_contactno')
            ->pluck('passenger.passenger_contactno')
            ->toArray();
    }

    private function getCargoSenderNumbers(int $voyageId): array
    {
        return CargoReceipt::where('voyage_id', $voyageId)
            ->join('sender', 'cargo_receipt.sender_id', '=', 'sender.sender_id')
            ->whereNotNull('sender.sender_contactno')
            ->pluck('sender.sender_contactno')
            ->toArray();
    }

    private function normalizePhoneNumber(string $contact): ?string
    {
        $number = preg_replace('/\D+/', '', $contact);
        
        if (strlen($number) === 10 && str_starts_with($number, '9')) {
            $number = '63' . $number;
        }
        
        return $number ?: null;
    }

    private function dispatchMessages(array $numbers, string $message): void
    {
        foreach (array_chunk($numbers, self::CHUNK_SIZE) as $chunk) {
            SendSemaphoreSmsJob::dispatch($chunk, $message);
        }
    }
}
