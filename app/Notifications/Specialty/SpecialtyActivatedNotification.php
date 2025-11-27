<?php

namespace App\Notifications\Specialty;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class SpecialtyActivatedNotification extends Notification
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
            ->subject('Specialty Activated')
            ->greeting("Hello Admin, {$notifiable->name}")
            ->line("The specialty **{$this->specialtyDetails['specialty_name']}** has been successfully activated.")
            ->line('You can now manage this specialty and related operations.')
            ->action('View Specialty', url('/specialties/' . $this->specialtyDetails['specialty_id']))
            ->line('Thank you for using our application!');
    }

    /**
     * Storeable representation for database notifications.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Specialty Activated',
            'body' => "The specialty {$this->specialtyDetails['specialty_name']} has been activated.",
        ];
    }

    /**
     * Broadcast message format.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'Specialty Activated',
            'body' => "The specialty {$this->specialtyDetails['specialty_name']} has been activated.",
        ]);
    }
}
