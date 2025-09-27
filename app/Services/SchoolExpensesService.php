<?php

namespace App\Services;

use App\Jobs\StatisticalJobs\FinancialJobs\SchoolExpensesStatJob;
use App\Models\SchoolExpenses;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Str;
use App\Exceptions\AppException;
class SchoolExpensesService
{
    // Implement your logic here

    public function createExpenses(array $data, $currentSchool)
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
        return $schoolExpenses;
    }

    public function deleteExpenses($currentSchool, $expensesId)
    {
        $expenses = SchoolExpenses::where('school_branch_id', $currentSchool->id)->find($expensesId);
        if (!$expenses) {
            return ApiResponseService::error("Expenses Not Found", null, 404);
        }
        $expenses->delete();
        return $expenses;
    }

    public function updateExpenses(array $data, $currentSchool, $expensesId)
    {
        $expenses = SchoolExpenses::where('school_branch_id', $currentSchool->id)->find($expensesId);
        if (!$expenses) {
            return ApiResponseService::error("School Expenses Not Found", null, 404);
        }
        $filterData = array_filter($data);
        $expenses->update($filterData);
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

    public function bulkDeleteSchoolExpenses($expensesIds)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($expensesIds as $expensesId) {
                $schoolExpense = SchoolExpenses::findOrFail($expensesId['expense_id']);
                $schoolExpense->delete();
                $result[] =  $schoolExpense;
            }
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function bulkUpdateExpenses($expensesDataList)
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($expensesDataList as $expensesData) {
                $schoolExpense = SchoolExpenses::findOrFail($expensesData['expense_id']);
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
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
