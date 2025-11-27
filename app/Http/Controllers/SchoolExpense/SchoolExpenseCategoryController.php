<?php

namespace App\Http\Controllers\SchoolExpense;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ApiResponseService;
use App\Services\SchoolExpenses\ExpenseCategoryService;
use App\Http\Requests\ExpensesCategory\CreateExpensesCategoryRequest;
use App\Http\Requests\ExpensesCategory\UpdateExpensesCategoryRequest;

class SchoolExpenseCategoryController extends Controller
{
    protected ExpenseCategoryService $schoolExpensesCategoryService;
    public function __construct(ExpenseCategoryService $schoolExpensesCategoryService)
    {
        $this->schoolExpensesCategoryService = $schoolExpensesCategoryService;
    }
    public function createCategory(CreateExpensesCategoryRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $createCategoryExpenses = $this->schoolExpensesCategoryService->createSchoolExpense($request->validated(), $currentSchool);
        return ApiResponseService::success("Category Expenses Created Sucessfully", $createCategoryExpenses, null, 201);
    }

    public function updateCategory(UpdateExpensesCategoryRequest $request, $categoryId)
    {
        $updateCategoryExpenses = $this->schoolExpensesCategoryService->updateSchoolExpenseCategory($request->validated(),  $categoryId);
        return ApiResponseService::success("Expenses Category Updated Sucessfully", $updateCategoryExpenses, null, 200);
    }


    public function deleteCategory(string $categoryId)
    {
        $deleteCategoryExpenses = $this->schoolExpensesCategoryService->deleteSchoolExpenseCategory($categoryId);
        return ApiResponseService::success("Expenses Category Deleted Succesfully", $deleteCategoryExpenses, null, 200);
    }


    public function getCategory(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $getSchoolExpensesCategory = $this->schoolExpensesCategoryService->getSchoolCategoryExpenses($currentSchool);
        return ApiResponseService::success("Category Expenses Fetched Successfully", $getSchoolExpensesCategory, null, 200);
    }
}
