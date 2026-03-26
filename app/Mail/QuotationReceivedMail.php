<?php

namespace App\Mail;

use App\Models\GeneralSetting;
use App\Models\Quotation;
use App\Services\QuotationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuotationReceivedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Quotation $quotation) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            to: [$this->quotation->customer_email],
            subject: 'Cotización Recibida #' . $this->quotation->quotation_number,
        );
    }

    public function content(): Content
    {
        $settings = GeneralSetting::withoutGlobalScopes()
            ->where('tenant_id', $this->quotation->tenant_id)
            ->first();

        return new Content(
            view: 'emails.quotation-received',
            with: [
                'quotation' => $this->quotation->load('items'),
                'settings' => $settings,
            ],
        );
    }

    /**
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $pdf = app(QuotationService::class)->generatePdf($this->quotation);
        $filename = 'cotizacion-' . $this->quotation->quotation_number . '.pdf';

        return [
            \Illuminate\Mail\Mailables\Attachment::fromData(
                fn () => $pdf->output(),
                $filename
            )->withMime('application/pdf'),
        ];
    }
}
