<?php

namespace App\Http\Controllers;

use App\Http\Requests\SchoolExpensesRequest;
use App\Http\Requests\UpdateSchoolExpensesRequest;
use App\Http\Requests\BulkUpdateSchoolExpenseRequest;
use Illuminate\Support\Facades\Validator;
use App\Services\ApiResponseService;
use App\Services\SchoolExpensesService;
use Exception;
use Illuminate\Http\Request;

class ExpensesController extends Controller
{
    //
    protected SchoolExpensesService $schoolExpensesService;
    public function __construct(SchoolExpensesService $schoolExpensesService)
    {
        $this->schoolExpensesService = $schoolExpensesService;
    }
    public function createExpense(SchoolExpensesRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $createExpenses = $this->schoolExpensesService->createExpenses($request->validated(), $currentSchool);
        return ApiResponseService::success("Expenses Created Succesfully", $createExpenses, null, 201);
    }

    public function deleteExpense(Request $request, $expense_id)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $deleteSchoolExpenses = $this->schoolExpensesService->deleteExpenses($currentSchool, $expense_id);
        return ApiResponseService::success('Expenses Deleted Succefully', $deleteSchoolExpenses, null, 200);
    }

    public function updateExpense(UpdateSchoolExpensesRequest $request, $expense_id)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $updateExpenses = $this->schoolExpensesService->updateExpenses($request->validated(), $currentSchool, $expense_id);
        return ApiResponseService::success("Expenses Updated Succefully", $updateExpenses, null, 200);
    }

    public function getExpenses(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $expensesData = $this->schoolExpensesService->getExpenses($currentSchool);
        return ApiResponseService::success('Expenses data fetched Succefully', $expensesData, null, 200);
    }

    public function getExpensesDetails(Request $request)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $expense_id = $request->route("expense_id");
        $expensesDetails = $this->schoolExpensesService->getExpensesDetails($expense_id, $currentSchool);
        return ApiResponseService::success("Expenses details fetched sucessfully", $expensesDetails, null, 200);
    }

    public function bulkDeleteSchoolExpenses($expensesIds){
        $idsArray = explode(',', $expensesIds);

        $idsArray = array_map('trim', $idsArray);
        if (empty($idsArray)) {
            return ApiResponseService::error("No IDs provided", null, 422);
        }
        $validator = Validator::make(['ids' => $idsArray], [
            'ids' => 'required|array',
            'ids.*' => 'string|exists:specialty,id',
        ]);
        if ($validator->fails()) {
            return ApiResponseService::error($validator->errors(), null, 422);
        }
        try{
            $bulkDelete = $this->schoolExpensesService->bulkDeleteSchoolExpenses($idsArray);
            return ApiResponseService::success("School Expenses Deleted Succesfully", $bulkDelete, null, 200);
        }
        catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }

    public function bulkUpdateSchoolExpenses(BulkUpdateSchoolExpenseRequest $request){
         try{
            $bulkUpdateSchoolExpenses = $this->schoolExpensesService->bulkUpdateExpenses($request->school_expenses);
            return ApiResponseService::success("School Expenses Updated Succesfully", $bulkUpdateSchoolExpenses, null, 200);
         }
         catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
         }
    }
}
