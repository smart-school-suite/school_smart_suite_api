<?php

namespace App\Notifications\Department;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class DepartmentDeletedNotification extends Notification
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
     * Email representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Department Deleted')
            ->greeting("Hello Admin {$notifiable->name}")
            ->line("The department **{$this->departmentDetails['department_name']}** has been deleted from your school branch.")
            ->line('This action is permanent and the department is no longer available for management.')
            ->line('If this was intentional, no further action is required.')
            ->line('If this was not done by you, please review your admin activity immediately.')
            ->salutation('Regards,
Smart School Suite Team');
    }

    /**
     * Database notification data.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Department Deleted',
            'body'  => "The department {$this->departmentDetails['department_name']} has been deleted.",
        ];
    }

    /**
     * Broadcast message.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'Department Deleted',
            'body'  => "The department {$this->departmentDetails['department_name']} has been deleted.",
        ]);
    }
}
