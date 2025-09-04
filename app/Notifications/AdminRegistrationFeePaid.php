<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminRegistrationFeePaid extends Notification implements ShouldQueue
{
use Queueable;

    protected $studentName;
    protected $amountPaid;
    protected $paymentDate;

    public function __construct($studentName, $amountPaid, $paymentDate)
    {
        $this->studentName = $studentName;
        $this->amountPaid = $amountPaid;
        $this->paymentDate = $paymentDate;
    }

    public function via($notifiable)
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Student Registration Payment Recorded')
            ->greeting("Hello Admin, {$notifiable->name}")
            ->line("{$this->studentName} has paid their registration fee.")
            ->line("**Amount Paid:** XAF{$this->amountPaid}")
            ->line("**Payment Date:** {$this->paymentDate}")
            ->action('View Record', url('/admin/finance/students'))
            ->line('The payment has been successfully recorded.');
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Registration Payment Recorded',
            'message' => "{$this->studentName} paid XAF{$this->amountPaid} for registration on {$this->paymentDate}.",
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'Registration Payment Received',
            'body' => "{$this->studentName} paid XAF{$this->amountPaid} for registration on {$this->paymentDate}.",
        ]);
    }
}
