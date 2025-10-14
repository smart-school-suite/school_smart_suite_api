<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminElectionConcluded extends Notification implements ShouldQueue
{
    use Queueable;

    public $tries = 3;
    public $backoff = [60, 300, 600];

    protected $electionName;

    public function __construct($electionName)
    {
        $this->electionName = $electionName;
    }

    /**
     * Get the notification's delivery channels.
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
            ->subject('Voting Phase Concluded – Transition in Progress')
            ->greeting('Hello Admin,')
            ->line("The voting phase for **{$this->electionName}** has successfully concluded.")
            ->line('Our system is now handling the post-election transition process — verifying votes, finalizing results, and preparing for official role handover.')
            ->line('You will be notified once the transition is complete and results are ready for publication.')
            ->line('Thank you for supervising the election process.');
    }

    /**
     * Get the database notification representation.
     */
    public function toArray($notifiable)
    {
        return [
            'title' => 'Voting Phase Concluded',
            'body' => "The voting phase for {$this->electionName} has ended. The system is processing the transition and finalizing results. You’ll be notified when this process completes.",
        ];
    }

    /**
     * Get the broadcast notification representation.
     */
    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'Voting Phase Concluded',
            'body' => "The voting for {$this->electionName} has ended. The transition process is underway — you’ll be alerted once it’s complete.",
        ]);
    }
}
