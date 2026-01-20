<?php

namespace App\Notifications\ActivationCode\Admin;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminActivationCodeExpireReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $tries = 3;
    public $backoff = [60, 300, 600];
    public readonly array $quantity;

    public readonly int $daysRemaining;

    /**
     * Create a new notification instance.
     *
     * @param array<int, array{type: string, quantity: int}> $quantity
     * @param int $daysRemaining
     */
    public function __construct(array $quantity, int $daysRemaining)
    {
        $this->quantity = $quantity;
        $this->daysRemaining = $daysRemaining;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    protected function buildMessage(): string
    {
        return collect($this->quantity)
            ->map(fn($item) => "{$item['quantity']} {$item['type']} codes")
            ->implode(' and ');
    }

    public function toMail(object $notifiable): MailMessage
    {
        $summary = $this->buildMessage();

        return (new MailMessage)
            ->subject('Activation Codes Expiry Reminder')
            ->greeting("Hello {$notifiable->name},")
            ->line("You have **{$summary}** expiring in **{$this->daysRemaining} days**.")
            ->line('Assign them to users now to ensure they do not go to waste.')
            ->action('Manage Activation Codes', url('/admin/activation-codes'))
            ->line('Thank you for using Smart School Suite!');
    }

    public function toArray(object $notifiable): array
    {
        $summary = $this->buildMessage();

        return [
            'title' => 'Activation Codes Expiry Reminder',
            'body'  => "You have {$summary} expiring in {$this->daysRemaining} days. Assign them before they expire.",
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        $summary = $this->buildMessage();

        return new BroadcastMessage([
            'title' => 'Activation Codes Expiry Reminder',
            'body'  => "You have {$summary} expiring in {$this->daysRemaining} days. Assign them before they expire.",
        ]);
    }
}
