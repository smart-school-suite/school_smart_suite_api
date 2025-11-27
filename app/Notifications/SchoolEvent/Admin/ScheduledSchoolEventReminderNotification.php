<?php

namespace App\Notifications\SchoolEvent\Admin;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\SchoolEvent;
class ScheduledSchoolEventReminderNotification extends Notification
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
        $publishTime = $this->schoolEvent->published_at?->format('F j, Y, g:i A');

        return (new MailMessage)
            ->subject('Reminder: Your Scheduled Event Will Be Published Soon')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line("This is a reminder that your scheduled event **{$this->schoolEvent->title}** is set to be published soon.")
            ->when($publishTime, fn($mail) => $mail->line("🕒 **Scheduled Publish Time:** {$publishTime}"))
            ->line('If you need to make any changes, you can still edit your event before it goes live.')
            ->action('Review Event Details', url('/school-events/' . $this->schoolEvent->id))
            ->line('Thank you for organizing your events with Smart School Suite!');
    }

    /**
     * Get the array representation of the notification for database storage.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Scheduled Event Reminder',
            'description' => "Your event '{$this->schoolEvent->title}' is scheduled to be published soon.",
            'event_id' => $this->schoolEvent->id,
        ];
    }
}
