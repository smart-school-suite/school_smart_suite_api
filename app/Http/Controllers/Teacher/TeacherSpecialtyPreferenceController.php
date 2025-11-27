<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\BulkTeacherPreferenceRequest;
use App\Http\Requests\Teacher\RemoveSpecialtyPreferenceRequest;
use App\Services\ApiResponseService;
use App\Services\Teacher\TeacherSpecialtyPreferenceService;
use Illuminate\Http\Request;
class TeacherSpecialtyPreferenceController extends Controller
{
       protected TeacherSpecialtyPreferenceService $teacherSpecialtyPreferenceService;
    public function __construct(TeacherSpecialtyPreferenceService $teacherSpecialtyPreferenceService)
    {
        $this->teacherSpecialtyPreferenceService = $teacherSpecialtyPreferenceService;
    }

    public function getTeacherSpecailtyPreference(Request $request, string $teacherId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $getSpecailtyPreference = $this->teacherSpecialtyPreferenceService->getTeacherPreference($teacherId, $currentSchool);
        return ApiResponseService::success("Teacher Specialty Preference Fetched Sucessfully", $getSpecailtyPreference, null, 200);
    }

    public function getTeacherAvialableSpecialtyPreference(Request $request, string $teacherId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $availablePreferences = $this->teacherSpecialtyPreferenceService->getAvailableSpecialtiesForTeacher($teacherId, $currentSchool);
        return ApiResponseService::success("Teacher Available Specialty Preference Fetched Sucessfully", $availablePreferences, null, 200);
    }

    public function removeTeacherSpecialtyPreference(RemoveSpecialtyPreferenceRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $this->teacherSpecialtyPreferenceService->removeTeacherSpecialtyPreference($currentSchool, $request->specialty_preferences);
        return ApiResponseService::success("Teacher Specialty Preference Removed Successfully", null, null, 200);
    }

    public function bulkAddTeacherSpecialtyPreference(BulkTeacherPreferenceRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $this->teacherSpecialtyPreferenceService->bulkAddTeacherSpecialtyPreference($currentSchool, $request->validated());
        return ApiResponseService::success("Teacher Specialty Preference Added Successfully");
    }

    public function bulkRemoveTeacherSpecialtyPreference(BulkTeacherPreferenceRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $this->teacherSpecialtyPreferenceService->bulkRemoveTeacherSpecialtyPreference($currentSchool, $request->validated());
        return ApiResponseService::success("Teacher Specialty Preference Removed Successfully");
    }
}
