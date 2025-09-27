<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminApplicationApproved extends Notification
{
    use Queueable;

      public $tries = 3;

    public $backoff = [60, 300, 600];
    /**
     * Create a new notification instance.
     */
    protected $studentName;
    protected $roleName;
    protected $electionName;
    public function __construct($studentName, $roleName, $electionName)
    {
        $this->studentName = $studentName;
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
            ->subject('Candidate Application Approved')
            ->greeting("Hello Admin,")
            ->line("The application of {$this->studentName} for the role of {$this->roleName} in the {$this->electionName} has been approved.")
            ->line('Keep track of all candidate activities from the admin panel.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable):array
    {
        return [
            'title' => 'Candidate Approved',
            'body' => "{$this->studentName} has been approved for the {$this->roleName} role in {$this->electionName}.",
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'Candidate Approved',
            'body' => "{$this->studentName} has been approved for the {$this->roleName} role in {$this->electionName}.",
        ]);
    }
}
