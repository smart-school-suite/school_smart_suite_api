<?php

namespace App\Notifications\ActivationCode\Admin;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminActivationCodeRenewalReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly int $daysRemaining,
        public readonly array $accounts
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    protected function accountSummary(): string
    {
        return collect($this->accounts)
            ->map(fn ($item) => "{$item['quantity']} {$item['type']} account" . ($item['quantity'] > 1 ? 's' : ''))
            ->implode(' and ');
    }

    public function toMail(object $notifiable): MailMessage
    {
        $summary = $this->accountSummary();

        return (new MailMessage)
            ->subject("Subscription expires in {$this->daysRemaining} days")
            ->greeting("Hello {$notifiable->name},")
            ->line("Your school subscription will expire in **{$this->daysRemaining} days**.")
            ->line("The following accounts are at risk of losing access:")
            ->line("**{$summary}**")
            ->line('Renew now to ensure uninterrupted access for both students and teachers.')
            ->action(
                'Renew Subscription',
                url('/admin/activation-codes/purchase')
            )
            ->line('Early renewal helps avoid service disruption.')
            ->line('— Smart School Suite Team');
    }

    public function toArray(object $notifiable): array
    {
        $summary = $this->accountSummary();

        return [
            'title' => 'Subscription Renewal Reminder',
            'body'  => "Your school subscription expires in {$this->daysRemaining} days. {$summary} will lose access if you do not renew.",
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        $summary = $this->accountSummary();

        return new BroadcastMessage([
            'title' => 'Subscription Renewal Reminder',
            'body'  => "Your subscription expires in {$this->daysRemaining} days. {$summary} are at risk. Renew now.",
        ]);
    }
}
