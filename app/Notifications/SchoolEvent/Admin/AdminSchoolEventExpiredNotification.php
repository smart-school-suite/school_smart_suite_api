<?php

namespace App\Notifications\SchoolEvent\Admin;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\SchoolEvent;
class AdminSchoolEventExpiredNotification extends Notification
{
    use Queueable;

    protected SchoolEvent $schoolEvent;

    public $tries = 3;
    public $backoff = [60, 300, 600];

    /**
     * Create a new notification instance.
     */
    public function __construct(SchoolEvent $schoolEvent)
    {
        $this->schoolEvent = $schoolEvent;
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
            ->subject('Your Event Has Expired: ' . $this->schoolEvent->title)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line("Your event **{$this->schoolEvent->title}** has officially ended and is now marked as expired.")
            ->line('📅 **Event Period:** ' . $this->schoolEvent->start_date->format('F j, Y, g:i A') . ' — ' . $this->schoolEvent->end_date->format('F j, Y, g:i A'))
            ->line('We hope your event was a success! You can still access event insights, feedback, and attendance details.')
            ->action('View Event Summary', url('/school-events/' . $this->schoolEvent->id))
            ->line('Thank you for hosting events with Smart School Suite.');
    }

    /**
     * Get the array representation of the notification for database storage.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Event Expired',
            'description' => "Your event '{$this->schoolEvent->title}' has ended and is now marked as expired.",
            'event_id' => $this->schoolEvent->id,
        ];
    }
}
