<?php

namespace App\Mail;

use App\Models\Cart;
use App\Models\GeneralSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AbandonedCartReminderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Cart $cart) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            to: [$this->cart->user->email],
            subject: 'Tienes productos esperando en tu carrito',
        );
    }

    public function content(): Content
    {
        $tenantId = $this->cart->tenant_id ?? app()->bound('current_tenant') ? app('current_tenant')?->id : null;

        $settings = GeneralSetting::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->first();

        return new Content(
            view: 'emails.abandoned-cart-reminder',
            with: [
                'cart' => $this->cart->load('items.product'),
                'settings' => $settings,
                'userName' => $this->cart->user->name,
            ],
        );
    }
}
