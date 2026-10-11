<?php

namespace App\Http\Controllers\Audience;

use App\Http\Controllers\Controller;
use App\Services\ApiResponseService;
use App\Services\Audience\AudienceService;
use Illuminate\Http\Request;

class AudienceController extends Controller
{
    protected AudienceService $audienceService;
    public function __construct(AudienceService $audienceService)
    {
        $this->audienceService = $audienceService;
    }

    public function getStudentAudienceBySpecialty(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $students = $this->audienceService->getStudentAudienceBySpecialty($currentSchool);
        return ApiResponseService::success("Student Audience Fetched Successfully", $students, null, 200);
    }

    public function getStudentAudienceByLevel(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $students = $this->audienceService->getStudentAudienceByLevel($currentSchool);
        return ApiResponseService::success("Student Level Audience Fetched Successfully", $students, null, 200);
    }

    public function getStudentAudienceByDepartment(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $students = $this->audienceService->getStudentAudienceByDepartment($currentSchool);
        return ApiResponseService::success("Student Deparment Audience Fetched Successfully", $students, null, 200);
    }

    public function getStudentAudience(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $students = $this->audienceService->getStudents($currentSchool);
        return ApiResponseService::success("Students Fetched Successfully", $students, null, 200);
    }

    public function getTeacherAudienceByDepartment(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $teachers = $this->audienceService->getTeacherAudienceByDepartment($currentSchool);
        return ApiResponseService::success("Teacher Audience By Department Fetched Successfully", $teachers, null, 200);
    }

    public function getTeacherAudienceBySpecialty(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $teachers = $this->audienceService->getTeacherAudienceBySpecialty($currentSchool);
        return ApiResponseService::success("Teacher Audience By Specialty Fetched Successfully", $teachers, null, 200);
    }

    public function getTeacherAudienceByLevel(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $teachers = $this->audienceService->getTeacherAudienceByLevel($currentSchool);
        return ApiResponseService::success("Teacher Audience By Level Fetched Successfully", $teachers, null, 200);
    }

    public function getSchoolAdminAudience(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $schoolAdmins = $this->audienceService->getSchoolAdminAudience($currentSchool);
        return ApiResponseService::success("School Admin Audience Fetched Successfully", $schoolAdmins, null, 200);
    }

    public function getSchoolAudienceSummary(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $summary = $this->audienceService->getAudienceSummary($currentSchool);
        return ApiResponseService::success("Audience Summary Fetched Successfully", $summary, null, 200);
    }

    public function getTeacherAudience(Request $request){
         $currentSchool = $request->attributes->get('currentSchool');
         $teachers = $this->audienceService->getTeacherAudience($currentSchool);
         return ApiResponseService::success("Teacher Audience Fetched Successfully", $teachers, null, 200);
    }
}
