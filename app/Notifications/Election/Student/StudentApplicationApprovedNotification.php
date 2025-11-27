<?php

namespace App\Notifications\Election\Student;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class StudentApplicationApprovedNotification extends Notification
{
    use Queueable;

    public $tries = 3;

    public $backoff = [60, 300, 600];
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
            ->subject('Candidacy Application Approved')
            ->greeting("Hello {$notifiable->name},")
            ->line("Your application for the role of {$this->roleName} in the {$this->electionName} has been approved.")
            ->line('We wish you the best of luck in the upcoming election!')
            ->line('Thank you for your interest in leadership.');
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => 'Candidacy Approved',
            'body' => "Your application for the role of {$this->roleName} in {$this->electionName} has been approved. Best of luck!",
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'Candidacy Approved',
            'body' => "Your application for the role of {$this->roleName} in {$this->electionName} has been approved.",
        ]);
    }
}
