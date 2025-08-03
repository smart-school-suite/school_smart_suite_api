<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class ElectionConcluded extends Notification implements ShouldQueue
{
    use Queueable;

       protected $electionName;

    public function __construct($electionName)
    {
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

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Election Concluded – Check the Results!')
            ->greeting("Hello {$notifiable->name},")
            ->line("The {$this->electionName} has officially concluded.")
            ->line('You can now view the election results and see the winners.')
            ->action('View Results', url('/elections/results'))
            ->line('Thank you for participating in the election process.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Election Concluded',
            'body' => "The {$this->electionName} has ended. Check out the results!",
        ];
    }

     public function toBroadcast($notifiable):BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'Election Concluded',
            'body' => "The {$this->electionName} has ended. Results are now available.",
        ]);
    }
}
