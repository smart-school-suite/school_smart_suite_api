<?php

namespace App\Services\Announcement;

use App\Models\Announcement;
use App\Models\Announcement\AnnouncementRecipient;
use App\Models\AnnouncementTag;
use Illuminate\Support\Collection;
use Throwable;
use App\Exceptions\AppException;
use App\Models\StudentAnnouncement;
use App\Models\TeacherAnnouncement;
use App\Models\SchoolAdminAnnouncement;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Schooladmin;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Events\Actions\AdminActionEvent;
use App\Events\Actions\StudentActionEvent;

class AnnouncementService
{
    public function getAnnouncementEngagementOverview(object $currentSchool, string $announcementId)
    {
        $summary = AnnouncementRecipient::where("school_branch_id", $currentSchool->id)
            ->where("announcement_id", $announcementId)
            ->with([
                'recipient',
                'announcement.announcementCategory',
                'announcement.announcementLabel'
            ])
            ->get();

        $groupedByActor = $summary->groupBy('recipient_type');

        $resolveActorType = function (?string $type): string {
            return match ($type) {
                Student::class, 'student'       => 'student',
                Teacher::class, 'teacher'       => 'teacher',
                Schooladmin::class, 'admin', 'schooladmin' => 'admin',
                default => strtolower(class_basename($type ?? 'unknown')),
            };
        };

        $getActorStats = function (string $modelClass) use ($groupedByActor) {
            $actors = $groupedByActor->get($modelClass, collect());
            $total = $actors->count();
            $seen = $actors->whereNotNull('seen_at')->count();
            $percentage = $total > 0 ? round(($seen / $total) * 100, 2) : 0;

            return [
                'total'           => $total,
                'seen'            => $seen,
                'unseen'          => $total - $seen,
                'seen_percentage' => $percentage,
            ];
        };

        $totalRecipients = $summary->count();
        $totalSeen = $summary->whereNotNull('seen_at')->count();
        $overallSeenPercentage = $totalRecipients > 0 ? round(($totalSeen / $totalRecipients) * 100, 2) : 0;

        return [
            'announcement' => $summary->first()?->announcement,
            'total_recipients'       => $totalRecipients,
            'seen_count'             => $totalSeen,
            'unseen_count'           => $totalRecipients - $totalSeen,
            'overall_seen_percentage' => $overallSeenPercentage,
            'student_stats' => $getActorStats(Student::class),
            'teacher_stats' => $getActorStats(Teacher::class),
            'admin_stats'   => $getActorStats(Schooladmin::class),
            'recipients' => $summary->map(fn($r) => [
                'username' => $r->recipient->username,
                'name' => $r->recipient->name,
                'first_name' => $r->recipient->first_name,
                'last_name' => $r->recipient->last_name,
                'profile_picture' => $r->recipient->profile_picture,
                'seen_at'         => $r->seen_at,
                'actor_type'            => $resolveActorType($r->recipient_type)
            ]),
        ];
    }

    public function getAnnouncementReadUnreadList(object $currentSchool, string $announcementId)
    {
        $studentAnnouncement = StudentAnnouncement::where("school_branch_id", $currentSchool->id)
            ->where("announcement_id", $announcementId)
            ->with(['student'])
            ->get();
        $teacherAnnouncement = TeacherAnnouncement::where("school_branch_id", $currentSchool->id)
            ->where("announcement_id", $announcementId)
            ->with(['teacher'])
            ->get();
        $schoolAdminAnnouncement = SchoolAdminAnnouncement::where("school_branch_id", $currentSchool->id)
            ->where("announcement_id", $announcementId)
            ->with(['schoolAdmin'])
            ->get();

        if ($studentAnnouncement->isEmpty() && $teacherAnnouncement->isEmpty() && $schoolAdminAnnouncement->isEmpty()) {
            throw new AppException(
                "No read/unread status found for this announcement.",
                404,
                "Announcement Status Missing",
                "There is no read/unread status data available for any user type (student, teacher, or admin) for this announcement.",
                "/announcement"
            );
        }

        return [
            'student_announcement' => $studentAnnouncement,
            'teacher_announcement' => $teacherAnnouncement,
            'school_admin_announcement' => $schoolAdminAnnouncement
        ];
    }

    public function updateAnnouncementContent(array $announcementData, object $currentSchool, string $announcementId, object $authAdmin)
    {
        try {
            $announcement = Announcement::where("school_branch_id", $currentSchool->id)
                ->findOrFail($announcementId);

            $dataToUpdate = array_filter($announcementData, function ($value) {
                return !is_null($value) && $value !== '';
            });

            if (isset($dataToUpdate['tag_ids'])) {
                if (!empty($dataToUpdate['tag_ids'])) {
                    $tags = $this->getTags($dataToUpdate);
                    $dataToUpdate['tags'] = json_encode($tags->toArray());
                } else {
                    $dataToUpdate['tags'] = '[]';
                }
            }

            $announcement->update($dataToUpdate);

            return $announcement;
        } catch (Throwable $e) {
            throw $e;
        }
    }

    public function announcementSummary(object $currentSchool)
    {
        $now = now();
        $baseQuery = Announcement::where('school_branch_id', $currentSchool->id);

        return [
            'draft' => (clone $baseQuery)
                ->whereNull('published_at')
                ->count(),

            'scheduled' => (clone $baseQuery)
                ->where('published_at', '>', $now)
                ->count(),

            'active' => (clone $baseQuery)
                ->where('published_at', '<=', $now)
                ->where(function ($query) use ($now) {
                    $query->whereNull('expires_at')
                        ->orWhere('expires_at', '>', $now);
                })
                ->count(),

            'expired' => (clone $baseQuery)
                ->whereNotNull('expires_at')
                ->where('expires_at', '<=', $now)
                ->count(),
        ];
    }
    public function deleteAnnouncement(string $announcementId, object $currentSchool, object $authAdmin)
    {
        try {
            $annoucement = Announcement::where("school_branch_id", $currentSchool->id)
                ->findOrFail($announcementId);
            $annoucement->delete();
            AdminActionEvent::dispatch(
                [
                    "permissions" =>  ["schoolAdmin.announcement.delete"],
                    "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                    "schoolBranch" =>  $currentSchool->id,
                    "feature" => "announcementManagement",
                    "authAdmin" => $authAdmin,
                    "data" => $annoucement,
                    "message" => "Announcement Deleted",
                ]
            );
            //$specialtyIds = collect(json_decode($annoucement->audience)->students)->pluck('student_audience_id')->toArray();
            if (!empty($specialtyIds)) {

                StudentActionEvent::dispatch([
                    'schoolBranch' => $currentSchool->id,
                    'specialtyIds'   => $specialtyIds,
                    'feature'      => 'announcementDelete',
                    'message'      => 'Announcement Deleted',
                    'data'         =>  $annoucement,
                ]);
            }
            return $annoucement;
        } catch (ModelNotFoundException $e) {
            throw new AppException(
                "Announcement not found for deletion",
                404,
                "Announcement Missing",
                "The announcement with ID $announcementId could not be found in this school branch for deletion.",
                "/announcements"
            );
        } catch (Throwable $e) {
            throw new AppException(
                "Failed to delete announcement",
                500,
                "Deletion Error",
                "An unexpected error occurred while attempting to delete the announcement.",
                "/announcements"
            );
        }
    }


    public function getAnnouncementDetails(object $currentSchool, string $announcementId)
    {
        $announcement = Announcement::where("school_branch_id", $currentSchool->id)
            ->with(['announcementLabel', 'announcementCategory', 'announcementAuthor.authorable'])
            ->find($announcementId);

        if (is_null($announcement)) {
            throw new AppException(
                "Announcement not found",
                404,
                "Announcement Details Missing",
                "The announcement with ID $announcementId could not be found for this school branch.",
                "/announcements"
            );
        }

        return $announcement;
    }

    protected function getTags(array $data): Collection
    {
        if (empty($data['tag_ids'])) {
            return collect();
        }

        $tagIds = collect($data['tag_ids'])->pluck('tag_id')->unique()->toArray();
        $tags = AnnouncementTag::whereIn("id", $tagIds)->get();

        if ($tags->count() < count($tagIds)) {
            throw new AppException(
                "Some Tags Not Found",
                404,
                "Some Tags Not Found",
                "One or more selected tags could not be found. Please check that the tags exist and have not been deleted.",
                null
            );
        }

        return $tags;
    }

    public function getAllStudentAnnouncements(object $currentSchool, object $student)
    {
        $student = Student::where("school_branch_id", $currentSchool->id)
            ->find($student->id);
        if (!$student) {
            throw new AppException(
                "Student Not Found",
                404,
                "Student Not Found",
                "Student Not Found the student might have been deleted please verify and try again"
            );
        }
        $announcements = StudentAnnouncement::where("school_branch_id", $currentSchool->id)
            ->where("student_id", $student->id)
            ->with(['announcement.announcementCategory', 'announcement.announcementLabel',])
            ->get();
        return $announcements->sortBy('announcement.created_at')->values();
    }

    public function getStudentAnnouncementLabelId(object $currentSchool, object $student, string $labelId)
    {
        $student = Student::where("school_branch_id", $currentSchool->id)
            ->find($student->id);

        if (!$student) {
            throw new AppException(
                "Student Not Found",
                404,
                "Student Not Found",
                "Student Not Found the student might have been deleted please verify and try again"
            );
        }

        $announcements = StudentAnnouncement::where("school_branch_id", $currentSchool->id)
            ->where("student_id", $student->id)
            ->whereHas('announcement.announcementLabel', function ($query) use ($labelId) {
                $query->where("label_id", $labelId);
            })
            ->with(['announcement.announcementCategory'])
            ->get();

        return $announcements->sortBy('announcement.created_at')->values();
    }


    public function getAnnoucementsByState(object $currentSchool, string $status)
    {
        $validStatuses = ["active", "scheduled", "draft", "expired", "all"];
        $status = strtolower($status);

        if (!in_array($status, $validStatuses)) {
            throw new AppException(
                "Invalid announcement status provided",
                400,
                "Invalid Status",
                "The provided status '$status' is not a valid announcement state. Valid states are: " . implode(', ', $validStatuses) . ".",
                "/announcements"
            );
        }

        try {
            $announcements = Announcement::where("school_branch_id", $currentSchool->id)
                ->when($status !== 'all', function ($query) use ($status) {
                    return $query->where("status", $status);
                })
                ->withCount('recipient')
                ->with(['announcementCategory', 'announcementLabel', 'announcementAuthor.authorable'])
                ->get();

            if ($announcements->isEmpty()) {
                throw new AppException(
                    "No $status announcements found",
                    404,
                    ucwords($status) . " Announcements Missing",
                    "There are no $status announcements available for this school branch.",
                    "/announcements"
                );
            }

            return $announcements;
        } catch (AppException $e) {
            throw $e;
        } catch (Throwable $e) {
            throw new AppException(
                "Failed to retrieve announcements",
                500,
                "Retrieval Error",
                "An unexpected error occurred while attempting to fetch $status announcements.",
                "/announcements"
            );
        }
    }
}
