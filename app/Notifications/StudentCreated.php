<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StudentCreated extends Notification implements ShouldQueue
{
   use Queueable;

    public $tries = 3;

    public $backoff = [60, 300, 600];
    protected $studentName;
    protected $level;
    protected $specialty;

    public function __construct($studentName,  $level, $specialty)
    {
        $this->studentName = $studentName;
        $this->level = $level;
        $this->specialty = $specialty;
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
            ->subject('New Student Account Created')
            ->greeting("Hello {$notifiable->name},")
            ->line("A new student has been successfully registered into the system.")
            ->line("**Name:** {$this->studentName}")
            ->line("**Level:** {$this->level}")
            ->line("**Specialty:** {$this->specialty}")
            ->line('Please ensure the student details are verified and complete.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable)
    {
        return [
            'type' => 'student_created',
            'title' => 'New Student Created',
            'body' => "{$this->studentName} has been added to {$this->specialty}, Level {$this->level}.",
        ];
    }

    /**
     * Get the broadcast representation of the notification.
     */
    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => 'New Student Created',
            'body' => "{$this->studentName}  registered in {$this->specialty}, Level {$this->level}.",
        ]);
    }
}
