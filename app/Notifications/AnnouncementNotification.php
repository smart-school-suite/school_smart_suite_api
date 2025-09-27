<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Announcement;

class AnnouncementNotification extends Notification implements ShouldQueue
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
        return (new MailMessage)
            ->subject('New Announcement for You')
            ->greeting("Hello {$notifiable->name},")
            ->line('You have received a new announcement that concerns you.')
            ->line($this->announcement->title)
            ->line($this->announcement->content)
            ->action('View Announcement', url("/announcements/{$this->announcement->id}"))
            ->line('Please log in to your account for more details.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'announcement',
            'title' => $this->announcement->title,
            'body' => $this->announcement->content,
            'id' => $this->announcement->id
        ];
    }
}
