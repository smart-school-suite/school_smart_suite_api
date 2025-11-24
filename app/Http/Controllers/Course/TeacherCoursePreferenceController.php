<?php

namespace App\Http\Controllers\Course;

use App\Http\Controllers\Controller;
use App\Http\Requests\Course\CreateTeacherCoursePreferenceRequest;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use App\Services\Course\TeacherCoursePreferenceService;
class TeacherCoursePreferenceController extends Controller
{
    protected TeacherCoursePreferenceService $teacherCoursePreferenceService;
    public function __construct(TeacherCoursePreferenceService $teacherCoursePreferenceService){
         $this->teacherCoursePreferenceService = $teacherCoursePreferenceService;
    }

    public function getAssignableTeacherCourses(Request $request, $teacherId){
         $currentSchool = $request->attributes->get('currentSchool');
         $assignableCourses = $this->teacherCoursePreferenceService->getAssignableTeacherCourses($currentSchool, $teacherId);
         return ApiResponseService::success("Teacher Assignable Courses Fetched Successfully", $assignableCourses, null, 200);
    }

    public function assignTeacherToCourse(CreateTeacherCoursePreferenceRequest $request){
         $currentSchool = $request->attributes->get('currentSchool');
         $assignTeacherCourse = $this->teacherCoursePreferenceService->assignTeacherCoursePreference($currentSchool, $request->validated());
         return ApiResponseService::success("Course Assigned to teacher successfully", $assignTeacherCourse, null, 201);
    }

    public function removeTeacherAssignedCourse(CreateTeacherCoursePreferenceRequest $request){
         $currentSchool = $request->attributes->get('currentSchool');
         $removeTeacherCourse = $this->teacherCoursePreferenceService->removeTeacherAssignedCourses($currentSchool, $request->validated());
         return ApiResponseService::success("Teacher Course Preference removed Successfully", $removeTeacherCourse, null, 200);
    }

    public function getAssignedTeacherCourses(Request $request, $teacherId){
         $currentSchool = $request->attributes->get('currentSchool');
         $assignedCourses = $this->teacherCoursePreferenceService->getAssignedTeacherCourses($currentSchool, $teacherId);
         return ApiResponseService::success("Assigned Teacher Courses Fetched Successfully", $assignedCourses, null, 200);
    }
}
