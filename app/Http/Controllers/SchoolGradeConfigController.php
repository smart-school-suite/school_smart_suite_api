<?php

namespace App\Http\Controllers;

use App\Services\ApiResponseService;
use App\Services\SchoolGradesConfigService;
use App\Http\Resources\SchoolGradeConfigResource;
use Illuminate\Http\Request;

class SchoolGradeConfigController extends Controller
{
    //
    protected SchoolGradesConfigService $schoolGradesConfigService;
    public function __construct(SchoolGradesConfigService  $schoolGradesConfigService){
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

    public function createGradingBySchoolGradeConfig(Request $request){

    }
}
