<?php

namespace App\Notifications\Specialty;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class SpecialtyDeactivatedNotification extends Notification
{
    use Queueable;

    public $tries = 3;
    public $backoff = [60, 300, 600];

    protected $specialtyDetails;

    /**
     * Create a new notification instance.
     */
    public function __construct($specialtyDetails)
    {
        $this->specialtyDetails = $specialtyDetails;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Specialty Deactivated')
            ->greeting("Hello Admin, {$notifiable->name}")
            ->line("The specialty **{$this->specialtyDetails['specialty_name']}** has been deactivated.")
            ->line('This specialty is no longer available for assignments or usage in the system.')
            ->action('Manage Specialties', url('/specialties'))
            ->line('Thank you for using Smart School Suite!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Specialty Deactivated',
            'body' => "The specialty {$this->specialtyDetails['specialty_name']} has been deactivated.",
        ];
    }

    /**
     * Broadcast message payload.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'Specialty Deactivated',
            'body' => "The specialty {$this->specialtyDetails['specialty_name']} has been deactivated.",
        ]);
    }
}
