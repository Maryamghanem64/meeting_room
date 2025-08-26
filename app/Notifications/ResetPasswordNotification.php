<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends Notification
{
    public string $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {

        $frontendUrl = config('app.frontend_url', 'http://localhost:5173');

        $url = "{$frontendUrl}/reset-password/{$this->token}?email={$notifiable->getEmailForPasswordReset()}";

        return (new MailMessage)
            ->subject('Reset Password Request')
            ->line('Click the button below to reset your password.')
            ->action('Reset Password', $url)
            ->line('If you did not request this, no further action is required.');
    }
}
