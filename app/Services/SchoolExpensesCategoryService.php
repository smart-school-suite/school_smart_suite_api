<?php

namespace App\Services;

use App\Models\Schoolexpensescategory;

class SchoolExpensesCategoryService
{
    // Implement your logic here

    public function createSchoolExpense(array $data, $currentSchool)
    {
        $new_category_instance = new Schoolexpensescategory();
        $new_category_instance->name = $data["name"];
        $new_category_instance->school_branch_id = $currentSchool->id;
        return $new_category_instance;
    }

    public function updateSchoolExpenseCategory(array $data, $schoolExpensesId)
    {
        $schoolExpensesExists = Schoolexpensescategory::find($schoolExpensesId);
        if (!$schoolExpensesExists) {
            return ApiResponseService::error("School Expenses Not found", null, 400);
        }
        $filterData = array_filter($data);
        $schoolExpensesExists->update($filterData);
        return $schoolExpensesExists;
    }
    public function deleteSchoolExpenseCategory($schoolExpenseCategoryId)
    {
        $schoolExpensesCategoryExists = Schoolexpensescategory::find($schoolExpenseCategoryId);
        if (!$schoolExpensesCategoryExists) {
            return ApiResponseService::error("School Expenses Not found", null, 400);
        }
        $schoolExpensesCategoryExists->delete();
        return $schoolExpensesCategoryExists;
    }
    public function getSchoolCategoryExpenses($currentSchool)
    {
        $schoolExpenses = Schoolexpensescategory::where("school_branch_id", $currentSchool->id)->get();
        return $schoolExpenses;
    }
}
