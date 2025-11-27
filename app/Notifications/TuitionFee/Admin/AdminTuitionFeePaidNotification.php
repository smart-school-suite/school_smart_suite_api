<?php

namespace App\Notifications\TuitionFee\Admin;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
class AdminTuitionFeePaidNotification extends Notification
{
   use Queueable;
   public $tries = 3;

    public $backoff = [60, 300, 600];
    protected $studentName;
    protected $amountPaid;
    protected $balanceRemaining;
    protected $paymentDate;

    public function __construct($studentName, $amountPaid, $balanceRemaining, $paymentDate)
    {
        $this->studentName = $studentName;
        $this->amountPaid = $amountPaid;
        $this->balanceRemaining = $balanceRemaining;
        $this->paymentDate = $paymentDate;
    }

    public function via($notifiable)
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Student Tuition Payment Logged')
            ->greeting("Hello Admin, {$notifiable->name}")
            ->line("{$this->studentName} has made a tuition fee payment.")
            ->line("**Amount Paid:** XAF{$this->amountPaid}")
            ->line("**Remaining Balance:** XAF{$this->balanceRemaining}")
            ->line("**Payment Date:** {$this->paymentDate}")
            ->action('View Payment Record', url('/admin/finance/students'))
            ->line('The transaction has been recorded in the system.');
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Tuition Payment Received',
            'body' => "{$this->studentName} paid XAF{$this->amountPaid}. Remaining: XAF{$this->balanceRemaining}.",
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'Tuition Payment Received',
            'body' => "{$this->studentName} paid XAF{$this->amountPaid}. Balance left: XAF{$this->balanceRemaining}.",
        ]);
    }
}
