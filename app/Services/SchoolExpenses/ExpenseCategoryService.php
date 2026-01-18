<?php

namespace App\Services\SchoolExpenses;

use App\Models\Schoolexpensescategory;
use App\Exceptions\AppException;
use Exception;
use App\Services\ApiResponseService;
use App\Events\Actions\AdminActionEvent;

class ExpenseCategoryService
{
    public function createSchoolExpense(array $data, $currentSchool, $authAdmin)
    {
        $expensesCategory = new Schoolexpensescategory();
        $expensesCategory->name = $data["name"];
        $expensesCategory->school_branch_id = $currentSchool->id;
        $expensesCategory->save();
        AdminActionEvent::dispatch(
            [
                "permissions" =>  ["schoolAdmin.schoolExpenses.category.create"],
                "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                "schoolBranch" =>  $currentSchool->id,
                "feature" => "schoolExpenseManager",
                "action" => "schoolExpenseCategory.created",
                "authAdmin" => $authAdmin,
                "data" => $expensesCategory,
                "message" => "Expense Category Created",
            ]
        );
        return $expensesCategory;
    }

    public function updateSchoolExpenseCategory(array $data, $schoolExpensesId, $currentSchool, $authAdmin)
    {
        $schoolExpensesExists = Schoolexpensescategory::where("school_branch_id", $currentSchool->id)
            ->find($schoolExpensesId);
        if (!$schoolExpensesExists) {
            return ApiResponseService::error("School Expenses Not found", null, 400);
        }
        $filterData = array_filter($data);
        $schoolExpensesExists->update($filterData);
        AdminActionEvent::dispatch(
            [
                "permissions" =>  ["schoolAdmin.schoolExpenses.category.update"],
                "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                "schoolBranch" =>  $currentSchool->id,
                "feature" => "expenseCategoryManagement",
                "action" => "schoolExpenseCategory.updated",
                "authAdmin" => $authAdmin,
                "data" => $schoolExpensesExists,
                "message" => "Expense Category Updated",
            ]
        );
        return $schoolExpensesExists;
    }
    public function deleteSchoolExpenseCategory($schoolExpenseCategoryId, $currentSchool, $authAdmin)
    {
        $schoolExpensesCategoryExists = Schoolexpensescategory::where("school_branch_id", $currentSchool->id)
            ->find($schoolExpenseCategoryId);
        if (!$schoolExpensesCategoryExists) {
            return ApiResponseService::error("School Expenses Not found", null, 400);
        }
        $schoolExpensesCategoryExists->delete();
        AdminActionEvent::dispatch(
            [
                "permissions" =>  ["schoolAdmin.schoolExpenses.category.delete"],
                "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                "schoolBranch" =>  $currentSchool->id,
                "feature" => "expenseCategoryManagement",
                "action" => "schoolExpenseCategory.deleted",
                "authAdmin" => $authAdmin,
                "data" => $schoolExpensesCategoryExists,
                "message" => "Expense Category Updated",
            ]
        );
        return $schoolExpensesCategoryExists;
    }

    public function getSchoolCategoryExpenses($currentSchool)
    {
        try {
            $schoolExpenses = Schoolexpensescategory::where("school_branch_id", $currentSchool->id)->get();

            if ($schoolExpenses->isEmpty()) {
                throw new AppException(
                    "No school expenses categories were found for this school branch.",
                    404,
                    "No Categories Found",
                    "There are no expense categories configured in the system for your school branch.",
                    null
                );
            }

            return $schoolExpenses;
        } catch (AppException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new AppException(
                "An unexpected error occurred while retrieving school expense categories.",
                500,
                "Internal Server Error",
                "A server-side issue prevented the expense categories from being retrieved successfully.",
                null
            );
        }
    }
}
