<?php

namespace App\Http\Controllers;


use App\Http\Requests\CreateCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Http\Resources\CourseResource;
use App\Services\CourseService;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;

class coursesController extends Controller
{
    protected CourseService $courseService;
    public function __construct(CourseService $courseService)
    {
        $this->courseService = $courseService;
    }
    //
    public function create_course(CreateCourseRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $course = $this->courseService->createCourse($request->validated(), $currentSchool);
        return ApiResponseService::success('Course Created Succefully', $course, null, 201);
    }

    public function delete_course(Request $request, string $course_id)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $deleteCourse = $this->courseService->deleteCourse($course_id, $currentSchool);
        return  ApiResponseService::success('Course Deleted Succefully', $deleteCourse, null, 200);
    }

    public function update_course(UpdateCourseRequest $request, string $course_id)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $updateCourse = $this->courseService->updateCourse($course_id, $request->validated(), $currentSchool);
        return ApiResponseService::success('Course Update Succefully', $updateCourse, null, 200);
    }
    public function get_all_courses_with_no_relation(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $courses = $this->courseService->getCourses($currentSchool);
        return ApiResponseService::success('Courses fetched succefully', CourseResource::collection($courses), null, 200);
    }
    public function courses_details(Request $request)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $course_id = $request->route("course_id");
        $courseDetails = $this->courseService->courseDetails($course_id, $currentSchool);
        return ApiResponseService::success('Course Details fetched succefully', CourseResource::collection($courseDetails), null, 200);
    }

    public function get_specialty_level_semester_courses(Request $request)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $specialtyId = $request->route("specialty_id");
        $semesterId = $request->route("semester_id");
        if (!$currentSchool || !$specialtyId || !$semesterId) {
            return ApiResponseService::error('Invalid input parameters', null, 400);
        }
        $coursesData = $this->courseService->getCoursesBySpecialtySemesterAndLevel($currentSchool, $specialtyId, $semesterId);
        if (!$coursesData->count()) {
            return ApiResponseService::error('No courses data found', null, 404);
        }
        return ApiResponseService::success(
            'Courses data fetched successfully',
            $coursesData,
            null,
            200
        );
    }
}
