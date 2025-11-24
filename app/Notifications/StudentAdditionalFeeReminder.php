<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Carbon\Carbon; // Ensure you use Carbon

class StudentAdditionalFeeReminder extends Notification
{
    use Queueable;

    public $tries = 3;

    public $backoff = [60, 300, 600];
    protected $additionalFeeData;

    public function __construct(array $additionalFeeData)
    {
        $this->additionalFeeData = $additionalFeeData;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [ 'database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $amount = number_format($this->additionalFeeData['amount'], 2);
        $reason = $this->additionalFeeData['reason'];
        $dueDate = Carbon::parse($this->additionalFeeData['due_date'])->format('F jS, Y');

        return (new MailMessage)
            ->subject('Urgent Reminder: Additional Fee Payment Due')
            ->greeting('Dear ' . $notifiable->name . ',')
            ->line('This is a friendly but important reminder that an **additional fee** is **due soon** on **' . $dueDate . '**. Please ensure the payment is completed on time to avoid any disruptions.')
            ->line('### 💰 Fee Details')
            ->line('**Reason:** ' . $reason)
            ->line('**Amount Due:** ' . 'Rwf ' . $amount)
            ->line('**Due Date:** ' . $dueDate)
            ->line('If you have already made this payment, please disregard this email.')
            ->line('Thank you for your prompt attention to this matter.');
    }

    public function toArray($notifiable)
    {
        $amount = number_format($this->additionalFeeData['amount'], 2);
        $dueDate = Carbon::parse($this->additionalFeeData['due_date'])->format('M j');

        return [
            'type' => 'additional_fee_reminder',
            'title' => 'Fee Due Soon: Rwf ' . $amount,
            'body' => "The additional fee for '{$this->additionalFeeData['reason']}' is due on {$dueDate}.",
            'fee_id' => $this->additionalFeeData['id'],
            'amount' => $this->additionalFeeData['amount'],
            'due_date' => $this->additionalFeeData['due_date'],
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        $amount = number_format($this->additionalFeeData['amount'], 2);
        $dueDate = Carbon::parse($this->additionalFeeData['due_date'])->format('M j');

        return new BroadcastMessage([
            'title' => 'Additional Fee Reminder',
            'body' => "Rwf {$amount} due for '{$this->additionalFeeData['reason']}' on {$dueDate}. Tap to pay.",
            'fee_id' => $this->additionalFeeData['id'],
        ]);
    }
}
