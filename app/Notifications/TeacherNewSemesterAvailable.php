<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class TeacherNewSemesterAvailable extends Notification implements ShouldQueue
{
      use Queueable;

    protected $semesterData;

    public function __construct($semesterData)
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
            ->subject("Submit Your Preferred Teaching Time for {$this->semesterData['semester']} {$this->semesterData['schoolYear']}")
            ->greeting("Dear {$notifiable->name},")
            ->line("The **{$this->semesterData['semester']} semester** for **{$this->semesterData['schoolYear']}** is approaching.")
            ->line("Please submit your **preferred teaching time** for:")
            ->line("• **Specialty:** {$this->semesterData['specialty']}")
            ->line("• **Level:** {$this->semesterData['level']}")
            ->line("This will help us prepare an optimal teaching schedule that aligns with your availability.")
            ->action('Submit Preferred Time', url('/teacher/availability'))
            ->line("We appreciate your cooperation in ensuring a smooth semester.");
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'availability_request',
            'title' => 'Submit Preferred Teaching Time',
            'message' => "Submit your preferred teaching time for {$this->semesterData['specialty']} - Level {$this->semesterData['level']} ({$this->semesterData['semester']}, {$this->semesterData['schoolYear']}).",
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'Preferred Teaching Time Needed',
            'message' => "Submit your preferred teaching time for {$this->semesterData['specialty']} - Level {$this->semesterData['level']} ({$this->semesterData['semester']} {$this->semesterData['schoolYear']}).",
        ]);
    }
}
