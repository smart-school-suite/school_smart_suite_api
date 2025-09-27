<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
class AdminExamResultsReleased extends Notification implements ShouldQueue
{
    use Queueable;
      public $tries = 3;

    public $backoff = [60, 300, 600];

    /**
     * Create a new notification instance.
     */
    protected $examDetails;
    public function __construct($examDetails)
    {
        $this->examDetails = $examDetails;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'broadcast', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject("Exam Results Released")
                    ->greeting("Hello Admin {$notifiable->name}")
                    ->line("The {$this->examDetails->examtype->exam_name} {$this->examDetails->specialty_name} {$this->examDetails->level->name} for the {$this->examDetails->school_year}
                      academic school year have been published")
                    ->line("You can now check student ranking with respect to this exam")
                    ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
     public function toArray($notifiable): array
    {
        return [
            'title' => 'Exam Results Released',
            'body' => "The {$this->examDetails->examtype->exam_name} {$this->examDetails->specialty_name} {$this->examDetails->level->name} for the {$this->examDetails->school_year}
                      academic school year have been published",
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'Exam Results Released',
            'body' => "The {$this->examDetails->examtype->exam_name} {$this->examDetails->specialty_name} {$this->examDetails->level->name} for the {$this->examDetails->school_year}
                      academic school year have been published",
        ]);
    }
}
