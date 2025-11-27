<?php

namespace App\Notifications\Specialty;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class SpecialtyDeletedNotification extends Notification
{
    use Queueable;

    public $tries = 3;
    public $backoff = [60, 300, 600];

    protected $specialtyDetails;

    public function __construct($specialtyDetails)
    {
        $this->specialtyDetails = $specialtyDetails;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Specialty Deleted')
            ->greeting("Hello Admin, {$notifiable->name}")
            ->line("The specialty **{$this->specialtyDetails['specialty_name']}** has been deleted from the system.")
            ->line('This action removes the specialty from all active listings.')
            ->line('If this was unintentional, you may need to recreate the specialty.')
            ->action('View Specialties', url('/specialties'))
            ->line('Thank you for using our application!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Specialty Deleted',
            'body' => "The specialty {$this->specialtyDetails['specialty_name']} has been deleted.",
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'Specialty Deleted',
            'body' => "The specialty {$this->specialtyDetails['specialty_name']} has been deleted.",
        ]);
    }
}
