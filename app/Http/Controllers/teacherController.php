<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\UpdateProfilePictureRequest;
use App\Services\ApiResponseService;
use App\Http\Requests\Teacher\UpdateTeacherRequest;
use App\Services\TeacherService;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Teacher\AddSpecialtyPreferenceRequest;
use App\Http\Requests\Teacher\TeacherIdRequest;
use Exception;
use Illuminate\Http\Request;
use Throwable;

class TeacherController extends Controller
{
    //
    protected TeacherService $teacherService;
    public function __construct(TeacherService $teacherService)
    {
        $this->teacherService = $teacherService;
    }

    public function getInstructors(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $getInstructorsBySchool = $this->teacherService->getAllTeachers($currentSchool);
        return ApiResponseService::success("Teacher Fetched Successfully", $getInstructorsBySchool, null, 200);
    }
    public function deleteInstructor( $teacherId)
    {
        $deleteTeacher = $this->teacherService->deletetTeacher($teacherId);
        return ApiResponseService::success("Teacher Deleted Sucessfully", $deleteTeacher, null, 200);
    }
    public function updateInstructor(UpdateTeacherRequest $request, $teacherId)
    {
        $updateTeacher = $this->teacherService->updateTeacher($request->all(), $teacherId);
        return ApiResponseService::success("Teacher Updated Sucessfully", $updateTeacher, null, 200);
    }
    public function getTimettableByTeacher(Request $request, $teacherId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $teacherId = $request->route('teacherId');
        $getTeacherSchedule = $this->teacherService->getTeacherSchedule($teacherId, $currentSchool);
        return ApiResponseService::success("Teacher Schedule Fetched And Generated Sucessfully", $getTeacherSchedule, null, 200);
    }
    public function getInstructorDetails(Request $request)
    {
        $teacherId = $request->route('teacherId');
        $teacherDetails = $this->teacherService->getTeacherDetails($teacherId);
        return ApiResponseService::success("Teacher Details Fetched Succesfully", $teacherDetails, null, 200);
    }
    public function assignTeacherSpecailtyPreference(AddSpecialtyPreferenceRequest $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $assignTeacherSpecailtyPreference = $this->teacherService->addSpecailtyPreference($request->specailties_preference, $currentSchool);
        return ApiResponseService::success("Teacher Specailty Preference Added Sucessfully", $assignTeacherSpecailtyPreference, null, 200);
    }

    public function deactivateTeacher($teacherId){
        $deactivateTeacher = $this->teacherService->deactivateTeacher($teacherId);
        return ApiResponseService::success("Teacher Account Deactivated Successfully", $deactivateTeacher, null, 200);
    }
    public function activateTeacher($teacherId){
        $activateTeacher = $this->teacherService->activateTeacher($teacherId);
        return ApiResponseService::success("Teacher Account Activated Successfully", $activateTeacher, null, 200);
    }

    public function bulkDeactivateTeacher(TeacherIdRequest $request){
         try{
             $bulkDeactivateTeacher = $this->teacherService->bulkDeactivateTeacher($request->teacherIds);
             return ApiResponseService::success("Teacher Deactivated Successfully", $bulkDeactivateTeacher, null, 200);
         }
         catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
         }
    }
    public function bulkActivateTeacher(TeacherIdRequest $request){
        try{
            $bulkActivateTeacher = $this->teacherService->bulkActivateTeacher($request->teacherIds);
            return ApiResponseService::success("Teacher Activated Successfully", $bulkActivateTeacher, null, 200);
        }
        catch(Exception $e){
           return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }
    public function bulkDeleteTeacher(TeacherIdRequest $request){
        try{
            $bulkDeleteTeacher = $this->teacherService->bulkDeleteTeacher($request->teacherIds);
            return ApiResponseService::success("Teachers Deleted Successfully", $bulkDeleteTeacher, null, 200);
        }
        catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }

    public function uploadProfilePicture(UpdateProfilePictureRequest $request){
         try{
            $authTeacher = auth()->guard('teacher')->user();
            $updateProfilePicture = $this->teacherService->uploadProfilePicture($request, $authTeacher);
            return ApiResponseService::success("Profile Picture Uploaded Successfully", $updateProfilePicture, null, 200);
         }
         catch(Throwable $e){
             return ApiResponseService::error($e->getMessage(), null, 500);
         }
    }

    public function deleteProfilePicture(Request $request){
          try{
              $authTeacher = auth()->guard('teacher')->user();
              $deleteProfilePicture = $this->teacherService->deleteProfilePicture($authTeacher);
              return ApiResponseService::success("Profile Picture Deleted Successfully", $deleteProfilePicture, null, 200);
          }
          catch(Throwable $e){
             return ApiResponseService::error($e->getMessage(), null, 500);
          }
    }

}
