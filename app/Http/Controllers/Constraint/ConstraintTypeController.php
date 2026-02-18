<?php

namespace App\Http\Controllers\Constraint;

use App\Http\Controllers\Controller;
use App\Services\ApiResponseService;
use App\Services\Constraint\ConstraintTypeService;
use Illuminate\Http\Request;

class ConstraintTypeController extends Controller
{
    protected ConstraintTypeService $constraintTypeService;
    public function __construct(ConstraintTypeService $constraintTypeService)
    {
        $this->constraintTypeService = $constraintTypeService;
    }

    public function getAllConstraintTypes(Request $request)
    {
        $constraintTypes = $this->constraintTypeService->getConstraintTypes();
        return ApiResponseService::success("Constraint Types Fetched Successfully", $constraintTypes, null, 200);
    }

    public function getConstraintTypeById(Request $request, string $constraintTypeId)
    {
        $constraintType = $this->constraintTypeService->getConstraintTypeById($constraintTypeId);
        return ApiResponseService::success("Constraint Type Fetched Successfully", $constraintType, null, 200);
    }

    public function deactivateConstraintType(Request $request, string $constraintTypeId)
    {
        $constraintType = $this->constraintTypeService->deactivateConstraintType($constraintTypeId);
        return ApiResponseService::success("Constraint Type Deactivated Successfully", $constraintType, null, 200);
    }

    public function activateConstraintType(Request $request, string $constraintTypeId)
    {
        $constraintType = $this->constraintTypeService->activateConstraintType($constraintTypeId);
        return ApiResponseService::success("Constraint Type Activated Successfully", $constraintType, null, 200);
    }
}
