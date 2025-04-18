<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class TestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $title;
    protected $body;
    protected $data;
    protected $type;

    /**
     * Create a new notification instance.
     */
    public function __construct($title, $body, $type, $data)
    {
        $this->title = $title;
        $this->body = $body;
        $this->type = $type;
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];  // Enable broadcast and database channels
    }

    /**
     * Save the notification in the database.
     */
    public function toDatabase($notifiable)
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'type' => $this->type,
            'data' => $this->data,  // Store the data in the database
        ];
    }

    /**
     * Send a broadcast notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'title' => $this->title,
            'body' => $this->body,
            'type' => $this->type,
            'data' => $this->data,  // Send all relevant data for the frontend
        ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'type' => $this->type,
            'data' => $this->data,  // Make sure the data is included here as well
        ];
    }
}
