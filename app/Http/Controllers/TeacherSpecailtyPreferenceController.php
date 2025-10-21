<?php

namespace App\Http\Controllers;

use App\Http\Requests\Teacher\BulkTeacherPreferenceRequest;
use App\Http\Requests\Teacher\RemoveSpecialtyPreferenceRequest;
use App\Services\ApiResponseService;
use App\Services\TeacherSpecailtyPreferenceService;
use Illuminate\Http\Request;

class TeacherSpecailtyPreferenceController extends Controller
{
    //
    protected TeacherSpecailtyPreferenceService $teacherSpecailtyPreferenceService;
    public function __construct(TeacherSpecailtyPreferenceService $teacherSpecailtyPreferenceService)
    {
        $this->teacherSpecailtyPreferenceService = $teacherSpecailtyPreferenceService;
    }

    public function getTeacherSpecailtyPreference(Request $request, string $teacherId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $getSpecailtyPreference = $this->teacherSpecailtyPreferenceService->getTeacherPreference($teacherId, $currentSchool);
        return ApiResponseService::success("Teacher Specialty Preference Fetched Sucessfully", $getSpecailtyPreference, null, 200);
    }

    public function getTeacherAvialableSpecialtyPreference(Request $request, string $teacherId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $availablePreferences = $this->teacherSpecailtyPreferenceService->getAvailableSpecialtiesForTeacher($teacherId, $currentSchool);
        return ApiResponseService::success("Teacher Available Specialty Preference Fetched Sucessfully", $availablePreferences, null, 200);
    }

    public function removeTeacherSpecialtyPreference(RemoveSpecialtyPreferenceRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $this->teacherSpecailtyPreferenceService->removeTeacherSpecialtyPreference($currentSchool, $request->specialty_preferences);
        return ApiResponseService::success("Teacher Specialty Preference Removed Successfully", null, null, 200);
    }

    public function bulkAddTeacherSpecialtyPreference(BulkTeacherPreferenceRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $this->teacherSpecailtyPreferenceService->bulkAddTeacherSpecialtyPreference($currentSchool, $request->validated());
        return ApiResponseService::success("Teacher Specialty Preference Added Successfully");
    }

    public function bulkRemoveTeacherSpecialtyPreference(BulkTeacherPreferenceRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $this->teacherSpecailtyPreferenceService->bulkRemoveTeacherSpecialtyPreference($currentSchool, $request->validated());
        return ApiResponseService::success("Teacher Specialty Preference Removed Successfully");
    }
}
