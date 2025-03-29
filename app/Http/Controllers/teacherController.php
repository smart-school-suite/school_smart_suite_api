<?php

namespace App\Http\Controllers;

use App\Models\Teacher;
use App\Services\ApiResponseService;
use App\Http\Requests\TeacherSpecailtyPreferenceRequest;
use App\Services\TeacherService;
use App\Http\Requests\UpdateTeacherRequest;
use Illuminate\Http\Request;

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

    public function deleteInstructor(Request $request, $teacher_id)
    {
        $deleteTeacher = $this->teacherService->deletetTeacher($teacher_id);
        return ApiResponseService::success("Teacher Deleted Sucessfully", $deleteTeacher, null, 200);
    }

    public function updateInstructor(UpdateTeacherRequest $request, $teacher_id)
    {
        $updateTeacher = $this->teacherService->updateTeacher($request->all(), $teacher_id);
        return ApiResponseService::success("Teacher Updated Sucessfully", $updateTeacher, null, 200);
    }

    public function getTimettableByTeacher(Request $request, $teacher_id)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $teacher_id = $request->route('teacher_id');
        $getTeacherSchedule = $this->teacherService->getTeacherSchedule($teacher_id, $currentSchool);
        return ApiResponseService::success("Teacher Schedule Fetched And Generated Sucessfully", $getTeacherSchedule, null, 200);
    }

    public function getInstructorDetails(Request $request)
    {
        $teacher_id = $request->route('teacher_id');
        $teacherDetails = $this->teacherService->getTeacherDetails($teacher_id);
        return ApiResponseService::success("Teacher Details Fetched Succesfully", $teacherDetails, null, 200);
    }

    public function assignTeacherSpecailtyPreference(TeacherSpecailtyPreferenceRequest $request, $teacherId){
        $currentSchool = $request->attributes->get('currentSchool');
        $assignTeacherSpecailtyPreference = $this->teacherService->addSpecailtyPreference($request->specailties_preference, $currentSchool, $teacherId);
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
}
