<?php

namespace App\Http\Controllers;

use App\Models\Educationlevels;
use App\Models\Specialty;
use App\Services\StudentPromotionService;
use App\Http\Requests\StudentPromotionRequest;
use App\Models\Student;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;

class studentpromotionController extends Controller
{
    //

    protected StudentPromotionService $studentPromotionService;
    public function __construct(StudentPromotionService $studentPromotionService)
    {
        $this->studentPromotionService = $studentPromotionService;
    }
    public function promote_student_to_another_class(StudentPromotionRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $promoteStudent = $this->studentPromotionService->promoteStudent($request->validated(), $currentSchool);
        return ApiResponseService::success("Student Promoted Sucessfully", $promoteStudent, null, 200);
    }
}
