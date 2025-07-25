<?php

namespace App\Http\Controllers;

use App\Http\Requests\Course\CourseIdRequest;
use App\Http\Requests\Course\CreateCourseRequest;
use App\Http\Requests\Course\UpdateCourseRequest;
use App\Http\Requests\Course\BulkUpdateCourseRequest;
use App\Http\Resources\CourseResource;
use App\Models\Courses;
use Illuminate\Support\Facades\Validator;
use App\Services\CourseService;
use App\Services\ApiResponseService;
use Exception;
use Illuminate\Http\Request;

class CoursesController extends Controller
{
    protected CourseService $courseService;
    public function __construct(CourseService $courseService)
    {
        $this->courseService = $courseService;
    }
    //
    public function createCourse(CreateCourseRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $course = $this->courseService->createCourse($request->validated(), $currentSchool);
        return ApiResponseService::success('Course Created Succefully', $course, null, 201);
    }

    public function deleteCourse(Request $request, string $courseId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $deleteCourse = $this->courseService->deleteCourse($courseId, $currentSchool);
        return  ApiResponseService::success('Course Deleted Succefully', $deleteCourse, null, 200);
    }

    public function updateCourse(UpdateCourseRequest $request, string $courseId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $updateCourse = $this->courseService->updateCourse($courseId, $request->validated(), $currentSchool);
        return ApiResponseService::success('Course Update Succefully', $updateCourse, null, 200);
    }
    public function getCourses(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $courses = $this->courseService->getCourses($currentSchool);
        return ApiResponseService::success('Courses fetched succefully', CourseResource::collection($courses), null, 200);
    }
    public function getCourseDetails(Request $request)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $courseId = $request->route("courseId");
        $courseDetails = $this->courseService->courseDetails($courseId, $currentSchool);
        return ApiResponseService::success('Course Details fetched succefully', $courseDetails, null, 200);
    }
    public function getBySpecialtyLevelSemester(Request $request)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $specialtyId = $request->route("specialtyId");
        $semesterId = $request->route("semesterId");
        if (!$currentSchool || !$specialtyId || !$semesterId) {
            return ApiResponseService::error('Invalid input parameters', null, 400);
        }
        $coursesData = $this->courseService->getCoursesBySpecialtySemesterAndLevel($currentSchool, $specialtyId, $semesterId);
        return ApiResponseService::success(
            'Courses data fetched successfully',
            $coursesData,
            null,
            200
        );
    }
    public function activateCourse(Request $request, string $courseId){
        $currentSchool = $request->attributes->get("currentSchool");
        $activateCourse = $this->courseService->activateCourse($currentSchool, $courseId);
        return ApiResponseService::success("Course Activated Succesfully", $activateCourse, null, 200);
    }
    public function deactivateCourse(Request $request, string $courseId){
        $currentSchool = $request->attributes->get("currentSchool");
        $deactivateCourse = $this->courseService->deactivateCourse($currentSchool, $courseId);
        return ApiResponseService::success("Course Deactivated Succesfully", $deactivateCourse, null, 200);
    }
    public function getCoursesBySchoolSemester(Request $request, string $semesterId, string $specialtyId){
        $currentSchool = $request->attributes->get("currentSchool");
        $courses = $this->courseService->getCoursesBySchoolSemester($currentSchool, $semesterId, $specialtyId);
        return ApiResponseService::success("Course By School Semester Fetched Succesfully", $courses, null, 200);
    }
    public function bulkUpdateCourse(BulkUpdateCourseRequest $request){
        try{
           $bulkUpdateCourse = $this->courseService->bulkUpdateCourse($request->courses);
           return ApiResponseService::success("Course Updated Succesfully", $bulkUpdateCourse, null, 200);
        }
        catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }
    public function bulkDeleteCourse(CourseIdRequest $request){
       try{
          $bulkDelete = $this->courseService->bulkDeleteCourse($request->courseIds);
          return ApiResponseService::success("Course Deleted Successfully", $bulkDelete, null, 200);
       }
       catch(Exception $e){
        return ApiResponseService::error($e->getMessage(), null, 400);
       }
    }
    public function bulkDeactivateCourse(CourseIdRequest $request){
        try{
            $bulkDeactivate = $this->courseService->bulkDeactivateCourse($request->courseIds);
            return ApiResponseService::success("Course Deactivated Successfully", $bulkDeactivate, null, 200);
        }
        catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }
    public function bulkActivateCourse(CourseIdRequest $request){

        try{
             $bulkActivate = $this->courseService->bulkActivateCourse($request->courseIds);
             return ApiResponseService::success("Course Activated Successfully", $bulkActivate, null, 200);
        }
        catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }
    public function getActiveCourses(Request $request){
        $currentSchool = $request->attributes->get("currentSchool");
        $activeCourses =  $this->courseService->getActiveCourses($currentSchool);
        return ApiResponseService::success("Active Courses Fetched Successfully", $activeCourses, null, 200);
    }

    public function getCoursesBySpecialtySemester(Request $request){
         $currentSchool = $request->attributes->get("currentSchool");
         $specialtyId = $request->route("specialtyId");
         $semesterId = $request->route("semesterId");
         $courses = Courses::where("school_branch_id", $currentSchool->id)
             ->where("specialty_id", $specialtyId)
             ->where("semester_id", $semesterId)
             ->get();
        return ApiResponseService::success("Courses By Specialty Semester Fetched Successfully", CourseResource::collection($courses), null, 200);
    }
}
