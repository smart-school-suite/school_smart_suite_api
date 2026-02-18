<?php

namespace App\Http\Controllers\Constraint;

use App\Http\Controllers\Controller;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use App\Services\Constraint\SemesterTimetableConstraintService;

class SemesterTimetableConstraintController extends Controller
{
    protected SemesterTimetableConstraintService $semesterTimetableConstraintService;
    public function __construct(SemesterTimetableConstraintService $semesterTimetableConstraintService)
    {
        $this->semesterTimetableConstraintService = $semesterTimetableConstraintService;
    }

    public function getAllSemesterTimetableConstraint(Request $request)
    {
        $semesterTimetableConstraints = $this->semesterTimetableConstraintService->getSemesterTimetableConstraints();
        return ApiResponseService::success("Semester Timetable Constraints Fetched Successfully", $semesterTimetableConstraints, null, 200);
    }

    public function getSemesterTimetableConstraintById(Request $request, string $constraintId)
    {
        $semesterTimetableConstraint = $this->semesterTimetableConstraintService->getSemesterTimetableConstraintById($constraintId);
        return ApiResponseService::success("Semester Timetable Constraint Fetched Successfully", $semesterTimetableConstraint, null, 200);
    }

    public function deactivateSemesterTimetableConstraint(Request $request, string $constraintId)
    {
        $semesterTimetableConstraint = $this->semesterTimetableConstraintService->deactivateSemesterTimetableConstraint($constraintId);
        return ApiResponseService::success("Semester Timetable Constraint Deactivated Successfully", $semesterTimetableConstraint, null, 200);
    }

    public function activateSemesterTimetableConstraint(Request $request, string $constraintId)
    {
        $semesterTimetableConstraint = $this->semesterTimetableConstraintService->activateSemesterTimetableConstraint($constraintId);
        return ApiResponseService::success("Semester Timetable Constraint Activated Successfully", $semesterTimetableConstraint, null, 200);
    }
}
