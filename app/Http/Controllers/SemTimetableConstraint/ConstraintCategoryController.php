<?php

namespace App\Http\Controllers\SemTimetableConstraint;

use App\Http\Controllers\Controller;
use App\Services\ApiResponseService;
use App\Services\SemTimetableConstraint\ConstraintCategoryService;

class ConstraintCategoryController extends Controller
{
    protected ConstraintCategoryService $constraintCategoryService;
    public function __construct(ConstraintCategoryService $constraintCategoryService)
    {
        $this->constraintCategoryService = $constraintCategoryService;
    }

    public function getConstraintCategories()
    {
        $constraintCategories = $this->constraintCategoryService->getConstraintCategories();
        return ApiResponseService::success( "Constraint Categories Retrieved Successfully", $constraintCategories, null, 200);
    }

    public function getConstraintCategoryById(string $constraintCategoryId)
    {
        $constraintCategory = $this->constraintCategoryService->getConstraintCategoryById($constraintCategoryId);
        return ApiResponseService::success("Constraint Category Retrieved Successfully", $constraintCategory, null, 200);
    }

    public function activateConstraintCategory(string $constraintCategoryId)
    {
        $activatedConstraintCategory = $this->constraintCategoryService->activateConstraintCategory($constraintCategoryId);
        return ApiResponseService::success("Constraint Category Activated Successfully", $activatedConstraintCategory, null, 200);
    }

    public function deactivateConstraintCategory(string $constraintCategoryId)
    {
        $deactivatedConstraintCategory = $this->constraintCategoryService->deactivateConstraintCategory($constraintCategoryId);
        return ApiResponseService::success("Constraint Category Deactivated Successfully", $deactivatedConstraintCategory, null, 200);
    }
}
