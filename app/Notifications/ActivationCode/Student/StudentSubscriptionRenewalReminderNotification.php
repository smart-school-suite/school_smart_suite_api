<?php

namespace App\Notifications\ActivationCode\Student;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StudentSubscriptionRenewalReminderNotification extends Notification implements ShouldQueue
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
            ->subject("Your subscription expires in {$this->daysRemaining} days")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your school subscription will expire in **{$this->daysRemaining} days** (on {$this->expiryDate}).")
            ->line('Please remind your school administrator to renew your subscription to avoid service interruption.')
            ->action('Go to Dashboard', url('/student/dashboard'))
            ->line('Smart School Suite Team');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Subscription Renewal Reminder',
            'body'  => "Your subscription expires in {$this->daysRemaining} days (on {$this->expiryDate}). Please remind your school administrator to renew it.",
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'Subscription Renewal Reminder',
            'body'  => "Your subscription expires in {$this->daysRemaining} days (on {$this->expiryDate}). Please remind your school administrator to renew it.",
        ]);
    }
}
