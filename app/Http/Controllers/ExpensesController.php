<?php

namespace App\Http\Controllers;

use App\Http\Requests\SchoolExpenses\BulkUpdateSchoolExpensesRequest;
use App\Http\Requests\SchoolExpenses\CreateSchoolExpensesRequest;
use App\Http\Requests\SchoolExpenses\ExpensesIdRequest;
use App\Http\Requests\SchoolExpenses\UpdateSchoolExpensesRequest;
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
    public function createExpense(CreateSchoolExpensesRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $createExpenses = $this->schoolExpensesService->createExpenses($request->validated(), $currentSchool);
        return ApiResponseService::success("Expenses Created Succesfully", $createExpenses, null, 201);
    }

    public function deleteExpense(Request $request, $expenseId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $deleteSchoolExpenses = $this->schoolExpensesService->deleteExpenses($currentSchool, $expenseId);
        return ApiResponseService::success('Expenses Deleted Succefully', $deleteSchoolExpenses, null, 200);
    }

    public function updateExpense(UpdateSchoolExpensesRequest $request, $expenseId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $updateExpenses = $this->schoolExpensesService->updateExpenses($request->validated(), $currentSchool, $expenseId);
        return ApiResponseService::success("Expenses Updated Succefully", $updateExpenses, null, 200);
    }

    public function getExpenses(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $expensesData = $this->schoolExpensesService->getExpenses($currentSchool);
        return ApiResponseService::success('Expenses data fetched Succefully', $expensesData, null, 200);
    }

    public function getExpensesDetails(Request $request, $expenseId)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $expensesDetails = $this->schoolExpensesService->getExpensesDetails($expenseId, $currentSchool);
        return ApiResponseService::success("Expenses details fetched sucessfully", $expensesDetails, null, 200);
    }

    public function bulkDeleteSchoolExpenses(ExpensesIdRequest $request){
        try{
            $bulkDelete = $this->schoolExpensesService->bulkDeleteSchoolExpenses($request->expenseIds);
            return ApiResponseService::success("School Expenses Deleted Succesfully", $bulkDelete, null, 200);
        }
        catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }
    public function bulkUpdateSchoolExpenses(BulkUpdateSchoolExpensesRequest $request){
         try{
            $bulkUpdateSchoolExpenses = $this->schoolExpensesService->bulkUpdateExpenses($request->school_expenses);
            return ApiResponseService::success("School Expenses Updated Succesfully", $bulkUpdateSchoolExpenses, null, 200);
         }
         catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
         }
    }
}
