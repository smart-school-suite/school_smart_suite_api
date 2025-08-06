<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class AppointedAsHOS extends Notification implements ShouldQueue
{
    use Queueable;

    protected $specialty;

    public function __construct($specialty)
    {
        $this->specialty = $specialty;
    }

    public function via($notifiable)
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject("Appointment as Head of Specialty (HOS)")
            ->greeting("Dear {$notifiable->name},")
            ->line("We are pleased to inform you that you have been appointed as the **Head of the {$this->specialty} Specialty")
            ->line("This is in recognition of your professional dedication, academic leadership, and contributions to the department.")
            ->line("We trust that you will excel in this leadership role and contribute meaningfully to student and staff development.")
            ->action('Go to HOS Dashboard', url('/staff/hos/dashboard'))
            ->line("Congratulations on your well-deserved appointment!");
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'appointment_hos',
            'title' => 'Appointed as Head of Specialty',
            'body' => "You have been appointed as Head of the {$this->specialty} Specialty",
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'HOS Appointment',
            'body' => "You have been appointed Head of the {$this->specialty} Specialty",
        ]);
    }
}
