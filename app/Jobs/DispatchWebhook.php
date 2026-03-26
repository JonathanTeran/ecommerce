<?php

namespace App\Jobs;

use App\Models\Webhook;
use App\Models\WebhookDelivery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class DispatchWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /** @var array<int, int> */
    public array $backoff = [10, 60, 300];

    public function __construct(
        public Webhook $webhook,
        public string $event,
        public array $payload,
    ) {}

    public function handle(): void
    {
        $payloadJson = json_encode([
            'event' => $this->event,
            'timestamp' => now()->toIso8601String(),
            'data' => $this->payload,
        ]);

        $signature = hash_hmac('sha256', $payloadJson, $this->webhook->secret ?? '');

        $delivery = WebhookDelivery::create([
            'webhook_id' => $this->webhook->id,
            'event' => $this->event,
            'payload' => json_decode($payloadJson, true),
            'attempts' => $this->attempts(),
        ]);

        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-Webhook-Signature' => "sha256={$signature}",
                    'X-Webhook-Event' => $this->event,
                    'User-Agent' => 'Amephia-Webhook/1.0',
                ])
                ->withBody($payloadJson, 'application/json')
                ->post($this->webhook->url);

            $delivery->update([
                'response_status_code' => $response->status(),
                'response_body' => mb_substr($response->body(), 0, 5000),
                'attempts' => $this->attempts(),
                'delivered_at' => $response->successful() ? now() : null,
                'failed_at' => $response->successful() ? null : now(),
            ]);

            if (! $response->successful()) {
                $this->fail(new \RuntimeException("Webhook returned {$response->status()}"));
            }
        } catch (\Exception $e) {
            $delivery->update([
                'response_body' => mb_substr($e->getMessage(), 0, 5000),
                'attempts' => $this->attempts(),
                'failed_at' => now(),
            ]);

            throw $e;
        }
    }
}
