<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RegistrationFeePaid extends Notification implements ShouldQueue
{
    use Queueable;

    protected $amountPaid;
    protected $paymentDate;

    public function __construct($amountPaid, $paymentDate)
    {
        $this->amountPaid = $amountPaid;
        $this->paymentDate = $paymentDate;
    }

    public function via($notifiable)
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail($notifiable):MailMessage
    {
        return (new MailMessage)
            ->subject('Registration Fee Payment Received')
            ->greeting("Hello {$notifiable->name},")
            ->line("We have received your registration fee payment of XAF{$this->amountPaid}.")
            ->line("**Payment Date:** {$this->paymentDate}")
            ->action('View Receipt', url('/student/fees/receipts'))
            ->line('Thank you for completing your registration process.');
    }

    public function toArray($notifiable):array
    {
        return [
            'title' => 'Registration Fee Paid',
            'message' => "You paid XAF{$this->amountPaid} for registration Fee on {$this->paymentDate}.",
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'Registration Fee Paid',
            'message' => "You paid XAF{$this->amountPaid} for registration Fee on {$this->paymentDate}.",
        ]);
    }
}
