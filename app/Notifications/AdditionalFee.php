<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdditionalFee extends Notification implements ShouldQueue
{
    use Queueable;
    public $tries = 3;

    public $backoff = [60, 300, 600];
    protected $amount;
    protected $reason;

    public function __construct($amount, $reason)
    {
        $this->amount = $amount;
        $this->reason = $reason;
    }

    public function via($notifiable)
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Additional Fee Incurred')
            ->greeting("Hello {$notifiable->name},")
            ->line("An additional fee of XAF{$this->amount} has been added to your account.")
            ->line("**Reason:** {$this->reason}")
            ->action('View Payment Details', url('/student/fees'))
            ->line('Please attend to the payment at your earliest convenience.');
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Additional Fee Incurred',
            'body' => "You were charged ₦{$this->amount} for: {$this->reason}.",
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'Additional Fee Incurred',
            'body' => "You were charged ₦{$this->amount} for: {$this->reason}.",
        ]);
    }
}
