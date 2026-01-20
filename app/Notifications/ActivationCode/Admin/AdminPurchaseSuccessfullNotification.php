<?php

namespace App\Notifications\ActivationCode\Admin;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class AdminPurchaseSuccessfullNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $tries = 3;
    public $backoff = [60, 300, 600];

    public function __construct(
        public readonly array $items,
        public readonly float $totalAmount
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'broadcast', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Activation Codes Purchase Successful')
            ->greeting("Hello Admin {$notifiable->name},")
            ->line('Your activation code purchase has been completed successfully.')
            ->line('**Purchase summary:**');

        foreach ($this->items as $item) {
            $mail->line(sprintf(
                '• %d %s activation codes (%d days validity)',
                $item['quantity'],
                Str::title($item['type']),
                $item['duration']
            ));
        }

        $mail
            ->line("**Total amount paid:** {$notifiable->formatAmount($this->totalAmount)}")
            ->action(
                'Manage Activation Codes',
                url('/admin/activation-codes')
            )
            ->line('You can now assign these codes to users from your dashboard.')
            ->line('Thank you for choosing Smart School Suite.');

        return $mail;
    }

    public function toArray(object $notifiable): array
    {
        $itemsSummary = collect($this->items)
            ->map(fn($item) => "{$item['quantity']} " . ucfirst($item['type']) . " codes ({$item['duration']} days)")
            ->implode(', ');

        return [
            'title' => 'Activation Codes Purchased Successfully',
            'body'  => "You purchased: {$itemsSummary}. Total amount: {$notifiable->formatAmount($this->totalAmount)}",
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        $itemsSummary = collect($this->items)
            ->map(fn($item) => "{$item['quantity']} " . ucfirst($item['type']) . " codes ({$item['duration']} days)")
            ->implode(', ');

        return new BroadcastMessage([
            'title' => 'Activation Codes Purchased Successfully',
            'body'  => "You purchased: {$itemsSummary}. Total amount: {$notifiable->formatAmount($this->totalAmount)}",
        ]);
    }
}
