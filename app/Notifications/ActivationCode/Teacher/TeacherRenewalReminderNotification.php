<?php

namespace App\Notifications\ActivationCode\Teacher;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TeacherRenewalReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $tries = 3;
    public $backoff = [60, 300, 600];

    protected string $expiryDate;
    protected int $daysRemaining;

    public function __construct(string $expiryDate, int $daysRemaining)
    {
        $this->expiryDate = $expiryDate;
        $this->daysRemaining = $daysRemaining;
    }
    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Your school's subscription expires in {$this->daysRemaining} days")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your school's subscription will expire in **{$this->daysRemaining} days** (on {$this->expiryDate}).")
            ->line('Please contact your school administrator to ensure the subscription is renewed to avoid any service interruption.')
            ->action('Go to Dashboard', url('/teacher/dashboard'))
            ->line('Smart School Suite Team');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Subscription Renewal Reminder',
            'body'  => "Your school's subscription expires in {$this->daysRemaining} days (on {$this->expiryDate}). Please contact your school administrator to renew it.",
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'Subscription Renewal Reminder',
            'body'  => "Your school's subscription expires in {$this->daysRemaining} days (on {$this->expiryDate}). Please contact your school administrator to renew it.",
        ]);
    }
}
