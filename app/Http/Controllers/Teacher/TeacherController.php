<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Auth\UpdateProfilePictureRequest;
use App\Services\ApiResponseService;
use App\Http\Requests\Teacher\UpdateTeacherRequest;
use App\Http\Requests\Teacher\AddSpecialtyPreferenceRequest;
use App\Http\Requests\Teacher\TeacherIdRequest;
use App\Services\Teacher\TeacherService;

class TeacherController extends Controller
{
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
    public function deleteInstructor(Request $request, $teacherId)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get('currentSchool');
        $deleteTeacher = $this->teacherService->deletetTeacher($teacherId, $currentSchool, $authAdmin);
        return ApiResponseService::success("Teacher Deleted Sucessfully", $deleteTeacher, null, 200);
    }
    public function updateInstructor(UpdateTeacherRequest $request, $teacherId)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get('currentSchool');
        $updateTeacher = $this->teacherService->updateTeacher($request->all(), $teacherId, $currentSchool, $authAdmin);
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
    public function assignTeacherSpecailtyPreference(AddSpecialtyPreferenceRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $assignTeacherSpecailtyPreference = $this->teacherService->addSpecailtyPreference($request->specailties_preference, $currentSchool);
        return ApiResponseService::success("Teacher Specailty Preference Added Sucessfully", $assignTeacherSpecailtyPreference, null, 200);
    }

    public function deactivateTeacher(Request $request, $teacherId)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get('currentSchool');
        $deactivateTeacher = $this->teacherService->deactivateTeacher($teacherId, $currentSchool, $authAdmin);
        return ApiResponseService::success("Teacher Account Deactivated Successfully", $deactivateTeacher, null, 200);
    }
    public function activateTeacher(Request $request, $teacherId)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get('currentSchool');
        $activateTeacher = $this->teacherService->activateTeacher($teacherId, $currentSchool, $authAdmin);
        return ApiResponseService::success("Teacher Account Activated Successfully", $activateTeacher, null, 200);
    }

    public function bulkDeactivateTeacher(TeacherIdRequest $request)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get('currentSchool');
        $bulkDeactivateTeacher = $this->teacherService->bulkDeactivateTeacher($request->teacherIds, $currentSchool, $authAdmin);
        return ApiResponseService::success("Teacher Deactivated Successfully", $bulkDeactivateTeacher, null, 200);
    }
    public function bulkActivateTeacher(TeacherIdRequest $request)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get('currentSchool');
        $bulkActivateTeacher = $this->teacherService->bulkActivateTeacher($request->teacherIds, $currentSchool, $authAdmin);
        return ApiResponseService::success("Teacher Activated Successfully", $bulkActivateTeacher, null, 200);
    }
    public function bulkDeleteTeacher(TeacherIdRequest $request)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get('currentSchool');
        $bulkDeleteTeacher = $this->teacherService->bulkDeleteTeacher($request->teacherIds, $currentSchool, $authAdmin);
        return ApiResponseService::success("Teachers Deleted Successfully", $bulkDeleteTeacher, null, 200);
    }

    public function uploadProfilePicture(UpdateProfilePictureRequest $request)
    {
        $authTeacher = auth()->guard('teacher')->user();
        $updateProfilePicture = $this->teacherService->uploadProfilePicture($request, $authTeacher);
        return ApiResponseService::success("Profile Picture Uploaded Successfully", $updateProfilePicture, null, 200);
    }

    public function deleteProfilePicture(Request $request)
    {
        $authTeacher = auth()->guard('teacher')->user();
        $deleteProfilePicture = $this->teacherService->deleteProfilePicture($authTeacher);
        return ApiResponseService::success("Profile Picture Deleted Successfully", $deleteProfilePicture, null, 200);
    }

    public function getTeacherBySpecialtyPreference(Request $request, $specialtyId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $getTeachersBySpecialty = $this->teacherService->getTeachersBySpecialtyPreference($specialtyId, $currentSchool);
        return ApiResponseService::success("Teachers Fetched Successfully", $getTeachersBySpecialty, null, 200);
    }
    protected function resolveUser()
    {
        foreach (['student', 'teacher', 'schooladmin'] as $guard) {
            $user = request()->user($guard);
            if ($user !== null) {
                return $user;
            }
        }
        return null;
    }
}
