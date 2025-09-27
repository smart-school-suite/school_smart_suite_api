<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class AvailabilitySubmitted extends Notification implements ShouldQueue
{
       use Queueable;
         public $tries = 3;

    public $backoff = [60, 300, 600];

    protected array $availabilityData;

    public function __construct(array $availabilityData)
    {
        $this->availabilityData = $availabilityData;
    }

    public function via($notifiable)
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject("Availability Submitted for {$this->availabilityData['semester']} {$this->availabilityData['schoolYear']}")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your teaching availability has been successfully recorded.")
            ->line("• **Specialty:** {$this->availabilityData['specialty']}")
            ->line("• **Level:** {$this->availabilityData['level']}")
            ->line("• **Semester:** {$this->availabilityData['semester']}")
            ->line("• **Academic Year:** {$this->availabilityData['schoolYear']}")
            ->line("The planning team will use this information to generate teaching schedules.")
            ->action('Review Availability', url('/teacher/availability'))
            ->line("Thank you for submitting your availability.");
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'availability_submitted',
            'title' => 'Availability Submitted',
            'body' => "Your availability for {$this->availabilityData['specialty']} - Level {$this->availabilityData['level']} ({$this->availabilityData['semester']}, {$this->availabilityData['schoolYear']}) has been saved.",
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'Availability Submitted',
            'body' => "{$this->availabilityData['specialty']} - Level {$this->availabilityData['level']} ({$this->availabilityData['semester']}, {$this->availabilityData['schoolYear']}) availability recorded.",
        ]);
    }
}
