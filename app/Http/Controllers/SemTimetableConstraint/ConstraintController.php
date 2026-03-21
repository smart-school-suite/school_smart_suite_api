<?php

namespace App\Http\Controllers\SemTimetableConstraint;

use App\Http\Controllers\Controller;
use App\Services\SemTimetableConstraint\ConstraintService;
use App\Services\ApiResponseService;
class ConstraintController extends Controller
{
    protected ConstraintService $constraintService;
    public function __construct(ConstraintService $constraintService)
    {
        $this->constraintService = $constraintService;
    }

    public function getConstraintsByCategory()
    {
        $constraintsByCategory = $this->constraintService->getConstraintsByCategory();
        return ApiResponseService::success("Constraints Retrieved Successfully", $constraintsByCategory, null, 200);
    }

    public function getAllConstraints()
    {
        $allConstraints = $this->constraintService->getAllConstraints();
        return ApiResponseService::success("All Constraints Retrieved Successfully", $allConstraints, null, 200);
    }

    public function getConstraintById(string $constraintId)
    {
        $constraint = $this->constraintService->getConstraintById($constraintId);
        return ApiResponseService::success("Constraint Retrieved Successfully", $constraint, null, 200);
    }

}
