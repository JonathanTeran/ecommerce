<?php

namespace App\Mail;

use App\Models\GeneralSetting;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderCancelledMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            to: [$this->order->user->email],
            subject: 'Tu Pedido #'.$this->order->order_number.' ha sido cancelado',
        );
    }

    public function content(): Content
    {
        $settings = GeneralSetting::withoutGlobalScopes()
            ->where('tenant_id', $this->order->tenant_id)
            ->first();

        return new Content(
            view: 'emails.order-cancelled',
            with: [
                'order' => $this->order,
                'settings' => $settings,
            ],
        );
    }
}
