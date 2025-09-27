<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Channels\BroadcastChannel;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ElectionWinner extends Notification implements ShouldQueue
{
    use Queueable;
      public $tries = 3;

    public $backoff = [60, 300, 600];

    /**
     * Create a new notification instance.
     */
    protected $roleName;
    protected $electionName;

    public function __construct($roleName, $electionName)
    {
        $this->roleName = $roleName;
        $this->electionName = $electionName;
    }


    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
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
            ->subject('Congratulations on Winning the Election!')
            ->greeting("Congratulations  $notifiable->name !")
            ->line("You have been elected as the new {$this->roleName} in the {$this->electionName}.")
            ->line('Your dedication and effort are highly appreciated.')
            ->line('We wish you a successful term!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Election Winner 🎉',
            'body' => "You have been elected as the {$this->roleName} in the {$this->electionName}.",
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'Election Winner 🎉',
            'body' => "You have been elected as the {$this->roleName} in the {$this->electionName}.",
        ]);
    }
}
