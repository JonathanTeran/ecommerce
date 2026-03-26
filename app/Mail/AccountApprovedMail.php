<?php

namespace App\Mail;

use App\Models\GeneralSetting;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountApprovedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public User $user) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            to: [$this->user->email],
            subject: 'Tu cuenta ha sido aprobada',
        );
    }

    public function content(): Content
    {
        $settings = GeneralSetting::withoutGlobalScopes()
            ->where('tenant_id', $this->user->tenant_id)
            ->first();

        return new Content(
            view: 'emails.account-approved',
            with: [
                'user' => $this->user,
                'settings' => $settings,
            ],
        );
    }
}
