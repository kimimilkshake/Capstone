<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendSemaphoreSmsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $numbers;
    public $message;

    public $tries = 3;

    public function __construct(array $numbers, string $message)
    {
        $this->numbers = $numbers;
        $this->message = $message;
    }

    public function handle()
    {
        $apiKey   = SEMAPHORE_API_KEY;
        $sender   = config('services.semaphore.sender');
        $endpoint = config('services.semaphore.endpoint');

        foreach ($this->numbers as $number) {
            $payload = [
                'apikey'  => $apiKey,
                'number'  => $number,
                'message' => $this->message,
                'sender'  => $sender,
            ];

            $response = Http::asForm()->post($endpoint, $payload);

            if (! $response->successful()) {
                Log::warning('Semaphore SMS failed', [
                    'number' => $number,
                    'status' => $response->status(),
                    'body'   => (string) $response->body(),
                ]);
            } else {
                // Optionally log success and response message id for tracking
                Log::info('Semaphore SMS sent', [
                    'number' => $number,
                    'body'   => $response->body(),
                ]);
            }

            // Throttle between messages — tune according to API limits
            usleep(200000); // 200ms pause
        }
    }
}
