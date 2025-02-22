<?php

namespace App\Http\Controllers;


use App\Services\DepartmentService;
use App\Http\Requests\DepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    //
    protected DepartmentService $departmentService;
    public function __construct(DepartmentService $departmentService)
    {
        $this->departmentService = $departmentService;
    }
    public function create_school_department(DepartmentRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $department = $this->departmentService->createDepartment($request->validated(), $currentSchool);
        return ApiResponseService::success("Department Created Sucessfully", $department, null, 201);
    }

    public function delete_school_department(string $department_id)
    {
        $deleteDepartment = $this->departmentService->deleteDepartment($department_id);
        return ApiResponseService::success("Department Deleted successfully", $deleteDepartment, null, 200);
    }

    public function update_school_department(UpdateDepartmentRequest $request, string $department_id)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $updateDepartment = $this->departmentService->updateDepartment($department_id, $request->validated(), $currentSchool);
        return ApiResponseService::success('Department updated sucessfully', $updateDepartment, null, 200);
    }

    public function get_all_school_department_with_school_branches(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $getDepartments = $this->departmentService->getDepartments($currentSchool);
        return ApiResponseService::success('Departments fetched succefully', $getDepartments, null, 200);
    }

    public function department_details(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $department_id = $request->route("department_id");
        $departmentDetails = $this->departmentService->getDepartmentDetails($currentSchool, $department_id);
        return ApiResponseService::success("Department Details Fetched Sucessfully", $departmentDetails, null, 200);
    }
}
