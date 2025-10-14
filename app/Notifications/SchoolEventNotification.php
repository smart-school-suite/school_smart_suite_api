<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use App\Models\SchoolEvent;
use Carbon\Carbon;
class SchoolEventNotification extends Notification
{
    use Queueable;

    public $tries = 3;
    public $backoff = [60, 300, 600];
    protected SchoolEvent $schoolEvent;
    public function __construct(SchoolEvent $schoolEvent)
    {
        $this->schoolEvent = $schoolEvent;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
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
        $event = $this->schoolEvent;

        $start = Carbon::parse($event->start_date)->format('l, F j, Y g:i A');
        $end = $event->end_date ? Carbon::parse($event->end_date)->format('l, F j, Y g:i A') : null;

        return (new MailMessage)
            ->subject("🎉 {$event->title} — Upcoming School Event")
            ->greeting("Hello {$notifiable->name},")
            ->line("You're invited to {$event->title}, organized by {$event->organizer}")
            ->line("📍 Location: {$event->location}")
            ->line("🗓️ Date: {$start}" . ($end ? " to {$end}" : ''))
            ->line($event->description)
            ->action('View Event Details', "/events")
            ->line('We look forward to seeing you there!')
            ->salutation('— Smart School Suite Team');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'New Event Avialable',
            'body' => "You're invited to {$this->schoolEvent->title}, organized by {$this->schoolEvent->organizer}",
        ]);
    }
}
