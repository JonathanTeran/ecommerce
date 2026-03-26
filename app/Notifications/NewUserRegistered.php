<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewUserRegistered extends Notification
{
    use Queueable;

    public function __construct(public User $newUser) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Nuevo usuario requiere aprobación')
            ->greeting('Hola ' . $notifiable->name)
            ->line("El usuario **{$this->newUser->name}** ({$this->newUser->email}) se ha registrado y requiere aprobación.")
            ->action('Revisar en el Panel', url('/admin/customers'))
            ->line('Aprueba o rechaza la cuenta desde el panel de administración.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'new_user_registered',
            'user_id' => $this->newUser->id,
            'user_name' => $this->newUser->name,
            'user_email' => $this->newUser->email,
            'message' => "Nuevo registro: {$this->newUser->name} ({$this->newUser->email}) requiere aprobación.",
        ];
    }
}
