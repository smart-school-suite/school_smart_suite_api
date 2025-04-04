<?php

namespace App\Http\Controllers;
use App\Models\GradesCategory;
use App\Http\Requests\CreateGradesCategoryRequest;
use App\Http\Requests\UpdateGradesCategoryRequest;
use App\Services\ApiResponseService;
use App\Services\GradesCategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GradesCategoryController extends Controller
{
    //
    protected GradesCategoryService $gradesCategoryService;
    public function __construct(GradesCategoryService $gradesCategoryService) {
        $this->gradesCategoryService = $gradesCategoryService;
    }
    public function createCategory(CreateGradesCategoryRequest $request): JsonResponse
    {
        $createCategory = $this->gradesCategoryService->createGradeCategory($request->validated());
        return ApiResponseService::success("Grades Category Created Succesfully", $createCategory, null, 201);
    }

    // Update an existing category
    public function updateCategory(UpdateGradesCategoryRequest $request, $categoryId): JsonResponse
    {
       $updateCategory = $this->gradesCategoryService->UpdateGradeCategory($request->validated(), $categoryId);
       return ApiResponseService::success("Grades Category Updated Succesfully", $updateCategory, null, 200);
    }

    // Delete a category
    public function deleteCategory($categoryId): JsonResponse
    {
        $deleteCategory = $this->gradesCategoryService->deleteGradeCategory($categoryId);
        return ApiResponseService::success("Grades Category Deleted Succesfully", $deleteCategory, null, 200);
    }

    // Get a specific category or all categories
    public function getGradesCategory(){
        $getCategory = $this->gradesCategoryService->getGradesCategory();
        return ApiResponseService::success("Grades Categories Fetched Succesfully", $getCategory, null, 200);
    }

}
