<?php

namespace App\Services;

use App\Jobs\DispatchWebhook;
use App\Models\Webhook;

class WebhookService
{
    public function dispatch(string $event, array $payload): void
    {
        $tenant = app()->bound('current_tenant') ? app('current_tenant') : null;

        if (! $tenant) {
            return;
        }

        $webhooks = Webhook::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->get()
            ->filter(fn (Webhook $webhook) => $webhook->subscribesToEvent($event));

        foreach ($webhooks as $webhook) {
            DispatchWebhook::dispatch($webhook, $event, $payload);
        }
    }
}
