<?php

namespace App\Notifications\AdditionalFee\Admin;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminAdditionalFeePaidNotification extends Notification implements ShouldQueue
{
    use Queueable;
      public $tries = 3;

    public $backoff = [60, 300, 600];

    protected $studentName;
    protected $amount;
    protected $reason;
    protected $currency;

    public function __construct($studentName, $amount, $reason,  $currency)
    {
        $this->studentName = $studentName;
        $this->amount = $amount;
        $this->reason = $reason;
        $this->currency = $currency;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Student Paid Additional Fee')
            ->greeting("Hello Admin {$notifiable->name}")
            ->line("{$this->studentName} has paid an additional fee.")
            ->line("**Amount:** {$notifiable->formatAmount($this->amount)}")
            ->line("**Reason:** {$this->reason}")
            ->action('View Transaction', url('/admin/finance/transactions'))
            ->line('The payment has been successfully recorded.');
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => 'Additional Fee Paid by Student',
            'body' => "{$this->studentName} paid {$notifiable->formatAmount($this->amount)} for: {$this->reason} ",
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'Student Payment Received',
            'body' => "{$this->studentName} paid {$notifiable->formatAmount($this->amount)} for: {$this->reason} .",
        ]);
    }
}
