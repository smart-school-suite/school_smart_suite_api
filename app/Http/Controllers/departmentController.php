<?php

namespace App\Http\Controllers;


use App\Services\DepartmentService;
use App\Http\Requests\DepartmentRequest;
use App\Http\Resources\DepartmentResource;
use App\Http\Requests\Department\CreateDepartmentRequest;
use App\Http\Requests\Department\UpdateDepartmentRequest;
use App\Http\Requests\Department\BulkUpdateDepartmentRequest;
use App\Services\ApiResponseService;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    //
    protected DepartmentService $departmentService;
    public function __construct(DepartmentService $departmentService)
    {
        $this->departmentService = $departmentService;
    }
    public function createDepartment(CreateDepartmentRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $department = $this->departmentService->createDepartment($request->validated(), $currentSchool);
        return ApiResponseService::success("Department Created Sucessfully", $department, null, 201);
    }
    public function deleteDepartment(string $departmentId)
    {
        $deleteDepartment = $this->departmentService->deleteDepartment($departmentId);
        return ApiResponseService::success("Department Deleted successfully", $deleteDepartment, null, 200);
    }
    public function updateDepartment(UpdateDepartmentRequest $request, string $departmentId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $updateDepartment = $this->departmentService->updateDepartment($departmentId, $request->validated(), $currentSchool);
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
        $departmentId = $request->route("department_id");
        $departmentDetails = $this->departmentService->getDepartmentDetails($currentSchool, $departmentId);
        return ApiResponseService::success("Department Details Fetched Sucessfully", $departmentDetails, null, 200);
    }
    public function deactivateDepartment($departmentId)
    {
        $deactivateDepartment = $this->departmentService->deactivateDepartment($departmentId);
        return ApiResponseService::success("Department Deactivated Sucessfully", $deactivateDepartment, null, 200);
    }
    public function activateDepartment($departmentId)
    {
        $activateDepartment = $this->departmentService->activateDepartment($departmentId);
        return ApiResponseService::success("Department Activated Sucessfully", $activateDepartment, null, 200);
    }
    public function bulkDeactivateDepartment($departmentIds)
    {
        $idsArray = explode(',', $departmentIds);

        $idsArray = array_map('trim', $idsArray);
        if (empty($idsArray)) {
            return ApiResponseService::error("No IDs provided", null, 422);
        }
        $validator = Validator::make(['ids' => $idsArray], [
            'ids' => 'required|array',
            'ids.*' => 'string|exists:department,id',
        ]);
        if ($validator->fails()) {
            return ApiResponseService::error($validator->errors(), null, 422);
        }
        try {
            $bulkDeactivateDepartment = $this->departmentService->bulkDeactivateDepartment($idsArray);
            return ApiResponseService::success("Department Deactivated Succesfully", $bulkDeactivateDepartment, null, 200);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }
    public function bulkActivateDepartment($departmentIds)
    {
        $idsArray = explode(',', $departmentIds);

        $idsArray = array_map('trim', $idsArray);
        if (empty($idsArray)) {
            return ApiResponseService::error("No IDs provided", null, 422);
        }
        $validator = Validator::make(['ids' => $idsArray], [
            'ids' => 'required|array',
            'ids.*' => 'string|exists:department,id',
        ]);
        if ($validator->fails()) {
            return ApiResponseService::error($validator->errors(), null, 422);
        }

        try {
            $bulkActivateDepartment = $this->departmentService->bulkActivateDepartment($idsArray);
            return ApiResponseService::success("Departments Activated Succesfully", $bulkActivateDepartment, null, 200);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }
    public function bulkDeleteDepartment($departmentIds){
        $idsArray = explode(',', $departmentIds);

        $idsArray = array_map('trim', $idsArray);
        if (empty($idsArray)) {
            return ApiResponseService::error("No IDs provided", null, 422);
        }
        $validator = Validator::make(['ids' => $idsArray], [
            'ids' => 'required|array',
            'ids.*' => 'string|exists:department,id',
        ]);
        if ($validator->fails()) {
            return ApiResponseService::error($validator->errors(), null, 422);
        }
        try{
           $bulkDeleteDepartment = $this->departmentService->bulkDeleteDepartment($idsArray);
           return ApiResponseService::success("Bulk Department Deleted Succesfully", $bulkDeleteDepartment, null, 200);
        }
        catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }
    public function bulkUpdateDepartment(BulkUpdateDepartmentRequest $request){
        try{
           $bulkUpdateDepartment = $this->departmentService->bulkUpdateDepartment($request->department);
           return ApiResponseService::success("Departments Updated Succesfully", $bulkUpdateDepartment, null, 200);
        }
        catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }
}
