<?php

namespace App\Http\Controllers;

use App\Services\ApiResponseService;
use App\Services\TeacherSpecailtyPreferenceService;


class TeacherSpecailtyPreferenceController extends Controller
{
    //
    protected TeacherSpecailtyPreferenceService $teacherSpecailtyPreferenceService;
    public function __construct(TeacherSpecailtyPreferenceService $teacherSpecailtyPreferenceService){
        $this->teacherSpecailtyPreferenceService = $teacherSpecailtyPreferenceService;
    }

    public function getTeacherSpecailtyPreference($request, string $teacherId){
         $currentSchool = $request->attributes->get('currentSchool');
         $getSpecailtyPreference = $this->teacherSpecailtyPreferenceService->getTeacherPreference($teacherId, $currentSchool);
         return ApiResponseService::success("Teacher Specailty Preference Fetched Sucessfully", $getSpecailtyPreference, null, 200);
    }
}
