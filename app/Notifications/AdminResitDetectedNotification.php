<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
class AdminResitDetectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */

    protected $examDetails;
    protected $resitExam;
    public function __construct($examDetails, $resitExam)
    {
        $this->examDetails = $examDetails;
        $this->resitExam = $resitExam;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Resit Exam Created: {$this->examDetails->specialty->specialty_name} {$this->examDetails->level->name}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Following the results for the **{$this->examDetails->specialty->specialty_name} {$this->examDetails->level->name} {$this->examDetails->school_year} {$this->examDetails->examtype->exam_name}**, our system has detected that some students did not pass.")
            ->line("We have automatically created a resit exam for these students. You can view the details and manage the exam by clicking the button below.")
            ->action('View Resit Exam', url('/resit-exams'))
            ->line("Please update the resit exam details and create the timetable so we can generate the list of candidates.")
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
   public function toArray($notifiable)
    {
        return [
            'title' => 'Resit Exam Created',
            'body' => "Following the results for the **{$this->examDetails->specialty->specialty_name} {$this->examDetails->level->name} {$this->examDetails->school_year} {$this->examDetails->examtype->exam_name}**, our system has detected that some students did not pass.",
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'Resit Exam Created',
            'body' => "Following the results for the **{$this->examDetails->specialty->specialty_name} {$this->examDetails->level->name} {$this->examDetails->school_year} {$this->examDetails->examtype->exam_name}**, our system has detected that some students did not pass.",
        ]);
    }
}
