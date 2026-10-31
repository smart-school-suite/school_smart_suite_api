<?php

namespace App\Jobs\DataCreationJob;

use App\Models\EventAudience;
use App\Models\SchoolEvent;
use App\Notifications\SchoolEventNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class PublishSchoolEventJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected string $schoolBranchId,
        protected string $schoolEventId
    ) {}

    public function handle(): void
    {
        $schoolEvent = SchoolEvent::where("school_branch_id", $this->schoolBranchId)
            ->with(['eventCategory'])
            ->find($this->schoolEventId);

        if (!$schoolEvent || !in_array($schoolEvent->status, ['scheduled', 'active'], true)) {
            return;
        }

        if ($schoolEvent->status === 'scheduled') {
            $schoolEvent->update(['status' => 'active']);
        }

        EventAudience::where("school_branch_id", $this->schoolBranchId)
            ->where("event_id", $schoolEvent->id)
            ->with('actorable')
            ->chunkById(500, function ($invitees) use ($schoolEvent) {
                $users = $invitees->pluck('actorable')->filter();

                if ($users->isNotEmpty()) {
                    Notification::send($users, new SchoolEventNotification($schoolEvent));
                }
            });

        $schoolEvent->update(['notification_sent_at' => now()]);
    }
}
