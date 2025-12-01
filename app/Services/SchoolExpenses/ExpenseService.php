<?php

namespace App\Services\SchoolExpenses;

use App\Jobs\StatisticalJobs\FinancialJobs\SchoolExpensesStatJob;
use App\Models\SchoolExpenses;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Str;
use App\Exceptions\AppException;
use App\Services\ApiResponseService;
use App\Events\Actions\AdminActionEvent;

class ExpenseService
{
    public function createExpenses(array $data, $currentSchool, $authAdmin)
    {
        $schoolExpenses = new SchoolExpenses();
        $schoolExpensesId = Str::uuid();
        $schoolExpenses->id = $schoolExpensesId;
        $schoolExpenses->expenses_category_id = $data["expenses_category_id"];
        $schoolExpenses->date = $data["date"];
        $schoolExpenses->amount = $data["amount"];
        $schoolExpenses->description = $data["description"] ?? null;
        $schoolExpenses->school_branch_id = $currentSchool->id;
        $schoolExpenses->save();
        SchoolExpensesStatJob::dispatch($schoolExpensesId, $currentSchool->id);
        AdminActionEvent::dispatch(
            [
                "permissions" =>  ["schoolAdmin.schoolExpenses.create"],
                "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                "schoolBranch" =>  $currentSchool->id,
                "feature" => "schoolExpenseManagement",
                "authAdmin" => $authAdmin,
                "data" => $schoolExpenses,
                "message" => "School Expense Created",
            ]
        );
        return $schoolExpenses;
    }

    public function deleteExpenses($currentSchool, $expensesId, $authAdmin)
    {
        $expenses = SchoolExpenses::where('school_branch_id', $currentSchool->id)->find($expensesId);
        if (!$expenses) {
            return ApiResponseService::error("Expenses Not Found", null, 404);
        }
        $expenses->delete();
        AdminActionEvent::dispatch(
            [
                "permissions" =>  ["schoolAdmin.schoolExpenses.delete"],
                "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                "schoolBranch" =>  $currentSchool->id,
                "feature" => "schoolExpenseManagement",
                "authAdmin" => $authAdmin,
                "data" => $expenses,
                "message" => "School Expense Deleted",
            ]
        );
        return $expenses;
    }

    public function updateExpenses(array $data, $currentSchool, $expensesId, $authAdmin)
    {
        $expenses = SchoolExpenses::where('school_branch_id', $currentSchool->id)->find($expensesId);
        if (!$expenses) {
            return ApiResponseService::error("School Expenses Not Found", null, 404);
        }
        $filterData = array_filter($data);
        $expenses->update($filterData);
        AdminActionEvent::dispatch(
            [
                "permissions" =>  ["schoolAdmin.schoolExpenses.update"],
                "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                "schoolBranch" =>  $currentSchool->id,
                "feature" => "schoolExpenseManagement",
                "authAdmin" => $authAdmin,
                "data" => $expenses,
                "message" => "School Expense Updated",
            ]
        );
        return $expenses;
    }

    public function getExpenses($currentSchool)
    {
        try {
            $expensesData = SchoolExpenses::where('school_branch_id', $currentSchool->id)
                ->with(['schoolexpensescategory'])
                ->get();

            if ($expensesData->isEmpty()) {
                throw new AppException(
                    "No expenses were found for this school branch.",
                    404,
                    "No Expenses Found",
                    "There are no expense records available in the system for your school branch.",
                    null
                );
            }

            return $expensesData;
        } catch (AppException $e) {
            throw $e;
        } catch (Exception $e) {
            throw new AppException(
                "An unexpected error occurred while retrieving expenses.",
                500,
                "Internal Server Error",
                "A server-side issue prevented the list of expenses from being retrieved successfully.",
                null
            );
        }
    }

    public function getExpensesDetails($expensesId, $currentSchool)
    {
        $expenses = SchoolExpenses::where('school_branch_id', $currentSchool->id)
            ->with(['schoolexpensescategory'])
            ->find($expensesId);
        if (!$expenses) {
            return ApiResponseService::error("School Expenses Not found", null, 404);
        }
        return $expenses;
    }

    public function bulkDeleteSchoolExpenses($expensesIds, $currentSchool, $authAdmin)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($expensesIds as $expensesId) {
                $schoolExpense = SchoolExpenses::where('school_branch_id', $currentSchool->id)
                    ->findOrFail($expensesId['expense_id']);
                $schoolExpense->delete();
                $result[] =  $schoolExpense;
            }
            DB::commit();
            AdminActionEvent::dispatch(
                [
                    "permissions" =>  ["schoolAdmin.schoolExpenses.delete"],
                    "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                    "schoolBranch" =>  $currentSchool->id,
                    "feature" => "schoolExpenseManagement",
                    "authAdmin" => $authAdmin,
                    "data" => $result,
                    "message" => "School Expense Deleted",
                ]
            );
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function bulkUpdateExpenses($expensesDataList, $currentSchool, $authAdmin)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($expensesDataList as $expensesData) {
                $schoolExpense = SchoolExpenses::where('school_branch_id', $currentSchool->id)
                    ->findOrFail($expensesData['expense_id']);
                if ($schoolExpense) {
                    $cleanedData = array_filter($expensesData, function ($value) {
                        return $value !== null && $value !== '';
                    });

                    if (!empty($cleanedData)) {
                        $schoolExpense->update($cleanedData);
                    }
                }
                $result[] = [
                    $schoolExpense
                ];
            }
            DB::commit();
            AdminActionEvent::dispatch(
                [
                    "permissions" =>  ["schoolAdmin.schoolExpenses.update"],
                    "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                    "schoolBranch" =>  $currentSchool->id,
                    "feature" => "schoolExpenseManagement",
                    "authAdmin" => $authAdmin,
                    "data" => $result,
                    "message" => "School Expense Updated",
                ]
            );
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
