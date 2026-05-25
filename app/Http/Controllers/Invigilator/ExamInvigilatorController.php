<?php

namespace App\Http\Controllers\Invigilator;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExamInvigilator\AssignInvigilatorRequest;
use App\Http\Requests\ExamInvigilator\RemoveInvigilatorRequest;
use App\Services\ApiResponseService;
use App\Services\ExamTimetable\ExamInvigilatorService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ExamInvigilatorController extends Controller
{
    protected ExamInvigilatorService $examInvigilatorService;
    public function __construct(ExamInvigilatorService $examInvigilatorService)
    {
        $this->examInvigilatorService = $examInvigilatorService;
    }
    public function assignInvigilator(AssignInvigilatorRequest $request)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $assignInvigilator = $this->examInvigilatorService->assignInvigilators($request->validated(), $currentSchool);
        return ApiResponseService::success("Invigilator Assigned Successfully", $assignInvigilator, null, 201);
    }
    public function removeAssignedInvigator(RemoveInvigilatorRequest $request)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $removeAssignedInvigilators = $this->examInvigilatorService->removeAssignedInvigilators($request->validated(), $currentSchool);
        return ApiResponseService::success("Invigilators Removed Successfully", $removeAssignedInvigilators, null, 200);
    }
    public function getPotAssignableInvigilators(Request $request, string $examId)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $invigilators = $this->examInvigilatorService->getPotAssignableInvigilators($currentSchool, $examId);
        return ApiResponseService::success("Invigilator Fetched Successfully", $invigilators, null, 200);
    }
    public function getInvigilatorsExamId(Request $request, string $examId)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $invigilators = $this->examInvigilatorService->getInvigilatorsExamId($currentSchool, $examId, $this->getAuthenticatedUser());
        return ApiResponseService::success("Exam Invigilator Fetched Successfully", $invigilators, null, 200);
    }
    private function getAuthenticatedUser(): array
    {
        $user = Auth::user();

        if ($user instanceof Model) {
            return [
                'userId' => $user->id,
                'userType' => get_class($user),
                'authUser' => $user
            ];
        }

        return [
            'userId' => null,
            'userType' => null,
            'authUser' => $user
        ];
    }
}
