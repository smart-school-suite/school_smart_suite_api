<?php

namespace App\Notifications;

use App\Models\Exams;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class ExamResultsAvailable extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    protected $exam;
    public function __construct(Exams $exams)
    {
        $this->exam = $exams;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'fcm'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->subject("📄 Your Exam Results for {$this->exam->examtype->exam_name} are Available!")
                    ->greeting("Hello, {$notifiable->name}!")
                    ->line("Your results for the **{$this->exam->examtype->exam_name}** exam are now available.")
                    ->action('View Full Results', url('/'))
                    ->line('Congratulations!');
    }

    /**
     * Get the array representation of the notification for database storage.
     * This method is called by the 'database' channel. The returned array is stored
     * as JSON in the 'data' column of the 'notifications' table. This data is used
     * by your frontend for displaying in-app notifications.
     *
     * @param  object  $notifiable The recipient of the notification.
     * @return array<string, mixed> An associative array of data for in-app display.
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'exam_results_available',
            'title' => "🎓 {$this->exam->examtype->exam_name} Results Available",
            'message' => "Your results for the **{$this->exam->examtype->exam_name}**  are now available.",
        ];
    }

     /**
     * Get the Firebase Cloud Messaging (FCM) representation of the notification.
     * This method is called by a custom 'fcm' channel (which you would define
     * in AppServiceProvider and implement in App\Channels\FcmChannel.php).
     * The returned array structures the payload for FCM, which targets both iOS and Android.
     *
     * @param  object  $notifiable The recipient of the notification.
     * @return array<string, mixed> The payload for FCM.
     */
    public function toFcm(object $notifiable): array
    {
        return [
            'notification' => [
                'title' => "🎓 {$this->exam->examtype->exam_name} Results Available",
                'body' => "Your results for the **{$this->exam->examtype->exam_name}**  are now available.",
                'sound' => 'default',
            ],
            'data' => [
                'notification_id' => $this->id,
                'type' => 'exam_results_available',
                'exam_name' => $this->exam->examtype->exam_name,
                'user_id' => $notifiable->id,
            ],
        ];
    }
    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */

     public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => 'exam_results_available',
            'title' => "🎓 {$this->exam->examtype->exam_name} Results Available",
            'message' => "Your results for the **{$this->exam->examtype->exam_name}**  are now available.",
        ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'exam_results_available',
            'title' => "🎓 {$this->exam->examtype->exam_name} Results Available",
            'message' => "Your results for the **{$this->exam->examtype->exam_name}**  are now available.",
        ];
    }
}
