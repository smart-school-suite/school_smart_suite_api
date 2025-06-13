<?php

namespace App\Jobs\EmailNotificationJobs;

use App\Mail\AnnoucementEmailNotification;
use App\Models\Announcement;
use App\Models\AnnouncementTargetUser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class EmailAnnouncementNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $announcementId;

    public function __construct(string $announcementId)
    {
        $this->announcementId = $announcementId;
    }

    public function handle(): void
    {
        try {
            $announcement = Announcement::findOrFail($this->announcementId);

            $recipients = AnnouncementTargetUser::with('actorable')
                ->where('announcement_id', $this->announcementId)
                ->get();

            $emails = $this->extractEmails($recipients);

            foreach ($emails as $email) {
                Mail::to($email)->send(new AnnoucementEmailNotification($announcement->title, $announcement->content));
            }

            $announcement->update(['notification_sent_at' => now(), 'status' => 'active']);
        } catch (Throwable $e) {
            Log::error("Failed to send announcement email [ID: {$this->announcementId}]: " . $e->getMessage());
        }
    }

    /**
     * Extract email addresses from polymorphic recipients.
     */
    private function extractEmails(Collection $recipients): array
    {
        return $recipients
            ->map(function ($recipient) {
                return optional($recipient->actorable)->email;
            })
            ->filter() // Remove nulls
            ->unique()
            ->values()
            ->all();
    }
}
