<?php

namespace App\Http\Controllers\SemTimetableConstraint;

use App\Http\Controllers\Controller;
use App\Services\SemTimetableConstraint\ConstraintTypeService;
use App\Services\ApiResponseService;
class ConstraintTypeController extends Controller
{
   protected ConstraintTypeService $constraintTypeService;
    public function __construct(ConstraintTypeService $constraintTypeService)
    {
        $this->constraintTypeService = $constraintTypeService;
    }

    public function getConstraintTypes()
    {
        $constraintTypes = $this->constraintTypeService->getConstraintTypes();
        return ApiResponseService::success("Constraint Types Retrieved Successfully", $constraintTypes,  null, 200);
    }

    public function getConstraintTypeById(string $constraintTypeId)
    {
        $constraintType = $this->constraintTypeService->getConstraintTypeById($constraintTypeId);
        return ApiResponseService::success("Constraint Type Retrieved Successfully", $constraintType, null, 200);
    }

    public function activateConstraintType(string $constraintTypeId)
    {
        $activatedConstraintType = $this->constraintTypeService->activateConstraintType($constraintTypeId);
        return ApiResponseService::success("Constraint Type Activated Successfully", $activatedConstraintType, null, 200);
    }

    public function deactivateConstraintType(string $constraintTypeId)
    {
        $deactivatedConstraintType = $this->constraintTypeService->deactivateConstraintType($constraintTypeId);
        return ApiResponseService::success("Constraint Type Deactivated Successfully", $deactivatedConstraintType, null, 200);
    }

}
