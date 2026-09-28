<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendWaNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $targetPhone;
    protected $message;

    public function __construct($targetPhone, $message)
    {
        $this->targetPhone = $targetPhone;
        $this->message = $message;
    }

    public function handle(): void
    {
        $waEnv  = env('WA_ENV', 'development');
        $apiKey = env('BABLAST_API_KEY');

        if ($waEnv === 'development') {
            $devPhone = env('WA_DEV_PHONE');
            if (empty($devPhone)) {
                Log::warning('Bablast [Sitaksi]: WA_DEV_PHONE not set. Skipping.');
                return;
            }
            $finalPhone = $devPhone;
            Log::info("Bablast [Sitaksi] Sandbox: rerouting from {$this->targetPhone} to {$finalPhone}");
        } else {
            $finalPhone = $this->targetPhone;
        }

        if (empty($apiKey) || empty($finalPhone)) {
            Log::error('Bablast [Sitaksi]: API Key or Phone missing.');
            return;
        }

        $formattedPhone = preg_replace('/[^0-9]/', '', $finalPhone);
        if (str_starts_with($formattedPhone, '0')) {
            $formattedPhone = '62' . substr($formattedPhone, 1);
        }

        try {
            $response = Http::withToken($apiKey)
                ->post('https://api.bablast.id/send', [
                    'phone'   => $formattedPhone,
                    'message' => $this->message,
                ]);

            if ($response->successful()) {
                Log::info("Bablast [Sitaksi]: Sent to {$finalPhone}");
            } else {
                Log::error("Bablast [Sitaksi]: Failed to {$finalPhone}. " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("Bablast [Sitaksi]: Exception: " . $e->getMessage());
        }
    }
}
