<?php

namespace App\Notifications;

use App\Models\Announcement;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ScheduledAnnouncementNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Announcement $announcement;

    public $tries = 3;

    public $backoff = [60, 300, 600];

    public function __construct(Announcement $announcement)
    {
        $this->announcement = $announcement;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $publishTime = $this->announcement->published_at->format('l, F j, Y g:i A');

        return (new MailMessage)
            ->subject('Announcement Scheduled')
            ->greeting("Hello Admin {$notifiable->name},")
            ->line('An announcement has been successfully scheduled.')
            ->line("{$this->announcement->title}")
            ->line("**Scheduled Publish Time:** $publishTime")
            ->line('You can cancel this announcement anytime before it is published.')
            ->line('We will remind you again shortly before it goes live.')
            ->action('Manage Announcement', url("/announcements/{$this->announcement->id}"))
            ->line('Thank you for using our platform.');
    }

public function toArray(object $notifiable): array
{
    return [
        'type' => 'scheduled_announcement',
        'title' => $this->announcement->title,
        'body' => "An announcement titled {$this->announcement->title} has been scheduled to be published on " .
                  $this->announcement->published_at->format('M j Y h:i A') .
                  ". You can cancel it before the scheduled time, and we will remind you again shortly before publishing.",
    ];
}

}
