<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
class TimetableAvailable extends Notification
{
     use Queueable;

    public $tries = 3;

    public $backoff = [60, 300, 600];
    protected array $timetableData;

    public function __construct(array $timetableData)
    {
        $this->timetableData = $timetableData;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject("Class Timetable Available - {$this->timetableData['semester']} {$this->timetableData['schoolYear']}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your class timetable for **Level {$this->timetableData['level']}**, **{$this->timetableData['semester']} semester**, **{$this->timetableData['schoolYear']}** academic year is now available.")
            ->line("You can now view the schedule of lectures and plan accordingly.")
            ->action('View Timetable', url('/student/timetable'))
            ->line("Please review your timetable regularly in case of any updates.")
            ->line("Best wishes for a productive semester ahead!");
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'timetable_available',
            'title' => "Timetable Available",
            'body' => "Your timetable for Level {$this->timetableData['level']}, {$this->timetableData['semester']} {$this->timetableData['schoolYear']} is now available.",
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'Timetable Published',
            'body' => "Your timetable for Level {$this->timetableData['level']}, {$this->timetableData['semester']} {$this->timetableData['schoolYear']} is now available.",
        ]);
    }
}
