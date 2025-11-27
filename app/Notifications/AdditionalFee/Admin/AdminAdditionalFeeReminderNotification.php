<?php

namespace App\Notifications\AdditionalFee\Admin;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Kreait\Firebase\Messaging\CloudMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class AdminAdditionalFeeReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $tries = 3;
    public $backoff = [60, 300, 600];

    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    // Unified message used in all channels
    private function getMessage(): string
    {
        $count        = $this->data['unpaid_number'];
        $amount       = number_format($this->data['unpaid_amount'], 0, '', ',');
        $feeName      = $this->data['fee_name'];
        $dueDate      = $this->data['due_date']
            ? \Carbon\Carbon::parse($this->data['due_date'])->format('d M Y')
            : 'overdue';

        $specialties = collect($this->data['specialties'])
            ->pluck('specialty_name')
            ->unique()
            ->sort();

        $specialtyText = $specialties->count() > 2
            ? $specialties->take(2)->join(', ') . ' and ' . ($specialties->count() - 2) . ' other program(s)'
            : $specialties->join(' and ');

        if ($specialties->isEmpty()) {
            $specialtyText = 'various programs';
        }

        return "{$count} student(s) from {$specialtyText} have not paid the additional fee \"{$feeName}\" (Rwf {$amount} )    due on {$dueDate}.";
    }

    public function toMail($notifiable): MailMessage
    {
        $message = $this->getMessage();

        return (new MailMessage)
            ->subject('Unpaid Additional Fee Reminder')
            ->greeting("Hello {$notifiable->name},")
            ->line('**Payment Reminder**')
            ->line($message)
            ->when($this->data['reason'] ?? null, fn($m) => $m->line("**Reason:** " . $this->data['reason']))
            ->action('View Unpaid Fees', url('/admin/fees/additional?status=unpaid'))
            ->line('Please follow up with the students to ensure timely payment.');
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => 'Unpaid Additional Fee',
            'body'  => $this->getMessage(),
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'Unpaid Additional Fee',
            'body'  => $this->getMessage(),
        ]);
    }
}
