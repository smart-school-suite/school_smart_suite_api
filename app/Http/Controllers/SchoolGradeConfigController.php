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
}
