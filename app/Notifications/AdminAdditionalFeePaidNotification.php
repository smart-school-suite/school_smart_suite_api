<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminAdditionalFeePaidNotification extends Notification
{
    use Queueable;

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
            ->greeting("Hello Admin,")
            ->line("{$this->studentName} has paid an additional fee.")
            ->line("**Amount:** {$this->currency}{$this->amount}")
            ->line("**Reason:** {$this->reason}")
            ->action('View Transaction', url('/admin/finance/transactions'))
            ->line('The payment has been successfully recorded.');
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => 'Additional Fee Paid by Student',
            'message' => "{$this->studentName} paid {$this->currency}{$this->amount} for: {$this->reason} ",
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'Student Payment Received',
            'message' => "{$this->studentName} paid {$this->currency}{$this->amount} for: {$this->reason} .",
        ]);
    }
}
