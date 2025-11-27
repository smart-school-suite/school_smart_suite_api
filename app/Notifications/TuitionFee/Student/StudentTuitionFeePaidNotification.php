<?php

namespace App\Notifications\TuitionFee\Student;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
class StudentTuitionFeePaidNotification extends Notification
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
        return [ 'database', 'broadcast', 'mail', 'fcm'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Tuition Payment Received')
            ->greeting("Hello {$notifiable->name},")
            ->line("We’ve received your payment of {$notifiable->formatAmount($this->balanceRemaining)} for tuition fees, paid on {$this->paymentDate}.")
            ->line("**Remaining Balance:** {$notifiable->formatAmount($this->balanceRemaining)}")
            ->line('Please ensure the balance is paid before the due date.')
            ->action('View Payment History', url('/student/fees'))
            ->line('Thank you for your continued commitment.');
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Tuition Fee Payment Update',
            'body' => "You paid {$notifiable->formatAmount($this->amountPaid)} . for tuition fees Balance left: {$notifiable->formatAmount($this->balanceRemaining)}.",
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'title' => 'Payment Received',
            'body' => "You paid {$notifiable->formatAmount($this->amountPaid)}. for tuition fees Remaining: {$notifiable->formatAmount($this->balanceRemaining)}.",
        ]);
    }
}
