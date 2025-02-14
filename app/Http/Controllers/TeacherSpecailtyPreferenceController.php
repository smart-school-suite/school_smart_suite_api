<?php

namespace App\Http\Controllers;

use App\Services\ApiResponseService;
use App\Services\TeacherSpecailtyPreferenceService;
use Illuminate\Http\Request;

class TeacherSpecailtyPreferenceController extends Controller
{
    //
    protected TeacherSpecailtyPreferenceService $teacherSpecailtyPreferenceService;
    public function __construct(TeacherSpecailtyPreferenceService $teacherSpecailtyPreferenceService){
        $this->teacherSpecailtyPreferenceService = $teacherSpecailtyPreferenceService;
    }

    public function getTeacherSpecailtyPreference(Request $request, string $teacherId){
         $currentSchool = $request->attributes->get('currentSchool');
         $getSpecailtyPreference = $this->teacherSpecailtyPreferenceService->getTeacherPreference($teacherId, $currentSchool);
         return ApiResponseService::success("Teacher Specailty Preference Fetched Sucessfully", $getSpecailtyPreference, null, 200);
    }
}
