<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
class AdminSpecialtyCreated extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    protected $schoolBranchId;
    protected $specialtyDetails;
    public function __construct($schoolBranchId, $specialtyDetails)
    {
        $this->schoolBranchId = $schoolBranchId;
        $this->specialtyDetails = $specialtyDetails;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                   ->subject('New Specialty Created')
                   ->greeting("Hello Admin, {$notifiable->name}")
                    ->line("A new Specialty {$this->specialtyDetails['specialty_name']} has been successfully created.")
                   ->line('You can view more details and manage the specialties by clicking the button below.')
                   ->action('View Department', url('/departments'))
                   ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable): array
    {
        return [
            'title' => 'New Specialty Created',
            'body' => "A new Specialty {$this->specialtyDetails['specialty_name']} has been successfully created",
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'New Specialty Created',
            'body' => "A new Specialty {$this->specialtyDetails['specialty_name']} has been successfully created",
        ]);
    }
}
