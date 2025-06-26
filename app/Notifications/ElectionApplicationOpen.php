<?php

namespace App\Notifications;

use App\Models\Elections;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ElectionApplicationOpen extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    protected $election;
    public function __construct(Elections $election)
    {
        $this->election = $election;
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
                    ->subject('Election Application Window Open')
                    ->greeting("Helle $notifiable->name !")
                    ->line('The application period for the "' . $this->election->title . '" election has officially opened!')
                    ->line('You can now submit your application.')
                    ->action('View Election & Apply', url('/elections/' . $this->election->id . '/apply')) // Customize this URL as needed
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
            'election_title' => $this->election->electionType->election_title,
            'message' => 'The application period for "' . $this->election->electionType->election_title . '" has opened.',
            'link' => url('/elections/' . $this->election->id . '/apply'),
        ];
    }
}
