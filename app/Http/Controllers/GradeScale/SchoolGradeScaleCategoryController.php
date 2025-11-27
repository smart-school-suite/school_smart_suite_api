<?php

namespace App\Http\Controllers\GradeScale;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ApiResponseService;
use App\Http\Resources\SchoolGradeConfigResource;
use App\Services\Grade\SchoolGradeConfigService;
class SchoolGradeScaleCategoryController extends Controller
{
       protected SchoolGradeConfigService $schoolGradesConfigService;
    public function __construct(SchoolGradeConfigService  $schoolGradesConfigService){
        $this->schoolGradesConfigService = $schoolGradesConfigService;
     }

     public function getSchoolGradesConfig(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $getSchoolGradesConfig =  $this->schoolGradesConfigService->getSchoolGradeConfig($currentSchool);
        return ApiResponseService::success("School Grades Config fetched Succesfully",  SchoolGradeConfigResource::collection($getSchoolGradesConfig), null, 200);
     }

    public function getGradingBySchoolGradeCongfig(Request $request, $schoolGradeConfigId){
        $currentSchool = $request->attributes->get('currentSchool');
        $gradingBySchoolGradeConfig = $this->schoolGradesConfigService->getGradingBySchoolGradeCongfig($currentSchool, $schoolGradeConfigId);
        return ApiResponseService::success("Grading By School Grade Config Fetched Successfully", $gradingBySchoolGradeConfig, null, 200);
    }

}
