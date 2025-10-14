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
use App\Models\Schooladmin;
use App\Models\SchoolEvent;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Support\Str;

class CreateSchoolEventLikeStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $schoolBranchId;
    protected Collection $recipients;
    protected string $schoolEventId;

    public function __construct(string $schoolBranchId, Collection $recipients, string $schoolEventId)
    {
        $this->schoolBranchId = $schoolBranchId;
        $this->recipients = $recipients;
        $this->schoolEventId = $schoolEventId;
    }


    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $groupedRecipients = $this->recipients->groupBy(fn($item) => get_class($item));

        $schoolEvent = SchoolEvent::where("school_branch_id", $this->schoolBranchId)
            ->where("id", $this->schoolEventId)
            ->with(['eventCategory'])
            ->firstOrFail();

        $schoolEvent->status = 'active';
        $schoolEvent->save();

        if ($groupedRecipients->has(Student::class)) {
            $students = $groupedRecipients->get(Student::class);

            $studentData = $students->map(function ($student) {
                return [
                    'id' => Str::uuid(),
                    'event_id' => $this->schoolEventId,
                    'likable_id' => $student->id,
                    'likable_type' => Student::class,
                    'school_branch_id' => $this->schoolBranchId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->toArray();

            EventLikeStatus::insert($studentData);

            $this->sendUserNotifications($students, $schoolEvent);
        }

        if ($groupedRecipients->has(Teacher::class)) {
            $teachers = $groupedRecipients->get(Teacher::class);

            $teacherData = $teachers->map(function ($teacher) {
                return [
                    'id' => Str::uuid(),
                    'event_id' => $this->schoolEventId,
                    'likable_id' => $teacher->id,
                    'likable_type' => Teacher::class,
                    'school_branch_id' => $this->schoolBranchId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->toArray();

            EventLikeStatus::insert($teacherData);

            $this->sendUserNotifications($teachers, $schoolEvent);
        }

        if ($groupedRecipients->has(Schooladmin::class)) {
            $admins = $groupedRecipients->get(Schooladmin::class);

            $adminData = $admins->map(function ($admin) {
                return [
                    'id' => Str::uuid(),
                    'event_id' => $this->schoolEventId,
                    'likable_id' => $admin->id,
                    'likable_class' => Schooladmin::class,
                    'school_branch_id' => $this->schoolBranchId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->toArray();

            EventLikeStatus::insert($adminData);

            $this->sendUserNotifications($admins, $schoolEvent);
        }
    }

    private function sendUserNotifications(Collection $users, SchoolEvent $schoolEvent): void
    {
        foreach ($users as $user) {
            $user->notify(new SchoolEventNotification($schoolEvent));
        }
    }
}
