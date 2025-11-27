<?php

namespace App\Notifications\Department;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class DepartmentActivatedNotification extends Notification
{
    use Queueable;

    public $tries = 3;
    public $backoff = [60, 300, 600];

    protected $departmentDetails;

    public function __construct($departmentDetails)
    {
        $this->departmentDetails = $departmentDetails;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'broadcast', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Department Activated Successfully')
            ->greeting("Hello Admin {$notifiable->name}")
            ->line("The department **{$this->departmentDetails['department_name']}** has just been activated.")
            ->line('You can now manage this department and related operations.')
            ->action('View Department', url('/departments/' . $this->departmentDetails['department_id']))
            ->line('Thank you for using Smart School Suite!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Department Activated',
            'body' => "The department {$this->departmentDetails['department_name']} has been activated.",
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'Department Activated',
            'body' => "The department {$this->departmentDetails['department_name']} has been activated.",
        ]);
    }
}
