<?php

namespace App\Console\Commands;

use App\Mail\AbandonedCartReminderMail;
use App\Models\Cart;
use App\Services\TenantMailService;
use Illuminate\Console\Command;

class SendAbandonedCartReminders extends Command
{
    protected $signature = 'carts:send-abandoned-reminders
                            {--hours=2 : Hours of inactivity before sending reminder}
                            {--max-reminders=2 : Maximum reminders per cart}';

    protected $description = 'Send email reminders for abandoned carts';

    public function handle(): int
    {
        $hours = (int) $this->option('hours');
        $maxReminders = (int) $this->option('max-reminders');

        $carts = Cart::query()
            ->whereNotNull('user_id')
            ->whereHas('items')
            ->where('updated_at', '<=', now()->subHours($hours))
            ->where('reminder_count', '<', $maxReminders)
            ->where(function ($query) use ($hours) {
                $query->whereNull('reminder_sent_at')
                    ->orWhere('reminder_sent_at', '<=', now()->subHours(24));
            })
            ->with(['user', 'items.product'])
            ->get();

        $sent = 0;

        foreach ($carts as $cart) {
            if (! $cart->user || ! $cart->user->email) {
                continue;
            }

            if ($cart->items->isEmpty()) {
                continue;
            }

            try {
                app(TenantMailService::class)->send(new AbandonedCartReminderMail($cart));

                $cart->update([
                    'reminder_sent_at' => now(),
                    'reminder_count' => $cart->reminder_count + 1,
                ]);

                $sent++;
            } catch (\Exception $e) {
                $this->error("Failed to send reminder for cart #{$cart->id}: {$e->getMessage()}");
            }
        }

        $this->info("Sent {$sent} abandoned cart reminders.");

        return self::SUCCESS;
    }
}
