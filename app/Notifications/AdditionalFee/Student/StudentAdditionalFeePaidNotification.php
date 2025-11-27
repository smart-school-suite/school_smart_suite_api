<?php

namespace App\Notifications\AdditionalFee\Student;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Laravel\Firebase\Facades\FirebaseMessaging;

class StudentAdditionalFeePaidNotification extends Notification implements ShouldQueue
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
        return ['database', 'broadcast', 'fcm'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Payment Successful – Additional Fee')
            ->greeting("Hello {$notifiable->name},")
            ->line("We have received your payment of {$notifiable->formatAmount($this->amount)} for: **{$this->reason}**.")
            ->action('View Receipt', url('/student/fees/receipts'))
            ->line('Thank you for your prompt payment.');
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Additional Fee Payment Completed',
            'body' => "You paid  {$notifiable->formatAmount($this->amount)} for: {$this->feeTitle}.",
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'title' => 'Additional Fee Payment Completed',
            'body' => "You paid {$notifiable->formatAmount($this->amount)} for: {$this->feeTitle}.",
        ]);
    }

    public function toFcm($notifiable)
    {
        return CloudMessage::new()
            ->withNotification([
                'title' => 'New Fee Charged',
                //'body'  => "{$this->studentName} was charged XAF {$this->amount}",
            ])
            ->withData([
                'type' => 'fee',
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            ]);
    }
}
