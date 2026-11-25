<?php

namespace App\Http\Controllers\Course;

use App\Http\Controllers\Controller;
use App\Http\Requests\Course\ChangeTeacherCourseRequest;
use App\Http\Requests\Course\CreateTeacherCoursePreferenceRequest;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use App\Services\Course\TeacherCoursePreferenceService;
use App\Http\Requests\TeacherCourse\ImportTeacherCourseRequest;
use App\Jobs\Teacher\TeacherCourseImportJob;
use App\Services\Import\ImportJobDispatcher;

class TeacherCoursePreferenceController extends Controller
{
    protected TeacherCoursePreferenceService $teacherCoursePreferenceService;
    public function __construct(TeacherCoursePreferenceService $teacherCoursePreferenceService)
    {
        $this->teacherCoursePreferenceService = $teacherCoursePreferenceService;
    }

    public function getAssignableTeacherCourses(Request $request, string $teacherId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $assignableCourses = $this->teacherCoursePreferenceService->getAssignableTeacherCourses($currentSchool, $teacherId);
        return ApiResponseService::success("Teacher Assignable Courses Fetched Successfully", $assignableCourses, null, 200);
    }

    public function assignTeacherToCourse(CreateTeacherCoursePreferenceRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $authAdmin = $this->resolveUser();
        $assignTeacherCourse = $this->teacherCoursePreferenceService->assignTeacherCoursePreference($currentSchool, $request->validated(), $authAdmin);
        return ApiResponseService::success("Course Assigned to teacher successfully", $assignTeacherCourse, null, 201);
    }

    public function removeTeacherAssignedCourse(CreateTeacherCoursePreferenceRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $authAdmin = $this->resolveUser();
        $removeTeacherCourse = $this->teacherCoursePreferenceService->removeTeacherAssignedCourses($currentSchool, $request->validated(), $authAdmin);
        return ApiResponseService::success("Teacher Course Preference removed Successfully", $removeTeacherCourse, null, 200);
    }

    public function getAssignedTeacherCourses(Request $request, string $teacherId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $assignedCourses = $this->teacherCoursePreferenceService->getAssignedTeacherCourses($currentSchool, $teacherId);
        return ApiResponseService::success("Assigned Teacher Courses Fetched Successfully", $assignedCourses, null, 200);
    }

    public function changeTeacherForCourse(ChangeTeacherCourseRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $changeTeacher = $this->teacherCoursePreferenceService->changeTeacherForCourse($currentSchool, $request->validated());
        return ApiResponseService::success("Teacher Changed Successfully", $changeTeacher, null, 200);
    }

    public function importTeacherCourses(ImportTeacherCourseRequest $request)
    {
        $job = app(ImportJobDispatcher::class)->dispatch(
            TeacherCourseImportJob::class,
            $request->file('file'),
            $request->mapping(),
            $request->attributes->get('currentSchool'),
            $this->resolveUser()
        );

        return ApiResponseService::success('Teacher course import started', $job, null, 202);
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
