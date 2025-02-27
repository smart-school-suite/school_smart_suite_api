<?php

namespace App\Http\Controllers;


use App\Services\DepartmentService;
use App\Http\Requests\DepartmentRequest;
use App\Http\Requests\UpdateDepartmentRequest;
use App\Http\Resources\DepartmentResource;
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
    public function createDepartment(DepartmentRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $department = $this->departmentService->createDepartment($request->validated(), $currentSchool);
        return ApiResponseService::success("Department Created Sucessfully", $department, null, 201);
    }

    public function deleteDepartment(string $department_id)
    {
        $deleteDepartment = $this->departmentService->deleteDepartment($department_id);
        return ApiResponseService::success("Department Deleted successfully", $deleteDepartment, null, 200);
    }

    public function updateDepartment(UpdateDepartmentRequest $request, string $department_id)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $updateDepartment = $this->departmentService->updateDepartment($department_id, $request->validated(), $currentSchool);
        return ApiResponseService::success('Department updated sucessfully', $updateDepartment, null, 200);
    }

    public function getDepartments(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $getDepartments = $this->departmentService->getDepartments($currentSchool);
        return ApiResponseService::success('Departments fetched succefully', DepartmentResource::collection($getDepartments), null, 200);
    }

    public function getDepartmentDetails(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $department_id = $request->route("department_id");
        $departmentDetails = $this->departmentService->getDepartmentDetails($currentSchool, $department_id);
        return ApiResponseService::success("Department Details Fetched Sucessfully", DepartmentResource::collection($departmentDetails), null, 200);
    }
}
