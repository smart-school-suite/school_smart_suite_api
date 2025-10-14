<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\SchoolEvent;

class AdminEventScheduledNotification extends Notification implements ShouldQueue
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
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Event Scheduled Successfully: ' . $this->schoolEvent->title)
            ->greeting("Hello {$notifiable->name},")
            ->line("Your school event {$this->schoolEvent->title} has been successfully scheduled to be published at {$this->schoolEvent->published_at->format('F j, Y, g:i A')}")
            ->line('📅 **Start Date:** ' . $this->schoolEvent->start_date->format('F j, Y, g:i A'))
            ->line('🏁 **End Date:** ' . $this->schoolEvent->end_date->format('F j, Y, g:i A'))
            ->line('📍 **Location:** ' . ($this->schoolEvent->location ?? 'Not specified'))
            ->action('View Event Details', url('/school-events/' . $this->schoolEvent->id))
            ->line('Thank you for organizing events with Smart School Suite!');
    }

    /**
     * Get the array representation of the notification for database storage.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'school_events',
            'title' => 'Event Scheduled Successfully',
            'description' => "Your school event {$this->schoolEvent->title} has been successfully scheduled to be published at {$this->schoolEvent->published_at->format('F j, Y, g:i A')}",
        ];
    }
}
