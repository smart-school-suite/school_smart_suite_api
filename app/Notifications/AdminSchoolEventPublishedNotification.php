<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\SchoolEvent;

class AdminSchoolEventPublishedNotification extends Notification implements ShouldQueue
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
            ->subject('Your Event is Now Published: ' . $this->schoolEvent->title)
            ->greeting("Hello {$notifiable->name}")
            ->line("Your event **{$this->schoolEvent->title}** has been successfully published and is now visible to all invitees.")
            ->line('📅 **Start Date:** ' . $this->schoolEvent->start_date->format('F j, Y, g:i A'))
            ->line('🏁 **End Date:** ' . $this->schoolEvent->end_date->format('F j, Y, g:i A'))
            ->line('📍 **Location:** ' . ($this->schoolEvent->location ?? 'Not specified'))
            ->action('View Published Event', url('/school-events/' . $this->schoolEvent->id))
            ->line('Thank you for organizing events with Smart School Suite!');
    }

    /**
     * Get the array representation of the notification for database storage.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Event Published Successfully',
            'description' => "Your event '{$this->schoolEvent->title}' is now published and visible to all invitees.",
            'event_id' => $this->schoolEvent->id,
        ];
    }
}
