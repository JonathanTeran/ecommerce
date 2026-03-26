<?php

namespace App\Notifications;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Order $order,
        public OrderStatus $oldStatus,
        public OrderStatus $newStatus,
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
        return (new MailMessage)
            ->subject("Pedido {$this->order->order_number} - Estado actualizado")
            ->greeting("Hola {$notifiable->first_name},")
            ->line("El estado de tu pedido **{$this->order->order_number}** ha cambiado.")
            ->line("Estado anterior: **{$this->oldStatus->getLabel()}**")
            ->line("Nuevo estado: **{$this->newStatus->getLabel()}**")
            ->action('Ver Pedido', url("/account/orders/{$this->order->id}"))
            ->line('Gracias por tu compra.');
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'old_status' => $this->oldStatus->value,
            'old_status_label' => $this->oldStatus->getLabel(),
            'new_status' => $this->newStatus->value,
            'new_status_label' => $this->newStatus->getLabel(),
            'message' => "Tu pedido {$this->order->order_number} cambió de {$this->oldStatus->getLabel()} a {$this->newStatus->getLabel()}.",
        ];
    }
}
