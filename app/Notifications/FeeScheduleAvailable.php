<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class FeeScheduleAvailable extends Notification implements ShouldQueue
{
    use Queueable;

      public $tries = 3;

    public $backoff = [60, 300, 600];
    protected $schoolYear;
    protected $semester;

    public function __construct($schoolYear, $semester)
    {
        $this->schoolYear = $schoolYear;
        $this->semester = $semester;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject("Your Fee Payment Schedule for $this->semester  $this->schoolYear  is Now Available!")
            ->greeting("'Hello $notifiable->name,")
            ->line("We are pleased to inform you that your tuition fee payment schedule for the **{$this->semester} semester** of the **{$this->schoolYear}** academic year is now available.")
            ->line('You can log in to your student portal to view the detailed breakdown, upcoming due dates, and payment instructions.')
            ->action('View My Payment Schedule', url('/student/tuition-fees/'))
            ->line('Please ensure to adhere to the schedule to avoid any penalties or interruptions in your academic activities.')
            ->line('Thank you for being a part of our school!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'type' => 'fee_schedule_available',
            'title' => "Fee Schedule for $this->semester $this->schoolYear",
            'message' => "Your tuition fee schedule for {$this->semester} semester of {$this->schoolYear} is now available.",
        ];
    }

    /**
     * Get the broadcast representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\BroadcastMessage
     */
    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => "Fee Schedule for  $this->semester  $this->schoolYear",
            'body' => "Your tuition fee schedule for {$this->semester} semester of {$this->schoolYear} is now available.",
        ]);
    }
}

