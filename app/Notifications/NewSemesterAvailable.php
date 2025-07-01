<?php

namespace App\Notifications;


use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewSemesterAvailable extends Notification implements ShouldQueue
{
          use Queueable;

    protected array $semesterData;

    public function __construct(array $semesterData)
    {
        $this->semesterData = $semesterData;
    }

    public function via($notifiable)
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject("New Semester Available")
            ->greeting("Hello $notifiable->name,")
            ->line("A new academic semester has been launched for your level.")
            ->line("• **Level:** {$this->semesterData['level']}")
            ->line("• **Semester:** {$this->semesterData['semester']}")
            ->line("• **Academic Year:** {$this->semesterData['schoolYear']}")
            ->line("• **Start Date:** {$this->semesterData['startDate']}")
            ->line("• **End Date:** {$this->semesterData['endDate']}")
            ->action('Go to Dashboard', url('/student/dashboard'))
            ->line("Wishing you a successful and productive semester ahead!");
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'new_semester_available',
            'title' => 'New Semester Available',
            'message' => "Get ready! The {$this->semesterData['semester']} semester begins on {$this->semesterData['startDate']}",
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'New Semester Available',
            'message' => "Get ready! The {$this->semesterData['semester']} semester begins on {$this->semesterData['startDate']}",
        ]);
    }
}
