<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class ResitPayment extends Notification implements ShouldQueue
{
      use Queueable;

    protected array $paymentDetail;

    public function __construct(array $paymentDetail)
    {
        $this->paymentDetail = $paymentDetail;
    }

    public function via($notifiable)
    {
        return ['mail', 'database', 'broadcast'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Course Resit Payment Confirmed')
            ->greeting("Hello {$notifiable->name},")
            ->line("We have received your payment for the course resit.")
            ->line("• **Course:** {$this->paymentDetail['courseName']}")
            ->line("• **Amount Paid:** {$this->paymentDetail['amount']}")
            ->line("• **Transaction Reference:** {$this->paymentDetail['transactionRef']}")
            ->line("You can now view your resit schedule and details via your student portal.")
            ->action('View Resit Details', url('/student/resits'))
            ->line("Thank you for completing your resit payment.");
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'resit_payment_successful',
            'title' => 'Course Resit Payment Successful',
            'body' => "Your resit payment for {$this->paymentDetail['courseName']} has been received. Amount: {$this->paymentDetail['amount']}.",
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'Course Resit Payment Successful',
            'body' => "Payment for {$this->paymentDetail['courseName']} resit has been received.",
        ]);
    }
}
