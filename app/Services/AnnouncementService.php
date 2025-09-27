<?php

namespace App\Services;

use App\Models\Announcement;
use App\Models\Department;
use App\Models\Educationlevels;
use App\Models\Specialty;
use Exception;
use Throwable;

class AnnouncementService
{
    public function updateAnnouncementContent($announcementData, $currentSchool, $announcementId)
    {
        try {
            $annoucement = Announcement::where("school_branch_id", $currentSchool->id)->findOrFail($announcementId);
            $cleanData = array_filter($announcementData);
            $annoucement->update($cleanData);
            return $annoucement;
        } catch (Throwable $e) {
            throw $e;
        }
    }

    public function deleteAnnouncement($announcementId, $currentSchool)
    {
        try {
            $annoucement = Announcement::where("school_branch_id", $currentSchool->id)->findOrFail($announcementId);
            $annoucement->delete();
            return $annoucement;
        } catch (Throwable $e) {
            throw $e;
        }
    }

    public function getAnnoucementsByState(object $currentSchool, string $status)
    {
        try {
            if ($status === "active") {
                return Announcement::where("school_branch_id", $currentSchool->id)
                    ->where("status", "active")
                    ->get();
            }
            if ($status === "scheduled") {
                return Announcement::where("school_branch_id", $currentSchool->id)
                    ->where("status", "scheduled")
                    ->get();
            }
            if ($status === "draft") {
                return Announcement::where("school_branch_id", $currentSchool->id)
                    ->where("status", "draft")
                    ->get();
            }
            if ($status === "expired") {
                return Announcement::where("school_branch_id", $currentSchool->id)
                    ->where("status", "expired")
                    ->get();
            }
        } catch (Throwable $e) {
            throw $e;
        }
    }

    public function getAnnouncementDetails($currentSchool, $announcementId)
    {
        return Announcement::where("school_branch_id", $currentSchool->id)
            ->with(['announcementLabel', 'announcementTag', 'announcementCategory'])
            ->where('id', $announcementId)
            ->get();
    }


}
