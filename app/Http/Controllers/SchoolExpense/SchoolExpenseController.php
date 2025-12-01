<?php

namespace App\Http\Controllers\SchoolExpense;

use App\Http\Controllers\Controller;
use App\Http\Requests\SchoolExpenses\BulkUpdateSchoolExpensesRequest;
use App\Http\Requests\SchoolExpenses\CreateSchoolExpensesRequest;
use App\Http\Requests\SchoolExpenses\ExpensesIdRequest;
use App\Http\Requests\SchoolExpenses\UpdateSchoolExpensesRequest;
use App\Http\Resources\SchoolExpensesResource;
use App\Services\ApiResponseService;
use App\Services\SchoolExpenses\ExpenseService;
use Exception;
use Illuminate\Http\Request;

class SchoolExpenseController extends Controller
{
    protected ExpenseService $schoolExpensesService;
    public function __construct(ExpenseService $schoolExpensesService)
    {
        $this->schoolExpensesService = $schoolExpensesService;
    }
    public function createExpense(CreateSchoolExpensesRequest $request)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get("currentSchool");
        $createExpenses = $this->schoolExpensesService->createExpenses($request->validated(), $currentSchool, $authAdmin);
        return ApiResponseService::success("Expenses Created Succesfully", $createExpenses, null, 201);
    }
    public function deleteExpense(Request $request, $expenseId)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get("currentSchool");
        $deleteSchoolExpenses = $this->schoolExpensesService->deleteExpenses($currentSchool, $expenseId, $authAdmin);
        return ApiResponseService::success('Expenses Deleted Succefully', $deleteSchoolExpenses, null, 200);
    }
    public function updateExpense(UpdateSchoolExpensesRequest $request, $expenseId)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get("currentSchool");
        $updateExpenses = $this->schoolExpensesService->updateExpenses($request->validated(), $currentSchool, $expenseId, $authAdmin);
        return ApiResponseService::success("Expenses Updated Succefully", $updateExpenses, null, 200);
    }
    public function getExpenses(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $expensesData = $this->schoolExpensesService->getExpenses($currentSchool);
        return ApiResponseService::success('Expenses data fetched Succefully', SchoolExpensesResource::collection($expensesData), null, 200);
    }
    public function getExpensesDetails(Request $request, $expenseId)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $expensesDetails = $this->schoolExpensesService->getExpensesDetails($expenseId, $currentSchool);
        return ApiResponseService::success("Expenses details fetched sucessfully", $expensesDetails, null, 200);
    }
    public function bulkDeleteSchoolExpenses(ExpensesIdRequest $request)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get("currentSchool");
        $bulkDelete = $this->schoolExpensesService->bulkDeleteSchoolExpenses($request->expenseIds, $currentSchool, $authAdmin);
        return ApiResponseService::success("School Expenses Deleted Succesfully", $bulkDelete, null, 200);
    }
    public function bulkUpdateSchoolExpenses(BulkUpdateSchoolExpensesRequest $request)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get("currentSchool");
        $bulkUpdateSchoolExpenses = $this->schoolExpensesService->bulkUpdateExpenses($request->school_expenses, $currentSchool, $authAdmin);
        return ApiResponseService::success("School Expenses Updated Succesfully", $bulkUpdateSchoolExpenses, null, 200);
    }
    protected function resolveUser()
    {
        foreach (['student', 'teacher', 'schooladmin'] as $guard) {
            $user = request()->user($guard);
            if ($user !== null) {
                return $user;
            }
        }
        return null;
    }
}
