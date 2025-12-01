<?php

namespace App\Http\Controllers\Department;

use App\Http\Controllers\Controller;
use App\Services\Department\DepartmentService;
use App\Http\Resources\DepartmentResource;
use App\Http\Requests\Department\CreateDepartmentRequest;
use App\Http\Requests\Department\UpdateDepartmentRequest;
use App\Http\Requests\Department\BulkUpdateDepartmentRequest;
use App\Services\ApiResponseService;
use App\Http\Requests\Department\ValidateDepartmentIdRequest;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    protected DepartmentService $departmentService;
    public function __construct(DepartmentService $departmentService)
    {
        $this->departmentService = $departmentService;
    }
    public function createDepartment(CreateDepartmentRequest $request)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get('currentSchool');
        $department = $this->departmentService->createDepartment($request->validated(), $currentSchool, $authAdmin);
        return ApiResponseService::success("Department Created Sucessfully", $department, null, 201);
    }
    public function deleteDepartment(Request $request, string $departmentId)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get('currentSchool');
        $deleteDepartment = $this->departmentService->deleteDepartment($departmentId, $currentSchool, $authAdmin);
        return ApiResponseService::success("Department Deleted successfully", $deleteDepartment, null, 200);
    }
    public function updateDepartment(UpdateDepartmentRequest $request, string $departmentId)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get('currentSchool');
        $updateDepartment = $this->departmentService->updateDepartment($departmentId, $request->validated(), $currentSchool, $authAdmin);
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
        $departmentId = $request->route("departmentId");
        $departmentDetails = $this->departmentService->getDepartmentDetails($currentSchool, $departmentId);
        return ApiResponseService::success("Department Details Fetched Sucessfully", $departmentDetails, null, 200);
    }
    public function deactivateDepartment(Request $request, $departmentId)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get('currentSchool');
        $deactivateDepartment = $this->departmentService->deactivateDepartment($departmentId, $currentSchool, $authAdmin);
        return ApiResponseService::success("Department Deactivated Sucessfully", $deactivateDepartment, null, 200);
    }
    public function activateDepartment(Request $request, $departmentId)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get('currentSchool');
        $activateDepartment = $this->departmentService->activateDepartment($departmentId, $currentSchool, $authAdmin);
        return ApiResponseService::success("Department Activated Sucessfully", $activateDepartment, null, 200);
    }
    public function bulkDeactivateDepartment(ValidateDepartmentIdRequest $request)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get('currentSchool');
        $bulkDeactivateDepartment = $this->departmentService->bulkDeactivateDepartment($request->departmentIds, $currentSchool, $authAdmin);
        return ApiResponseService::success("Department Deactivated Succesfully", $bulkDeactivateDepartment, null, 200);
    }
    public function bulkActivateDepartment(ValidateDepartmentIdRequest $request)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get('currentSchool');
        $bulkActivateDepartment = $this->departmentService->bulkActivateDepartment($request->departmentIds, $currentSchool, $authAdmin);
        return ApiResponseService::success("Departments Activated Succesfully", $bulkActivateDepartment, null, 200);
    }
    public function bulkDeleteDepartment(ValidateDepartmentIdRequest $request)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get('currentSchool');
        $bulkDeleteDepartment = $this->departmentService->bulkDeleteDepartment($request->departmentIds, $currentSchool, $authAdmin);
        return ApiResponseService::success("Bulk Department Deleted Succesfully", $bulkDeleteDepartment, null, 200);
    }
    public function bulkUpdateDepartment(BulkUpdateDepartmentRequest $request)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get('currentSchool');
        $bulkUpdateDepartment = $this->departmentService->bulkUpdateDepartment($request->departments, $currentSchool, $authAdmin);
        return ApiResponseService::success("Departments Updated Succesfully", $bulkUpdateDepartment, null, 200);
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
