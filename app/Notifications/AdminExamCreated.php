<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminExamCreated extends Notification implements ShouldQueue
{
    use Queueable;

    protected $level;
    protected $semester;
    protected $examData;

    public function __construct($level, $semester, $examData)
    {
        $this->level = $level;
        $this->semester = $semester;
        $this->examData = $examData;
    }

    public function via($notifiable)
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Exam Created')
            ->greeting("Hello {$notifiable->name},")
            ->line("A new {$this->examData['examName']} has been created successfully.")
            ->line("**Level:** {$this->level}")
            ->line("**Semester:** {$this->semester}")
            ->line("**Exam Date:** {$this->examData['startDate']} to $this->examData['endDate']")
            ->action('View Exams', url('/admin/exams'))
            ->line('Please ensure the exam is properly managed and linked with students.');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'exam_created',
            'title' => 'Exam Created',
            'message' => "exam for Level {$this->level}, {$this->semester} has been created.",
        ];
    }

    public function toBroadcast($notifiable):BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'New Exam Created',
            'message' => "{$this->examData['examName']} (Level {$this->level}, {$this->semester}) is scheduled to run from {$this->examData['startDate']}
             to
             {$this->examData['endDate']}
            .",
        ]);
    }
}
