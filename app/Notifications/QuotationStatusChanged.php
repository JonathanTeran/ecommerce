<?php

namespace App\Notifications;

use App\Enums\QuotationStatus;
use App\Models\Quotation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class QuotationStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Quotation $quotation,
        public QuotationStatus $oldStatus,
        public QuotationStatus $newStatus,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $statusLabel = $this->newStatus === QuotationStatus::Approved ? 'aprobada' : 'rechazada';

        $mail = (new MailMessage)
            ->subject("Cotización {$this->quotation->quotation_number} - {$statusLabel}")
            ->greeting("Hola {$notifiable->first_name},")
            ->line("Tu cotización **{$this->quotation->quotation_number}** ha sido **{$statusLabel}**.");

        if ($this->newStatus === QuotationStatus::Rejected && $this->quotation->rejection_reason) {
            $mail->line("Motivo: {$this->quotation->rejection_reason}");
        }

        if ($this->newStatus === QuotationStatus::Approved) {
            $mail->line("Total: {$this->quotation->formatted_total}")
                ->line("Válida hasta: {$this->quotation->valid_until?->format('d/m/Y')}");
        }

        return $mail->action('Ver Cotización', url("/account/quotations/{$this->quotation->id}"))
            ->line('Gracias por tu preferencia.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $statusLabel = $this->newStatus === QuotationStatus::Approved ? 'aprobada' : 'rechazada';

        return [
            'quotation_id' => $this->quotation->id,
            'quotation_number' => $this->quotation->quotation_number,
            'old_status' => $this->oldStatus->value,
            'old_status_label' => $this->oldStatus->getLabel(),
            'new_status' => $this->newStatus->value,
            'new_status_label' => $this->newStatus->getLabel(),
            'message' => "Tu cotización {$this->quotation->quotation_number} ha sido {$statusLabel}.",
        ];
    }
}
