<?php

namespace App\Mail;

use App\Models\GeneralSetting;
use App\Models\Quotation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuotationRejectedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Quotation $quotation) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            to: [$this->quotation->customer_email],
            subject: 'Cotización Rechazada #' . $this->quotation->quotation_number,
        );
    }

    public function content(): Content
    {
        $settings = GeneralSetting::withoutGlobalScopes()
            ->where('tenant_id', $this->quotation->tenant_id)
            ->first();

        return new Content(
            view: 'emails.quotation-rejected',
            with: [
                'quotation' => $this->quotation->load('items'),
                'settings' => $settings,
            ],
        );
    }
}
