<?php

namespace App\Http\Controllers;

use App\Services\StudentPromotionService;
use App\Http\Requests\Student\StudentPromotionRequest;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;

class StudentPromotionController extends Controller
{
    //

    protected StudentPromotionService $studentPromotionService;
    public function __construct(StudentPromotionService $studentPromotionService)
    {
        $this->studentPromotionService = $studentPromotionService;
    }
    public function promoteStudent(StudentPromotionRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $promoteStudent = $this->studentPromotionService->promoteStudent($request->validated(), $currentSchool);
        return ApiResponseService::success("Student Promoted Sucessfully", $promoteStudent, null, 200);
    }
}
