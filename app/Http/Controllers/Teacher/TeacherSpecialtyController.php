<?php

namespace App\Http\Controllers\Teacher;

use App\Services\Import\ImportJobDispatcher;

use App\Http\Controllers\Controller;
use App\Http\Requests\TeacherSpecialty\AssignTeacherRequest;
use App\Http\Requests\TeacherSpecialty\ImportTeacherSpecialtyRequest;
use App\Http\Requests\TeacherSpecialty\RemoveTeacherRequest;
use App\Jobs\Teacher\TeacherSpecialtyImportJob;
use App\Services\ApiResponseService;
use App\Services\Teacher\TeacherSpecialtyService;
use App\Models\Teacher\TeacherSpecialty;
use Illuminate\Http\Request;

class TeacherSpecialtyController extends Controller
{
    protected TeacherSpecialtyService $teacherSpecialtyService;
    public function __construct(TeacherSpecialtyService $teacherSpecialtyService)
    {
        $this->teacherSpecialtyService = $teacherSpecialtyService;
    }

    public function getTeacherSpecialtyByTeacherId(Request $request, string $teacherId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $specialties = $this->teacherSpecialtyService->getTeacherSpecialtyTeacherId($currentSchool, $teacherId);
        return ApiResponseService::success("Teacher Specialties Fetched Successfully", $specialties, null, 200);
    }

    public function assignTeachers(AssignTeacherRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $assignTeacher = $this->teacherSpecialtyService->assignTeachers($currentSchool, $request->validated());
        return ApiResponseService::success("Teacher Assigned Successfully", $assignTeacher, null, 200);
    }

    public function removeAssignedTeachers(RemoveTeacherRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $removeAssignedTeacher = $this->teacherSpecialtyService->removeTeachers($currentSchool, $request->validated());
        return ApiResponseService::success("Teacher Removed Successfully", $removeAssignedTeacher, null, 200);
    }

    public function getAssignableTeachers(Request $request, string $specialtyId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $assignableTeachers = $this->teacherSpecialtyService->getAssignableTeachers($currentSchool, $specialtyId);
        return ApiResponseService::success("Assignable Teachers Fetched Successfully", $assignableTeachers, null, 200);
    }

    public function importTeacherSpecialty(ImportTeacherSpecialtyRequest $request)
    {
        $job = app(ImportJobDispatcher::class)->dispatch(
            TeacherSpecialtyImportJob::class,
            $request->file('file'),
            $request->mapping(),
            $request->attributes->get('currentSchool'),
            $this->resolveUser()
        );

        return ApiResponseService::success('Teacher specialty import started', $job, null, 202);
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
