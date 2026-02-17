<?php

namespace App\Http\Controllers\SemesterTimetable;

use App\Http\Controllers\Controller;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use App\Services\SemesterTimetable\SemesterTimetableVersionService;

class SemesterTimetableVersionController extends Controller
{
    protected SemesterTimetableVersionService $semesterTimetableVersionService;
    public function __construct(SemesterTimetableVersionService $semesterTimetableVersionService)
    {
        $this->semesterTimetableVersionService = $semesterTimetableVersionService;
    }

    public function getTimetableVersions(Request $request, string $schoolSemesterId, string $versionId)
    {
        $currentSchool = $request->get("currentSchool");
        $timetableVersions = $this->semesterTimetableVersionService->getTimetableSlotsVersionId($schoolSemesterId, $versionId, $currentSchool);
        return ApiResponseService::success(
            "Timetable version retrieved successfully.",
            $timetableVersions,
            null,
            200
        );
    }

    public function deleteTimetableVersion(Request $request, string $schoolSemesterId, string $versionId)
    {
        $currentSchool = $request->get("currentSchool");
        $timetableVersions = $this->semesterTimetableVersionService->deleteTimetableVersion($versionId, $schoolSemesterId, $currentSchool);
        return ApiResponseService::success(
            "Timetable Version Deleted Successfully.",
            $timetableVersions,
            null,
            200
        );
    }

    public function deleteTimetableVersionSlot(Request $request, string $slotId)
    {
        $currentSchool = $request->get("currentSchool");
        $timetableSlot = $this->semesterTimetableVersionService->deleteTimetableVersionSlot($slotId, $currentSchool);
        return ApiResponseService::success(
            "Timetable Slot Deleted Successfully.",
            $timetableSlot,
            null,
            200
        );
    }

    public function getTimetableVersionSlotDetail(Request $request, string $slotId)
    {
        $currentSchool = $request->get("currentSchool");
        $timetableSlot = $this->semesterTimetableVersionService->getTimetableVersionSlotDetail($slotId, $currentSchool);
        return ApiResponseService::success(
            "Timetable Slot Retrieved Successfully.",
            $timetableSlot,
            null,
            200
        );
    }

    public function getTimetableSlotsVersionId(Request $request, string $schoolSemesterId, string $versionId)
    {
        $currentSchool = $request->get("currentSchool");
        $timetableVersions = $this->semesterTimetableVersionService->getTimetableSlotsVersionId($schoolSemesterId, $versionId, $currentSchool);
        return ApiResponseService::success(
            "Timetable version retrieved successfully.",
            $timetableVersions,
            null,
            200
        );
    }
}
