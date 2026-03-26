<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\SriService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckSriAuthorizationStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $maxAttempts = 3;

    public int $delaySeconds = 120;

    public function __construct(public int $orderId, public int $attempt = 1) {}

    public function handle(SriService $sriService): void
    {
        $order = Order::query()->find($this->orderId);

        if (! $order || ! $order->sri_access_key) {
            return;
        }

        $authResponse = $sriService->authorize($order->sri_access_key);

        if ($authResponse['status'] === 'AUTORIZADO') {
            $update = [
                'sri_authorization_status' => 'authorized',
                'sri_authorization_date' => $authResponse['date'] ?? now(),
                'sri_authorization_number' => $authResponse['authorization_number'],
                'sri_error_message' => null,
            ];

            if (! empty($authResponse['xml'])) {
                $update['sri_authorized_xml_path'] = $sriService->storeAuthorizedXml($order, $authResponse['xml']);
            }

            $order->update($update);

            return;
        }

        if ($authResponse['status'] === 'NO AUTORIZADO') {
            $message = ($authResponse['source'] ?? null) === 'system'
                ? 'SISTEMA: '.$authResponse['message']
                : 'SRI: '.$authResponse['message'];

            $order->update([
                'sri_authorization_status' => 'rejected',
                'sri_error_message' => $message,
            ]);

            return;
        }

        $message = ($authResponse['source'] ?? null) === 'system'
            ? 'SISTEMA: '.$authResponse['message']
            : 'SRI: '.$authResponse['message'];

        $order->update([
            'sri_authorization_status' => 'pending',
            'sri_error_message' => $message,
        ]);

        $maxAttempts = (int) config('sri.authorization_retry_attempts', $this->maxAttempts);
        $delaySeconds = (int) config('sri.authorization_retry_delay', $this->delaySeconds);

        if ($this->attempt >= $maxAttempts) {
            return;
        }

        self::dispatch($order->id, $this->attempt + 1)
            ->delay(now()->addSeconds($delaySeconds));
    }
}
