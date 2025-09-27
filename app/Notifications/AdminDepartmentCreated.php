<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
class AdminDepartmentCreated extends Notification implements ShouldQueue
{
    use Queueable;

      public $tries = 3;

    public $backoff = [60, 300, 600];
    /**
     * Create a new notification instance.
     */
    protected $schoolBranchId;
    protected $departmentDetails;
    public function __construct($schoolBranchId, $departmentDetails)
    {
        $this->schoolBranchId = $schoolBranchId;
        $this->departmentDetails = $departmentDetails;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'broadcast', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
{
    return (new MailMessage)
        ->subject('New Department Created')
        ->greeting("Hello Admin {$notifiable->name}")
        ->line("{$this->departmentDetails['department_name']} has been successfully created.")
        ->line('You can view more details and manage the department by clicking the button below.')
        ->action('View Department', url('/departments'))
        ->line('Thank you for using our application!');
}

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray($notifiable): array
    {
        return [
            'title' => 'New Department Created',
            'body' => "A new department {$this->departmentDetails['department_name']} has been successfully created",
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'New Department Created',
            'body' => "A new department {$this->departmentDetails['department_name']} has been successfully created",
        ]);
    }
}
