<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminElectionConcluded extends Notification
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
       public function via($notifiable)
    {
        return ['mail', 'database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
       public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Election Concluded')
            ->greeting("Hello Admin,")
            ->line("The {$this->electionName} has successfully concluded.")
            ->line('You can now review and publish the official results if needed.')
            ->action('Manage Results', url('/admin/elections/results'))
            ->line('Thank you for overseeing the election process.');
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Election Completed',
            'body' => "The {$this->electionName} has concluded. Review and publish results.",
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'Election Completed',
            'body' => "The {$this->electionName} has ended. Admin actions may be required.",
        ]);
    }
}
