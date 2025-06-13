<?php

namespace App\Jobs\EmailNotificationJobs;

use App\Mail\EventEmailNotification;
use App\Models\EventInvitedMember;
use App\Models\SchoolEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

class EmailEventNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected string $schoolEventId;
    public function __construct(string $schoolEventId)
    {
        $this->schoolEventId = $schoolEventId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $schoolEvent = SchoolEvent::findOrFail($this->schoolEventId);

            $recipients = EventInvitedMember::with('actorable')
                ->where('event_id', $this->schoolEventId)
                ->get();

            $emails = $this->extractEmails($recipients);

            foreach ($emails as $email) {
                Mail::to($email)->send(new EventEmailNotification($schoolEvent->title, $schoolEvent->content));
            }

            $schoolEvent->update(['notification_sent_at' => now(), 'status' => 'active']);
        } catch (Throwable $e) {
            Log::error("Failed to send announcement email [ID: {$this->schoolEventId}]: " . $e->getMessage());
        }
    }

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
