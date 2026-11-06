<?php

namespace App\Http\Controllers\JointCourse;

use App\Exceptions\JointCourse\JointCourseSlotConflict;
use App\Http\Controllers\Controller;
use App\Http\Requests\JointCourse\GenerateJointCourseTimetableRequest;
use App\Http\Requests\JointCourse\JointCourseWeekRequest;
use App\Http\Requests\JointCourse\RecommendJointCourseSlotsRequest;
use App\Http\Requests\JointCourse\SaveJointCourseSlotsRequest;
use App\Services\ApiResponseService;
use App\Services\JointCourse\JointCourseTimetableService;
use Illuminate\Http\Request;

class JointCourseTimetableController extends Controller
{
    public function __construct(private JointCourseTimetableService $timetable) {}

    public function getTerms(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        return ApiResponseService::success('Terms Fetched Successfully', $this->timetable->terms($currentSchool), null, 200);
    }

    public function getTerm(Request $request, string $schoolYearId, string $semesterId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        return ApiResponseService::success('Joint Course Timetable Fetched Successfully', $this->timetable->term($currentSchool, $schoolYearId, $semesterId), null, 200);
    }

    public function saveSlots(SaveJointCourseSlotsRequest $request, string $semesterJointCourseId)
    {
        $currentSchool = $request->attributes->get('currentSchool');

        try {
            $result = $this->timetable->saveSlots($currentSchool, $semesterJointCourseId, $request->validated()['slots']);
        } catch (JointCourseSlotConflict $conflict) {
            return ApiResponseService::error($conflict->getMessage(), null, 422, [
                'title' => $conflict->getTitle(),
                'description' => $conflict->getDescription(),
                'path' => null,
                'conflicts' => $conflict->conflicts(),
            ]);
        }

        return ApiResponseService::success('Joint Course Times Saved Successfully', $result, null, 200);
    }

    public function generateTimetable(GenerateJointCourseTimetableRequest $request, string $semesterJointCourseId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        return ApiResponseService::success('Joint Course Timetable Generated Successfully', $this->timetable->generate($currentSchool, $semesterJointCourseId, $request->validated()), null, 200);
    }

    public function confirmTimetable(Request $request, string $semesterJointCourseId, string $proposalId)
    {
        $currentSchool = $request->attributes->get('currentSchool');

        try {
            $result = $this->timetable->confirmProposal($currentSchool, $semesterJointCourseId, $proposalId);
        } catch (JointCourseSlotConflict $conflict) {
            return ApiResponseService::error($conflict->getMessage(), null, 422, [
                'title' => $conflict->getTitle(),
                'description' => $conflict->getDescription(),
                'path' => null,
                'conflicts' => $conflict->conflicts(),
            ]);
        }

        return ApiResponseService::success('Joint Course Times Saved Successfully', $result, null, 200);
    }

    public function getRecommendedSlots(RecommendJointCourseSlotsRequest $request, string $semesterJointCourseId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        return ApiResponseService::success('Recommended Times Fetched Successfully', $this->timetable->recommend($currentSchool, $semesterJointCourseId, $request->validated()), null, 200);
    }

    public function getHallAvailability(JointCourseWeekRequest $request, string $semesterJointCourseId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        return ApiResponseService::success('Hall Availability Fetched Successfully', $this->timetable->hallAvailability($currentSchool, $semesterJointCourseId, $request->validated()), null, 200);
    }

    public function getRecommendedWeek(JointCourseWeekRequest $request, string $semesterJointCourseId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        return ApiResponseService::success('Recommended Times Fetched Successfully', $this->timetable->recommendWeek($currentSchool, $semesterJointCourseId, $request->validated()), null, 200);
    }
}
