<?php

namespace App\Notifications\AdditionalFee\Admin;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class AdminAdditionalFeeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $tries = 3;
    public $backoff = [60, 300, 600];

    protected $totalStudents;
    protected $totalAmount;
    protected $reason;

    /**
     * @param int    $totalStudents  Number of students charged
     * @param float  $totalAmount    Total additional fee amount across all students
     * @param string|null $reason    Common reason (optional — can be null if varied)
     */
    public function __construct(int $totalStudents, float $totalAmount, ?string $reason = null)
    {
        $this->totalStudents = $totalStudents;
        $this->totalAmount   = $totalAmount;
        $this->reason        = $reason;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast', 'fcm'];
    }

    public function toMail($notifiable)
    {
        $formattedTotal = $notifiable->formatAmount($this->totalAmount);

        $mail = (new MailMessage)
            ->greeting("Hello Admin {$notifiable->name}");

        if ($this->totalStudents === 1) {
            $mail->subject('A Student Incurred an Additional Fee')
                 ->line("One student has been charged an additional fee.")
                 ->line("**Amount:** {$formattedTotal}");
        } else {
            $mail->subject('Multiple Students Incurred Additional Fees')
                 ->line("**{$this->totalStudents} students** have been charged additional fees.")
                 ->line("**Total amount:** {$formattedTotal}");
        }

        if ($this->reason) {
            $mail->line("**Reason:** {$this->reason}");
        }

        $mail->line('These fees have been added to the students’ accounts.')
             ->action('Review Student Finances', url('/admin/finance'))
             ->line('You can view the detailed breakdown in the finance section.');

        return $mail;
    }

    public function toArray($notifiable)
    {
        $formattedTotal = $notifiable->formatAmount($this->totalAmount);

        if ($this->totalStudents === 1) {
            $body = "One student was charged {$formattedTotal}";
        } else {
            $body = "{$this->totalStudents} students were charged a total of {$formattedTotal}";
        }

        if ($this->reason) {
            $body .= " ({$this->reason})";
        }

        return [
            'title'       => $this->totalStudents === 1
                ? 'Additional Fee Charged'
                : 'Multiple Additional Fees Charged',
            'body'        => $body . '.',
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
