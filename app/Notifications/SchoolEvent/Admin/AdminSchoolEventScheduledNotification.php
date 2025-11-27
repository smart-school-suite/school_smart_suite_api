<?php

namespace App\Notifications\SchoolEvent\Admin;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\SchoolEvent;
use Carbon\Carbon;
class AdminSchoolEventScheduledNotification extends Notification
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
        // The SchoolEvent model should have 'published_at', 'start_date', and 'end_date' in its $casts array.
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
        $publishedAtRaw = $this->schoolEvent->published_at;
        $publishedAt = $publishedAtRaw
            ? Carbon::parse($publishedAtRaw)->format('F j, Y, g:i A')
            : 'Immediately';

        $startDateRaw = $this->schoolEvent->start_date;
        $startDate = $startDateRaw
            ? Carbon::parse($startDateRaw)->format('F j, Y, g:i A')
            : 'N/A';

        $endDateRaw = $this->schoolEvent->end_date;
        $endDate = $endDateRaw
            ? Carbon::parse($endDateRaw)->format('F j, Y, g:i A')
            : 'N/A';

        return (new MailMessage)
            ->subject('Event Scheduled Successfully: ' . $this->schoolEvent->title)
            ->greeting("Hello {$notifiable->name},")
            ->line("Your school event **{$this->schoolEvent->title}** has been successfully scheduled to be published at **{$publishedAt}**.")

            ->line('📅 **Start Date:** ' . $startDate)
            ->line('🏁 **End Date:** ' . $endDate)
            ->line('📍 **Location:** ' . ($this->schoolEvent->location ?? 'Not specified'))

            ->action('View Event Details', url('/school-events/' . $this->schoolEvent->id))
            ->line('Thank you for organizing events with Smart School Suite!');
    }

    /**
     * Get the array representation of the notification for database storage.
     */
    public function toArray(object $notifiable): array
    {
        $publishedAtRaw = $this->schoolEvent->published_at;
        $publishedAt = $publishedAtRaw
            ? Carbon::parse($publishedAtRaw)->format('F j, Y, g:i A')
            : 'N/A';

        return [
            'type' => 'school_events',
            'title' => 'Event Scheduled Successfully',
            'body' => "Your school event {$this->schoolEvent->title} has been successfully scheduled to be published at {$publishedAt}.",
        ];
    }
}
