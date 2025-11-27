<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Laravel\Firebase\Facades\FirebaseMessaging;
class AdminAdditionalFee extends Notification implements ShouldQueue
{
    use Queueable;

      public $tries = 3;

    public $backoff = [60, 300, 600];
    protected $studentName;
    protected $amount;
    protected $reason;

    public function __construct($studentName, $amount, $reason)
    {
        $this->studentName = $studentName;
        $this->amount = $amount;
        $this->reason = $reason;
    }

    public function via($notifiable)
    {
        return ['mail', 'database', 'broadcast', 'fcm'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Student Incurred Additional Fee')
            ->greeting("Hello Admin {$notifiable->name}")
            ->line("{$this->studentName} has been charged an additional fee.")
            ->line("**Amount:** XAF{$this->amount}")
            ->line("**Reason:** {$this->reason}")
            ->action('View Student Financial Records', url('/admin/finance'))
            ->line('This fee has been added to the student’s account.');
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Student Charged Additional Fee',
            'body' => "{$this->studentName} incurred ₦{$this->amount} for: {$this->reason}.",
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'Student Charged Additional Fee',
            'body' => "{$this->studentName} incurred ₦{$this->amount} for: {$this->reason}.",
        ]);
    }

    public function toFcm($notifiable)
    {
        return CloudMessage::new()
            ->withNotification([
                'title' => 'New Fee Charged',
                'body'  => "{$this->studentName} was charged XAF {$this->amount}",
            ])
            ->withData([
                'type' => 'fee',
                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
            ]);
    }
}
