<?php

namespace App\Http\Controllers\JointCourse;

use App\Http\Controllers\Controller;
use App\Http\Requests\JointCourse\CreateJointCourseSlotRequest;
use App\Http\Requests\JointCourse\SuggestJointCourseSlotRequest;
use App\Http\Requests\JointCourse\UpdateJointCourseRequest;
use App\Http\Requests\JointCourse\UpdateJointCourseSlotRequest;
use App\Services\ApiResponseService;
use App\Services\JointCourse\JointCourseSlotService;
use Illuminate\Http\Request;

class JointCourseSlotController extends Controller
{
    protected JointCourseSlotService $jointCourseSlotService;
    public function __construct(JointCourseSlotService $jointCourseSlotService)
    {
        $this->jointCourseSlotService = $jointCourseSlotService;
    }

    public function deleteJointCourseSlotSlotId(Request $request, string $jointCourseSlotId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $jointCourseSlot = $this->jointCourseSlotService->deleteJointCourseSlot($currentSchool, $jointCourseSlotId);
        return ApiResponseService::success("Joint course slot deleted successfully", $jointCourseSlot, null, 200);
    }
    public function createPreferenceJointCourseSlot(CreateJointCourseSlotRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $this->jointCourseSlotService->createPreferenceJointCourseSlot($request->all(), $currentSchool);
        return ApiResponseService::success("Preference joint course slot created successfully", null, null, 201);
    }

    public function createFixedJointCourseSlot(CreateJointCourseSlotRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $this->jointCourseSlotService->createFixedJointCourseSlot($request->all(), $currentSchool);
        return ApiResponseService::success("Fixed joint course slot created successfully", null, null, 201);
    }

    public function getJointCourseSlotsForSemester(Request $request, string $semesterJointCourseId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $slots = $this->jointCourseSlotService->getJointCourseSlots($currentSchool, $semesterJointCourseId);
        return ApiResponseService::success("Joint course slots retrieved successfully", $slots, null, 200);
    }

    public function suggestJointCourseSlots(SuggestJointCourseSlotRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $suggestedSlots = $this->jointCourseSlotService->suggestSlotsJointCourse($currentSchool, $request->validated());
        return ApiResponseService::success("Suggested joint course slots retrieved successfully", $suggestedSlots, null, 200);
    }

    public function updateFixedJointCourseSlots(UpdateJointCourseSlotRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $this->jointCourseSlotService->updateFixedJointCourseSlot($request->validated(), $currentSchool);
        return ApiResponseService::success("Joint course slots updated successfully", null, null, 200);
    }

    public function updatePreferenceJointCourseSlots(UpdateJointCourseSlotRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $this->jointCourseSlotService->updatePreferenceJointCourseSlot($request->validated(), $currentSchool);
        return ApiResponseService::success("Joint course slots updated successfully", null, null, 200);
    }

}
