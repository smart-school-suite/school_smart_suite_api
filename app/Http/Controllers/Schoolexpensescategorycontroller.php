<?php

namespace App\Http\Controllers;

use App\Services\ApiResponseService;
use App\Services\SchoolExpensesCategoryService;
use App\Http\Requests\SchoolCategoryExpensesRequest;
use App\Http\Requests\UpdateSchoolCategoryExpensesRequest;
use Illuminate\Http\Request;

class Schoolexpensescategorycontroller extends Controller
{
    //update validatioin, create validation
    protected SchoolExpensesCategoryService $schoolExpensesCategoryService;
    public function __construct(SchoolExpensesCategoryService $schoolExpensesCategoryService){
        $this->schoolExpensesCategoryService = $schoolExpensesCategoryService;
    }
    public function create_category_expenses(SchoolCategoryExpensesRequest $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $createCategoryExpenses = $this->schoolExpensesCategoryService->createSchoolExpense($request->validated, $currentSchool);
        return ApiResponseService::success("Category Expenses Created Sucessfully", $createCategoryExpenses, null, 201);
    }

    public function update_category_expenses(UpdateSchoolCategoryExpensesRequest $request, $category_expense_id){
        $updateCategoryExpenses = $this->schoolExpensesCategoryService->updateSchoolExpenseCategory($request->validated,  $category_expense_id);
        return ApiResponseService::success("Expenses Category Updated Sucessfully", $updateCategoryExpenses, null, 200);
    }


    public function delete_category_expense(string $category_expense_id){
        $deleteCategoryExpenses = $this->schoolExpensesCategoryService->deleteSchoolExpenseCategory($category_expense_id);
        return ApiResponseService::success("Expenses Category Deleted Succesfully", $deleteCategoryExpenses, null, 200);
    }


    public function get_all_category_expenses(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $getSchoolExpensesCategory = $this->schoolExpensesCategoryService->getSchoolCategoryExpenses($currentSchool);
        return ApiResponseService::success("Category Expenses Fetched Successfully", $getSchoolExpensesCategory, null, 200);
    }
}
