<?php

namespace App\Notifications\ActivationCode\Teacher;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TeacherSubscribedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $tries = 3;
    public $backoff = [60, 300, 600];

    protected string $subscriptionExpiry;

    /**
     * Create a new notification instance.
     *
     * @param string $subscriptionExpiry – e.g., '2026-03-14'
     */
    public function __construct(string $subscriptionExpiry)
    {
        $this->subscriptionExpiry = $subscriptionExpiry;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Subscription is Active!')
            ->greeting("Hello {$notifiable->name},")
            ->line('Congratulations! Your school subscription has been successfully activated.')
            ->line("✅ Your access is now valid until **{$this->subscriptionExpiry}**.")
            ->line('You can now use all teacher features without interruption.')
            ->action('Go to Dashboard', url('/teacher/dashboard'))
            ->line('Thank you for using Smart School Suite!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Subscription Activated',
            'body'  => "Your school subscription is active until {$this->subscriptionExpiry}. You can now access all teacher features.",
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'Subscription Activated',
            'body'  => "Your school subscription is active until {$this->subscriptionExpiry}. You can now access all teacher features.",
        ]);
    }
}
