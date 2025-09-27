<?php

namespace App\Notifications;

use App\Models\Elections;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ElectionVotingOpen extends Notification
{
    use Queueable;
      public $tries = 3;

    public $backoff = [60, 300, 600];

    /**
     * Create a new notification instance.
     */
    protected Elections $election;
    public function __construct(Elections $elections)
    {
        $this->election = $elections;
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
                    ->subject('Election Voting Window Open')
                    ->greeting("Hello $notifiable->name !")
                    ->line('The voting period for the "' . $this->election->title . '" election has officially opened!')
                    ->line('You cast your vote ')
                    ->action('Cast Your Vote Now for Your Favourite Candidate', url('/elections/' . $this->election->id . '/apply')) // Customize this URL as needed
                    ->line('Thank you for your interest and participation!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'election_id' => $this->election->id,
            'title' => $this->election->electionType->election_title,
            'body' => 'The voting period for "' . $this->election->electionType->election_title . '" has opened.',
        ];
    }
}
