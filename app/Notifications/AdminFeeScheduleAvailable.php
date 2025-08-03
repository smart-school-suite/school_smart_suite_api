<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;

class AdminFeeScheduleAvailable extends Notification implements ShouldQueue
{
    use Queueable;

    protected $schoolYear;
    protected $semester;
    protected $specialty;
    protected $level;

    public function __construct($schoolYear, $semester, $specialty, $level)
    {
        $this->schoolYear = $schoolYear;
        $this->semester = $semester;
        $this->specialty = $specialty;
        $this->level = $level;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable)
    {
        return ['mail', 'database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject("Fee Schedule Published for {$this->specialty}, Level {$this->level} - {$this->semester} {$this->schoolYear}")
            ->greeting("Hello {$notifiable->name},")
            ->line("The fee schedule for **{$this->specialty}**, **Level {$this->level}**, **{$this->semester} semester**, **{$this->schoolYear}** academic year has been successfully published.")
            ->line("Students in this program can now access the updated tuition fee breakdown via their portals.")
            ->action('Manage Fee Schedules', url('/admin/finance/fee-schedules'))
            ->line('Please ensure all relevant departments are informed accordingly.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable)
    {
        return [
            'type' => 'admin_fee_schedule_published',
            'title' => "Fee Schedule Published for {$this->specialty}, Level {$this->level} ({$this->semester} {$this->schoolYear})",
            'body' => "The fee schedule for {$this->specialty}, Level {$this->level} - {$this->semester} semester of {$this->schoolYear} has been published.",
        ];
    }

    /**
     * Get the broadcast representation of the notification.
     */
    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => "Fee Schedule Published for {$this->specialty}, Level {$this->level}",
            'body' => "{$this->semester} semester fee schedule for {$this->specialty}, Level {$this->level}, {$this->schoolYear} is now live.",
        ]);
    }
}
