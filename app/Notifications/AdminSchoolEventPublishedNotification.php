<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\SchoolEvent;
use Carbon\Carbon;
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
        $startDateRaw = $this->schoolEvent->start_date;
        $startDate = $startDateRaw
            ? Carbon::parse($startDateRaw)->format('F j, Y, g:i A')
            : 'N/A';

        $endDateRaw = $this->schoolEvent->end_date;
        $endDate = $endDateRaw
            ? Carbon::parse($endDateRaw)->format('F j, Y, g:i A')
            : 'N/A';

        return (new MailMessage)
            ->subject('Your Event is Now Published: ' . $this->schoolEvent->title)
            ->greeting("Hello {$notifiable->name},")
            ->line("Your event **{$this->schoolEvent->title}** has been successfully published and is now visible to all invitees.")

            ->line('📅 **Start Date:** ' . $startDate)
            ->line('🏁 **End Date:** ' . $endDate)

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
            'type' => 'school_events',
            'title' => 'Event Published Successfully',
            'body' => "Your event '{$this->schoolEvent->title}' is now published and visible to all invitees.",
        ];
    }
}
