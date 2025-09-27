<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdditionalFeePaidNotification extends Notification implements ShouldQueue
{
      use Queueable;
  public $tries = 3;

    public $backoff = [60, 300, 600];
    protected $amount;
    protected $reason;
    protected $feeTitle;
    protected $currency;

    public function __construct($amount, $reason,  $feeTitle, $currency)
    {
        $this->amount = $amount;
        $this->reason = $reason;
        $this->feeTitle = $feeTitle;
        $this->currency = $currency;
    }

    public function via($notifiable)
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Payment Successful – Additional Fee')
            ->greeting("Hello {$notifiable->name},")
            ->line("We have received your payment of {$this->currency} {$this->amount} for: **{$this->reason}**.")
            ->action('View Receipt', url('/student/fees/receipts'))
            ->line('Thank you for your prompt payment.');
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Additional Fee Payment Completed',
            'body' => "You paid  {$this->currency}{$this->amount} for: {$this->feeTitle}.",
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'title' => 'Additional Fee Payment Completed',
            'body' => "You paid {$this->currency}{$this->amount} for: {$this->feeTitle}.",
        ]);
    }
}
