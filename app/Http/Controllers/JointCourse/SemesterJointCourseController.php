<?php

namespace App\Http\Controllers\JointCourse;

use App\Http\Controllers\Controller;
use App\Services\ApiResponseService;
use App\Services\JointCourse\SemesterJointCourseService;
use Illuminate\Http\Request;

class SemesterJointCourseController extends Controller
{
    protected SemesterJointCourseService $semesterJointCourseService;
    public function __construct(SemesterJointCourseService $semesterJointCourseService)
    {
        $this->semesterJointCourseService = $semesterJointCourseService;
    }

    public function getSemesterJointCourses(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $semesterJointCourses = $this->semesterJointCourseService->getSemesterJointCourse($currentSchool);
        return ApiResponseService::success("Semester Joint Courses Fetched Successfully", $semesterJointCourses, null, 200);
    }
}
