<?php

namespace App\Http\Controllers;

use App\Models\SchoolExpenses;
use App\Http\Requests\SchoolExpensesRequest;
use App\Services\ApiResponseService;
use App\Services\SchoolExpensesService;
use Illuminate\Http\Request;

class SchoolexpensesController extends Controller
{
    //
    protected SchoolExpensesService $schoolExpensesService;
    public function __construct(SchoolExpensesService $schoolExpensesService)
    {
        $this->schoolExpensesService = $schoolExpensesService;
    }
    public function add_new_expense(SchoolExpensesRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $createExpenses = $this->schoolExpensesService->createExpenses($request->validated(), $currentSchool);
        return ApiResponseService::success("Expenses Created Succesfully", $createExpenses, null, 201);
    }

    public function delete_expense(Request $request, $expense_id)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $deleteSchoolExpenses = $this->schoolExpensesService->deleteExpenses($currentSchool, $expense_id);
        return ApiResponseService::success('Expenses Deleted Succefully', $deleteSchoolExpenses, null, 200);
    }

    public function update_expense(Request $request, $expense_id)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $updateExpenses = $this->schoolExpensesService->updateExpenses($request->validated(), $currentSchool, $expense_id);
        return ApiResponseService::success("Expenses Updated Succefully", $updateExpenses, null, 200);
    }

    public function get_all_expenses(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $expensesData = $this->schoolExpensesService->getExpenses($currentSchool);
        $expenses_data = SchoolExpenses::where('school_branch_id', $currentSchool->id)->with(['schoolexpensescategory'])
            ->get();
        return ApiResponseService::success('Expenses data fetched Succefully', $expensesData, null, 200);
    }

    public function expenses_details(Request $request)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $expense_id = $request->route("expense_id");
        $expensesDetails = $this->schoolExpensesService->getExpensesDetails($expense_id, $currentSchool);
        return ApiResponseService::success("Expenses details fetched sucessfully", $expensesDetails, null, 200);
    }
}
