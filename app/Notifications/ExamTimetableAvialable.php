<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExamTimetableAvialable extends Notification implements ShouldQueue
{
     use Queueable;

       public $tries = 3;

    public $backoff = [60, 300, 600];
    protected  array $examData;

    public function __construct(array $examData)
    {
       $this->examData = $examData;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Your {$this->examData['examName']} time table is Now Available")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your {$this->examData['examName']} for
             **Level {$this->examData['level']}**, **{$this->examData['semester']} semester**,
             **{$this->examData['schoolYear']}** academic year is now published."
             )
            ->action('View Timetable', url('/student/exams/timetable'))
            ->line("Please check the schedule and prepare accordingly.")
            ->line("Best of luck with your exams!");
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'exam_timetable_published',
            'title' => 'Exam Timetable Available',
            'body' => "The {$this->examData['examName']} time table is now available, Best of luck with your exams",
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'Exam Timetable Available',
            'body' => "The {$this->examData['examName']} time table is now available, Best of luck with your exams",
        ]);
    }
}
