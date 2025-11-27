<?php

namespace App\Notifications\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OTPNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public $tries = 3;

    public $backoff = [60, 300, 600];
    protected $OTP;
    public function __construct($OTP)
    {
        $this->OTP = $OTP;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Smart School Suite Verification Code')
            ->greeting('Hello!')
            ->line('Use the verification code below to complete your login or account verification.')
            ->line('**Your OTP Code:**')
            ->line('# ' . $this->OTP)
            ->line('This code will expire in 10 minutes for security reasons.')
            ->line('If you did not request this code, please ignore this email.')
            ->salutation('Regards,  Smart School Suite Team');
    }
}
