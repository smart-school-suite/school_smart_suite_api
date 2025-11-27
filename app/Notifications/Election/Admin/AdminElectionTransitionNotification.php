<?php

namespace App\Notifications\Election\Admin;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
class AdminElectionTransitionNotification extends Notification implements ShouldQueue
{
       use Queueable;

    public $tries = 3;
    public $backoff = [60, 300, 600];
    protected $electionName;

    /**
     * Create a new notification instance.
     */
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
            ->subject('Election Fully Concluded')
            ->greeting('Hello Admin,')
            ->line("The {$this->electionName} has officially and successfully concluded.")
            ->line('All voting activities, result verifications, and transition processes have been completed.')
            ->line('You may now review the final results and officially close out this election period.')
            ->action('View Final Results', url('/admin/elections/results'))
            ->line('Thank you for supervising this election to its successful completion.');
    }

    /**
     * Get the array representation of the notification (for database storage).
     */
    public function toArray($notifiable)
    {
        return [
            'title' => 'Election Fully Concluded',
            'body' => "The {$this->electionName} has fully concluded — all phases are now complete.",
        ];
    }

    /**
     * Get the broadcast representation of the notification.
     */
    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'title' => 'Election Fully Concluded',
            'body' => "The {$this->electionName} has officially concluded. All phases are complete and results finalized.",
        ]);
    }
}
