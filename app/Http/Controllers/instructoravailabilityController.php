<?php

namespace App\Http\Controllers;

use App\Models\InstructorAvailability;
use App\Http\Requests\InstructorAvialabiltyRequest;
use App\Http\Requests\UpdateInstructorAvailability;
use App\Services\ApiResponseService;
use App\Services\InstructorAvaliabilityService;
use Illuminate\Http\Request;
use Exception;

class InstructorAvailabilityController extends Controller
{
    //
    protected InstructorAvaliabilityService $instructorAvaliabilityService;
    public function __construct(InstructorAvaliabilityService $instructorAvaliabilityService)
    {
        $this->instructorAvaliabilityService = $instructorAvaliabilityService;
    }

    public function createInstructorAvailability(InstructorAvialabiltyRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        try {
            $createInstructorAvailability = $this->instructorAvaliabilityService->createInstructorAvailability($request->instructor_availability, $currentSchool);
            return ApiResponseService::success("Instructor Avialability Created Sucessfully", $createInstructorAvailability, null, 201);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, $e->getCode() ?: 500);
        }
    }

    public function bulkUpdateInstructorAvialabililty(UpdateInstructorAvailability $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        try {
            $updateInstructorAvailability = $this->instructorAvaliabilityService->bulkUpdateInstructorAvailability($request->instructor_availability, $currentSchool);
            return ApiResponseService::success('Instructor Avialabilty Updated Succefully', $updateInstructorAvailability, null, 200);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, $e->getCode() ?: 500);
        }
    }
    public function getAllInstructorAvailability(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $getAllInstructorAvailability = $this->instructorAvaliabilityService->getAllInstructorAvailabilties($currentSchool);
        return ApiResponseService::success("teacher availability data fetched successfully", $getAllInstructorAvailability, null, 200);
    }

    public function getInstructorAvailability(Request $request, $teacher_id)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $getMyInstructorAvailability = $this->instructorAvaliabilityService->getInstructorAvailability($currentSchool, $teacher_id);
        return ApiResponseService::success('Instructor Availabilty Fetched Sucessfully', $getMyInstructorAvailability, null, 200);
    }

    public function deleteInstructorAvailabilty(Request $request, $availabilty_id)
    {
        $availabilty = InstructorAvailability::find($availabilty_id);
        $currentSchool = $request->attributes->get('currentSchool');
        $deleteInstructorAvailability = $this->instructorAvaliabilityService->deleteInstructorAvailability($currentSchool, $availabilty);
        return ApiResponseService::success('Teachers availability deleted succefully', $deleteInstructorAvailability, null, 200);
    }

    public function updateInstructorAvailability(Request $request,  $availabilty_id)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $updateTeacherAvailability = $this->instructorAvaliabilityService->updateInstructorAvailability($request->instructor_availability, $currentSchool, $availabilty_id);
        return ApiResponseService::success("Availability updated succesfully", $updateTeacherAvailability, null, 200);
    }
}
