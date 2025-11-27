<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Student\StudentPromotionRequest;
use App\Services\ApiResponseService;
use App\Services\StudentPromotion\StudentPromotionService;

class StudentPromotionController extends Controller
{
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
