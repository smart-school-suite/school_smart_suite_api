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

    public function getTimetableVersions(Request $request, string $schoolSemesterId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $timetableVersions = $this->semesterTimetableVersionService->getVersionSchoolSemesterId($currentSchool, $schoolSemesterId);
        return ApiResponseService::success("Timetable Versions Fetched Successfully", $timetableVersions, null, 200);
    }

    public function deleteTimetableVersion(Request $request, string $versionId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $deleteVersion = $this->semesterTimetableVersionService->deleteVersion($currentSchool, $versionId);
        return ApiResponseService::success("Timetable Version Deleted Successfully", $deleteVersion, null, 200);
    }

    public function createTimetableVersion(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $data = $request->validate([
            'school_semester_id' => 'required|string',
        ]);
        $createVersion = $this->semesterTimetableVersionService->createVersion($currentSchool, $data);
        return ApiResponseService::success("Timetable Version Created Successfully", $createVersion, null, 201);
    }

    public function getSemesterTimetableSlotsVersionId(Request $request, string $versionId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $timetableSlots = $this->semesterTimetableVersionService->getTimetableSlotsVersionId($versionId, $currentSchool);
        return ApiResponseService::success("Timetable Slots Fetched Successfully", $timetableSlots, null, 200);
    }
}
