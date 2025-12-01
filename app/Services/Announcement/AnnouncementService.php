<?php

namespace App\Services\Announcement;

use App\Models\Announcement;
use App\Models\AnnouncementEngagementStat;
use App\Models\AnnouncementTag;
use Illuminate\Support\Collection;
use Throwable;
use App\Exceptions\AppException;
use App\Models\StudentAnnouncement;
use App\Models\TeacherAnnouncement;
use App\Models\SchoolAdminAnnouncement;
use App\Models\Student;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Events\Actions\AdminActionEvent;
class AnnouncementService
{
    public function getAnnouncementEngagementOverview($currentSchool, $announcementId)
    {
        $engagmentOverview = AnnouncementEngagementStat::where("school_branch_id", $currentSchool->id)
            ->where("announcement_id", $announcementId)
            ->first();

        if (is_null($engagmentOverview)) {
            throw new AppException(
                "Announcement engagement overview not found",
                404,
                "Engagement Overview Missing",
                "The engagement statistics for this announcement are not available.",
                "/announcements"
            );
        }

        return $engagmentOverview;
    }

    public function getAnnouncementReadUnreadList($currentSchool, $announcementId)
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

    public function updateAnnouncementContent($announcementData, $currentSchool, $announcementId, $authAdmin)
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
            AdminActionEvent::dispatch(
                [
                    "permissions" =>  ["schoolAdmin.announcement.update"],
                    "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                    "schoolBranch" =>  $currentSchool->id,
                    "feature" => "announcementManagement",
                    "authAdmin" => $authAdmin,
                    "data" => $announcement,
                    "message" => "Announcement Content Updated",
                ]
            );
            return $announcement;
        } catch (Throwable $e) {
            throw $e;
        }
    }

    public function deleteAnnouncement($announcementId, $currentSchool, $authAdmin)
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

    public function getAnnoucementsByState(object $currentSchool, string $status)
    {
        $validStatuses = ["active", "scheduled", "draft", "expired"];
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
                ->where("status", $status)
                ->with(['announcementCategory', 'announcementLabel'])
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

    public function getAnnouncementDetails($currentSchool, $announcementId)
    {
        $announcement = Announcement::where("school_branch_id", $currentSchool->id)
            ->with(['announcementLabel', 'announcementCategory'])
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

    public function getAllStudentAnnouncements($currentSchool, $student)
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
            ->with(['announcement.announcementCategory', 'announcement.announcementLabel'])
            ->get();
        return $announcements->sortBy('announcement.created_at')->values();
    }

    public function getStudentAnnouncementLabelId($currentSchool, $student, $labelId)
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
}
