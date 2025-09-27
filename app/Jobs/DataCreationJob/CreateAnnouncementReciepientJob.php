<?php

namespace App\Jobs\DataCreationJob;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use App\Models\StudentAnnouncement;
use App\Models\TeacherAnnouncement;
use App\Models\SchoolAdminAnnouncement;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Schooladmin;
use App\Models\Announcement;
use App\Notifications\AnnouncementNotification;

class CreateAnnouncementReciepientJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $schoolBranchId;
    protected Collection $recipients;
    protected string $announcementId;

    public function __construct(string $schoolBranchId, Collection $recipients, string $announcementId)
    {
        $this->schoolBranchId = $schoolBranchId;
        $this->recipients = $recipients;
        $this->announcementId = $announcementId;
    }

    public function handle(): void
    {
        $groupedRecipients = $this->recipients->groupBy(fn ($item) => get_class($item));

        $announcement = Announcement::where("school_branch_id", $this->schoolBranchId)
            ->where("id", $this->announcementId)
            ->with(['announcementCategory', 'announcementLabel'])
            ->firstOrFail();

        if ($groupedRecipients->has(Student::class)) {
            $students = $groupedRecipients->get(Student::class);

            $studentData = $students->map(function ($student) {
                return [
                    'announcement_id' => $this->announcementId,
                    'student_id' => $student->id,
                    'school_branch_id' => $this->schoolBranchId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->toArray();

            StudentAnnouncement::insert($studentData);

            $this->sendUserNotifications($students, $announcement);
        }

        if ($groupedRecipients->has(Teacher::class)) {
            $teachers = $groupedRecipients->get(Teacher::class);

            $teacherData = $teachers->map(function ($teacher) {
                return [
                    'announcement_id' => $this->announcementId,
                    'teacher_id' => $teacher->id,
                    'school_branch_id' => $this->schoolBranchId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->toArray();

            TeacherAnnouncement::insert($teacherData);

            $this->sendUserNotifications($teachers, $announcement);
        }

        if ($groupedRecipients->has(Schooladmin::class)) {
            $admins = $groupedRecipients->get(Schooladmin::class);

            $adminData = $admins->map(function ($admin) {
                return [
                    'announcement_id' => $this->announcementId,
                    'school_admin_id' => $admin->id,
                    'school_branch_id' => $this->schoolBranchId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->toArray();

            SchoolAdminAnnouncement::insert($adminData);

            $this->sendUserNotifications($admins, $announcement);
        }
    }

    private function sendUserNotifications(Collection $users, Announcement $announcement): void
    {
        foreach ($users as $user) {
            $user->notify(new AnnouncementNotification($announcement));
        }
    }
}
