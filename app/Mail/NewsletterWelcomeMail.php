<?php

namespace App\Mail;

use App\Models\GeneralSetting;
use App\Models\NewsletterSubscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewsletterWelcomeMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public NewsletterSubscriber $subscriber) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            to: [$this->subscriber->email],
            subject: 'Bienvenido a nuestro Newsletter',
        );
    }

    public function content(): Content
    {
        $settings = GeneralSetting::withoutGlobalScopes()
            ->where('tenant_id', $this->subscriber->tenant_id)
            ->first();

        return new Content(
            view: 'emails.newsletter-welcome',
            with: [
                'subscriber' => $this->subscriber,
                'settings' => $settings,
            ],
        );
    }
}
