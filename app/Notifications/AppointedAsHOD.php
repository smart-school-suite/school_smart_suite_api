<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class AppointedAsHOD extends Notification implements ShouldQueue
{
    use Queueable;

    protected $department;

    public function __construct($department)
    {
        $this->department = $department;
    }

    public function via($notifiable)
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject("Congratulations on Your Appointment as HOD")
            ->greeting("Dear {$notifiable->name},")
            ->line("We are delighted to inform you that you have been appointed as the **Head of the {$this->department} Department")
            ->line("This appointment is a recognition of your leadership, commitment, and service to the institution.")
            ->line("We wish you success in your new role and look forward to your impactful leadership.")
            ->action('Go to Dashboard', url('/staff/hod/dashboard'))
            ->line("Congratulations once again!");
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'appointment_hod',
            'title' => 'HOD Appointment',
            'body' => "Congratulations! You are now the HOD of {$this->department}",
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'HOD Appointment',
            'body' => "Congratulations! You are now the HOD of {$this->department}",
        ]);
    }
}
