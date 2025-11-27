<?php

namespace App\Notifications\Announcement\Admin;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Carbon\Carbon;
use App\Models\Announcement;

class AdminAnnouncementScheduledReminderNotification extends Notification
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
        $now = Carbon::now();
        $publishTime = $this->announcement->published_at;
        $minutesLeft = $now->diffInMinutes($publishTime, false);

        $reminderText = $minutesLeft >= 30
            ? 'in 30 minutes'
            : 'in 5 minutes';

        return (new MailMessage)
            ->subject('Reminder: Upcoming Announcement Publication')
            ->greeting("Hello {$notifiable->name},")
            ->line("This is a reminder that your scheduled announcement will be published {$reminderText}.")
            ->line("Title:{$this->announcement->title}")
            ->line("Publish Time: {$publishTime->format('M j Y h:i A')}")
            ->action('Manage Announcement', url("/announcements/{$this->announcement->id}"))
            ->line('You may cancel the announcement before it is published if necessary.');
    }

    public function toArray(object $notifiable): array
    {
        $now = Carbon::now();
        $publishTime = $this->announcement->published_at;
        $minutesLeft = $now->diffInMinutes($publishTime, false);

        $reminderText = $minutesLeft >= 30
            ? 'in 30 minutes'
            : 'in 5 minutes';

        return [
            'type' => 'scheduled_announcement_reminder',
            'title' => $this->announcement->title,
            'body' => "Your announcement titled \"{$this->announcement->title}\" will be published {$reminderText} (at {$publishTime->format('M j Y h:i A')}).",
            'id' => $this->announcement->id,
            'scheduled_at' => $publishTime->format('M j Y h:i A')
        ];
    }
}
