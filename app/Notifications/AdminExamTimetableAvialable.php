<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class AdminExamTimetableAvialable extends Notification implements ShouldQueue
{
    use Queueable;

    protected array $examData;

    public function __construct(array $examData)
    {
        $this->examData = $examData;
    }

    public function via($notifiable)
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject("Exam Timetable Created for Level {$this->examData['level']} ({$this->examData['semester']} {$this->examData['schoolYear']})")
            ->greeting("Hello {$notifiable->name},")
            ->line("An exam timetable has been successfully created for:")
            ->line("• **Level:** {$this->examData['level']}")
            ->line("• **Semester:** {$this->examData['semester']}")
            ->line("• **School Year:** {$this->examData['schoolYear']}")
            ->action('Manage Exam Timetables', url('/admin/exams/timetables'))
            ->line("Please verify all entries");
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'exam_timetable_created',
            'title' => 'Exam Timetable Created',
            'body' => "Exam timetable for Level {$this->examData['level']} ({$this->examData['semester']} {$this->examData['schoolYear']}) has been created.",
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'Exam Timetable Created',
            'body' => "Exam timetable for {$this->examData['semester']} {$this->examData['schoolYear']} (Level {$this->examData['level']}) is now set.",
        ]);
    }
}
