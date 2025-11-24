<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class AdminAdditionalFeeReminder extends Notification
{
    use Queueable;

    protected $unpaidFeesData;

    /**
     * Create a new notification instance.
     */
    public function __construct(array $unpaidFeesData)
    {
        $this->unpaidFeesData = $unpaidFeesData;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $count = $this->unpaidFeesData['count'] ?? 0;
        $totalAmount = number_format($this->unpaidFeesData['total_amount'] ?? 0, 2);
        $feesUrl = url('/admin/fees/additional?status=unpaid');

        return (new MailMessage)
            ->subject('🔴 URGENT: Additional Fees Are Now DUE')
            ->greeting('Hello Administration Team,')
            ->line("This is an **urgent alert**. We currently have **{$count} student(s)** with **unpaid additional fees** that are **now DUE**.")
            ->line('### 💰 Summary of DUE Fees')
            ->line("The **total amount DUE** for these outstanding fees is: **Rwf {$totalAmount}**.")
            ->line('Immediate action is required to follow up with the students concerned and ensure timely payment.')
            ->action('Review DUE Fees', $feesUrl)
            ->line('Thank you for ensuring the financial continuity of ');
    }

    /**
     * Get the array representation of the notification (for 'database' channel).
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $count = $this->unpaidFeesData['count'] ?? 0;
        $totalAmount = $this->unpaidFeesData['total_amount'] ?? 0;

        return [
            'type' => 'additional_fee_due_alert',
            'title' => 'Additional Fees are DUE',
            'body' => "{$count} student(s) have additional fees that are DUE today, totaling Rwf " . number_format($totalAmount, 2) . ".",
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        $count = $this->unpaidFeesData['count'] ?? 0;
        $totalAmount = number_format($this->unpaidFeesData['total_amount'] ?? 0, 2);

        return new BroadcastMessage([
            'title' => 'Due Student Additional Fees',
            'body' => "{$count} students' additional fees (Rwf {$totalAmount}) are now DUE.",
            'count' => $count,
        ]);
    }
}
