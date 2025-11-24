<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TuitionFeePaid extends Notification implements ShouldQueue
{
    use Queueable;
    public $tries = 3;

    public $backoff = [60, 300, 600];
    protected $amountPaid;
    protected $balanceRemaining;
    protected $paymentDate;

    public function __construct($amountPaid, $balanceRemaining, $paymentDate)
    {
        $this->amountPaid = $amountPaid;
        $this->balanceRemaining = $balanceRemaining;
        $this->paymentDate = $paymentDate;
    }

    public function via($notifiable)
    {
        return [ 'database', 'broadcast'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Tuition Payment Received')
            ->greeting("Hello {$notifiable->name},")
            ->line("We’ve received your payment of XAF{$this->amountPaid} for tuition fees, paid on {$this->paymentDate}.")
            ->line("**Remaining Balance:** XAF{$this->balanceRemaining}")
            ->line('Please ensure the balance is paid before the due date.')
            ->action('View Payment History', url('/student/fees'))
            ->line('Thank you for your continued commitment.');
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Tuition Fee Payment Update',
            'body' => "You paid XAF{$this->amountPaid}. for tuition fees Balance left: XAF{$this->balanceRemaining}.",
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'title' => 'Payment Received',
            'body' => "You paid XAF{$this->amountPaid}. for tuition fees Remaining: XAF{$this->balanceRemaining}.",
        ]);
    }
}
