<?php

namespace App\Http\Controllers\SemesterTimetable;

use App\Http\Controllers\Controller;
use App\Http\Requests\SemesterTimetable\AvailableTeacherRequest;
use App\Http\Requests\SemesterTimetable\GenerateSlotRequest;
use App\Http\Requests\SemesterTimetable\GetAvailableHallRequest;
use App\Http\Requests\SemesterTimetable\GetCourseRequest;
use App\Services\ApiResponseService;
use App\Services\SemesterTimetable\SemesterTimetableHelperService;
use Illuminate\Http\Request;

class SemesterTimetableHelperController extends Controller
{
    protected SemesterTimetableHelperService $semesterTimetableHelperService;
    public function __construct(
        SemesterTimetableHelperService $semesterTimetableHelperService
    ) {
        $this->semesterTimetableHelperService = $semesterTimetableHelperService;
    }

    public function getAvialableFixedTeachers(AvailableTeacherRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $teachers = $this->semesterTimetableHelperService->getAvailableTeachersFixed($currentSchool, $request->validated());
        return ApiResponseService::success("Available Fixed Teachers Fetched Successfully", $teachers, null, 200);
    }
    public function getAvailableTeachersPref(AvailableTeacherRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $teachers = $this->semesterTimetableHelperService->getAvailableTeachersPref($currentSchool, $request->validated());
        return ApiResponseService::success("Available Teachers Fetched Successfully", $teachers, null, 200);
    }
    public function getTeachersSchooolSemesterId(Request $request, string $teacherId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $teachers = $this->semesterTimetableHelperService->getTeachers($currentSchool, $teacherId);
        return ApiResponseService::success("Teachers Fetched Successfully", $teachers, null, 200);
    }

    public function generateSlots(GenerateSlotRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $slots = $this->semesterTimetableHelperService->generateSlots($request->validated());
        return ApiResponseService::success("Slots Generated Successfully", $slots, null, 200);
    }

    public function getAvailableHalls(GetAvailableHallRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $slots = $this->semesterTimetableHelperService->getAvailableHalls($currentSchool, $request->validated());
        return ApiResponseService::success("Available Halls Fetched Successfully", $slots, null, 200);
    }

    public function getCourses(GetCourseRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $courses = $this->semesterTimetableHelperService->getCourses($currentSchool, $request->validated());
        return ApiResponseService::success("Courses Fetched Successfully", $courses, null, 200);
    }
}
