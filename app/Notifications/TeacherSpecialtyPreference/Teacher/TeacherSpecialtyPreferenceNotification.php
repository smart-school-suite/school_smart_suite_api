<?php

namespace App\Notifications\TeacherSpecialtyPreference\Teacher;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
class TeacherSpecialtyPreferenceNotification extends Notification
{
     use Queueable;

    public $tries = 3;

    public $backoff = [60, 300, 600];
    protected array $specialties;

    public function __construct(array $specialties)
    {
        $this->specialties = $specialties;
    }

    public function via($notifiable)
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail($notifiable)
    {
        $formattedSpecialties = implode(', ', $this->specialties);

        return (new MailMessage)
            ->subject('You Have Been Assigned to Your Specialties')
            ->greeting("Hello {$notifiable->name},")
            ->line("You have been successfully assigned to the following specialty(ies) based on your preferences:")
            ->line("**{$formattedSpecialties}**")
            ->action('View Assignments', url('/teacher/assignments'))
            ->line('Please check your dashboard for teaching responsibilities and schedules.')
            ->line('Thank you for your commitment to teaching excellence.');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'specialty_assigned',
            'title' => 'Specialty Assignment Confirmed',
            'body' => 'You have been assigned to: ' . implode(', ', $this->specialties),
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'Assigned to Specialties',
            'body' => 'You have been assigned to: ' . implode(', ', $this->specialties),
        ]);
    }
}
