<?php

namespace App\Http\Controllers;

use App\Models\InstructorAvailability;
use App\Http\Requests\Teacher\UpdateTeacherAvailabilityRequest;
use App\Http\Requests\Teacher\AddTeacherAvailabilityRequest;
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

    public function createInstructorAvailability(AddTeacherAvailabilityRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        try {
            $createInstructorAvailability = $this->instructorAvaliabilityService->createInstructorAvailability($request->instructor_availability, $currentSchool);
            return ApiResponseService::success("Instructor Avialability Created Sucessfully", $createInstructorAvailability, null, 201);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, $e->getCode() ?: 500);
        }
    }

    public function bulkUpdateInstructorAvialabililty(Request $request)
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

    public function getInstructorAvailability(Request $request, $teacherId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $getMyInstructorAvailability = $this->instructorAvaliabilityService->getInstructorAvailability($currentSchool, $teacherId);
        return ApiResponseService::success('Instructor Availabilty Fetched Sucessfully', $getMyInstructorAvailability, null, 200);
    }

    public function deleteInstructorAvailabilty(Request $request, $availabilityId)
    {
        $availabilty = InstructorAvailability::find($availabilityId);
        $currentSchool = $request->attributes->get('currentSchool');
        $deleteInstructorAvailability = $this->instructorAvaliabilityService->deleteInstructorAvailability($currentSchool, $availabilty);
        return ApiResponseService::success('Teachers availability deleted succefully', $deleteInstructorAvailability, null, 200);
    }

    public function updateInstructorAvailability(UpdateTeacherAvailabilityRequest $request,  $availabilityId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $updateTeacherAvailability = $this->instructorAvaliabilityService->updateInstructorAvailability($request->instructor_availability, $currentSchool, $availabilityId);
        return ApiResponseService::success("Availability updated succesfully", $updateTeacherAvailability, null, 200);
    }
}
