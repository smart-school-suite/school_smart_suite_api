<?php

namespace App\Notifications\Department;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class DepartmentDeactivatedNotification extends Notification
{
    use Queueable;

    public $tries = 3;
    public $backoff = [60, 300, 600];

    protected $departmentDetails;

    /**
     * Create a new notification instance.
     */
    public function __construct($departmentDetails)
    {
        $this->departmentDetails = $departmentDetails;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'broadcast', 'database'];
    }

    /**
     * Mail message.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Department Deactivated')
            ->greeting("Hello Admin {$notifiable->name}")
            ->line("The department **{$this->departmentDetails['department_name']}** has been deactivated.")
            ->line('This department is now disabled and cannot be used for operations unless reactivated.')
            ->action('View Departments', url('/departments'))
            ->line('Thank you for using Smart School Suite!');
    }

    /**
     * Database notification payload.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Department Deactivated',
            'body' => "The department {$this->departmentDetails['department_name']} has been deactivated.",
        ];
    }

    /**
     * Broadcast message.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'Department Deactivated',
            'body' => "The department {$this->departmentDetails['department_name']} has been deactivated.",
        ]);
    }
}
