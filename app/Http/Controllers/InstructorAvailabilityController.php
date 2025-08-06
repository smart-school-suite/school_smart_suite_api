<?php

namespace App\Http\Controllers;

use App\Models\InstructorAvailability;
use App\Http\Requests\Teacher\UpdateTeacherAvailabilityRequest;
use App\Http\Requests\Teacher\AddTeacherAvailabilityRequest;
use App\Http\Requests\Teacher\BulkUpdateTeacherAvailabilitySlotsRequest;
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
            return ApiResponseService::error($e->getMessage(), null,  500);
        }
    }
    public function createAvailabilityByOtherSlots(Request $request){
        try{
            $currentSchool = $request->attributes->get('currentSchool');
            $targetAvailabilityId = $request->route('targetAvailabilityId');
            $availabilityId = $request->route('availabilityId');
             $this->instructorAvaliabilityService->createAvialabilityByOtherSlots(
                 $targetAvailabilityId,
                 $availabilityId,
                $currentSchool
            );
            return ApiResponseService::success("Instructor Avialability Created Sucessfully", null, null, 201);
        }
        catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 500);
        }
    }
    public function bulkUpdateInstructorAvialabililty(BulkUpdateTeacherAvailabilitySlotsRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        try {
            $updateInstructorAvailability = $this->instructorAvaliabilityService->bulkUpdateInstructorAvailability($request->instructor_availability, $currentSchool);
            return ApiResponseService::success('Instructor Avialabilty Updated Succefully', $updateInstructorAvailability, null, 200);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, $e->getCode() ?: 500);
        }
    }
    public function getSchoolSemestersByTeacherSpecialtyPreference(Request $request, $teacherId){
        $currentSchool = $request->attributes->get('currentSchool');
        $schoolSemesters = $this->instructorAvaliabilityService->getSchoolSemestersByTeacherSpecialtyPreference($currentSchool, $teacherId);
        return ApiResponseService::success("School Semesters Fetched Successfully", $schoolSemesters, null, 200);
    }
    public function deleteAvailabilitySlots(Request $request){
        $teacherId = $request->route('teacherId');
        $availabilityId = $request->route('availabilityId');
        $currentSchool = $request->attributes->get('currentSchool');
        $deleteSlots = $this->instructorAvaliabilityService->deleteAvailabilitySlots($availabilityId,$currentSchool,$teacherId);
        return ApiResponseService::success("All Availability Slots Deleted Successfully", $deleteSlots, null, 200);
    }
    public function getInstructorAvailabilities(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $getAllInstructorAvailability = $this->instructorAvaliabilityService->getInstructorAvailabilities($currentSchool);
        return ApiResponseService::success("teacher availability data fetched successfully", $getAllInstructorAvailability, null, 200);
    }
    public function getInstructorAvailabilitesByTeacher(Request $request, $teacherId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $getMyInstructorAvailability = $this->instructorAvaliabilityService->getInstructorAvailabilitesByTeacher($currentSchool, $teacherId);
        return ApiResponseService::success('Instructor Availabilty Fetched Sucessfully', $getMyInstructorAvailability, null, 200);
    }
    public function getInstructorAvailabilityDetails(Request $request, $availabilityId){
        $currentSchool = $request->attributes->get('currentSchool');
        $avaialbilityDetails = $this->instructorAvaliabilityService->getInstructorAvailabilityDetails($currentSchool, $availabilityId);
        return ApiResponseService::success("Instructor Availability Details Fetched Successfully", $avaialbilityDetails, null, 200);
    }
    public function getAvailabilitySlotsByTeacherAvailability(Request $request, $availabilityId){
        $currentSchool = $request->attributes->get('currentSchool');
        $slots = $this->instructorAvaliabilityService->getAvailabilitySlotsByTeacher($currentSchool, $availabilityId);
        return ApiResponseService::success("Availability Slots By Teacher Fetched Successfully", $slots, null, 200);
    }
}
