<?php

namespace App\Http\Controllers\Constraint;

use App\Http\Controllers\Controller;
use App\Services\Constraint\ConstraintCategoryService;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;

class ConstraintCategoryController extends Controller
{
    protected ConstraintCategoryService $constraintCategoryService;
    public function __construct(ConstraintCategoryService $constraintCategoryService)
    {
        $this->constraintCategoryService = $constraintCategoryService;
    }

    public function getAllConstraintCategories(Request $request)
    {
        $constraintCategories = $this->constraintCategoryService->getConstraintCategories();
        return ApiResponseService::success("Constraint Categories Fetched Successfully", $constraintCategories, null, 200);
    }

    public function getConstraintCategoryById(Request $request, string $constraintCategoryId)
    {
        $constraintCategory = $this->constraintCategoryService->getConstraintCategoryById($constraintCategoryId);
        return ApiResponseService::success("Constraint Category Fetched Successfully", $constraintCategory, null, 200);
    }

    public function deactivateConstraintCategory(Request $request, string $constraintCategoryId)
    {
        $constraintCategory = $this->constraintCategoryService->deactivateConstraintCategory($constraintCategoryId);
        return ApiResponseService::success("Constraint Category Deactivated Successfully", $constraintCategory, null, 200);
    }

    public function activateConstraintCategory(Request $request, string $constraintCategoryId)
    {
        $constraintCategory = $this->constraintCategoryService->activateConstraintCategory($constraintCategoryId);
        return ApiResponseService::success("Constraint Category Activated Successfully", $constraintCategory, null, 200);
    }

}
