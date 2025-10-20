<?php

namespace App\Jobs\DataCreationJob;

use App\Notifications\SchoolEventNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use App\Models\EventLikeStatus;
use App\Models\Schooladmin; // Note: Typically PascalCase (SchoolAdmin) is preferred for models.
use App\Models\SchoolEvent;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CreateSchoolEventLikeStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $schoolBranchId;
    /** @var Collection<\Illuminate\Database\Eloquent\Model> */
    protected Collection $recipients;
    protected string $schoolEventId;

    /**
     * Create a new job instance.
     *
     * @param string $schoolBranchId
     * @param Collection $recipients A collection of Student, Teacher, and/or Schooladmin models.
     * @param string $schoolEventId
     */
    public function __construct(string $schoolBranchId, Collection $recipients, string $schoolEventId)
    {
        $this->schoolBranchId = $schoolBranchId;
        $this->recipients = $recipients;
        $this->schoolEventId = $schoolEventId;
    }

    public function handle(): void
    {
        $schoolEvent = SchoolEvent::where("school_branch_id", $this->schoolBranchId)
            ->where("id", $this->schoolEventId)
            ->with(['eventCategory'])
            ->firstOrFail();
        $schoolEvent->status = 'active';
        $schoolEvent->save();

        $now = Carbon::now();

        $groupedRecipients = $this->recipients->groupBy(fn($item) => get_class($item));

        $allowedModels = [Student::class, Teacher::class, Schooladmin::class];

        foreach ($allowedModels as $modelClass) {
            if ($groupedRecipients->has($modelClass)) {
                $recipients = $groupedRecipients->get($modelClass);
                $this->createStatusesAndNotify($recipients, $schoolEvent, $modelClass, $now);
            }
        }
    }

    /**
     * Creates bulk EventLikeStatus records and sends notifications for a specific model group.
     *
     * @param Collection $recipients The collection of users (Student, Teacher, or Schooladmin).
     * @param SchoolEvent $schoolEvent The event model.
     * @param string $modelClass The fully qualified class name of the recipient (e.g., Student::class).
     * @param Carbon $timestamp The consistent timestamp to use for created_at/updated_at.
     * @return void
     */
    private function createStatusesAndNotify(
        Collection $recipients,
        SchoolEvent $schoolEvent,
        string $modelClass,
        Carbon $timestamp
    ): void {
        $insertData = $recipients->map(function ($recipient) use ($schoolEvent, $modelClass, $timestamp) {
            return [
                'id' => Str::uuid(),
                'event_id' => $schoolEvent->id,
                'likeable_id' => $recipient->id,
                'likeable_type' => $modelClass,
                'school_branch_id' => $this->schoolBranchId,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        })->toArray();

        EventLikeStatus::insert($insertData);
        $this->sendUserNotifications($recipients, $schoolEvent);
    }

    /**
     * Sends the SchoolEventNotification to a collection of users.
     *
     * @param Collection $users
     * @param SchoolEvent $schoolEvent
     * @return void
     */
    private function sendUserNotifications(Collection $users, SchoolEvent $schoolEvent): void
    {
        foreach ($users as $user) {
            $user->notify(new SchoolEventNotification($schoolEvent));
        }
    }
}
