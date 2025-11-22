<?php

namespace App\Services;

use App\Jobs\NotificationJobs\SendAdminDepartmentCreatedNotificationJob;
use App\Jobs\StatisticalJobs\OperationalJobs\DepartmentStatJob;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Models\Department;
use Illuminate\Support\Str;
use App\Exceptions\AppException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DepartmentService
{
    public function createDepartment(array $data, $currentSchool)
    {
        try {
            $department = new Department();
            $departmentExists = Department::where("department_name", $data["department_name"])
                ->where("school_branch_id", $currentSchool->id)
                ->first();
            if ($departmentExists) {
                throw new AppException(
                    "A department with this name already exists in the school branch.",
                    409,
                    "Duplicate Department",
                    "Please choose a different name for the department.",
                    "/departments"
                );
            }
            $departmentId = Str::uuid();
            $department->id = $departmentId;
            $department->department_name = $data["department_name"];
            $department->description = $data["description"];
            $department->school_branch_id = $currentSchool->id;
            $department->save();
            DepartmentStatJob::dispatch($departmentId, $currentSchool->id);
            SendAdminDepartmentCreatedNotificationJob::dispatch($currentSchool->id, $data);
            return $department;
        } catch (Exception $e) {
            throw new AppException(
                "An error occurred while creating the department. Please try again.",
                500,
                "Creation Error",
                "We encountered an issue while trying to create the department.",
                null
            );
        } catch (AppException $e) {
            throw $e;
        }
    }

    public function updateDepartment(string $departmentId, array $data, $currentSchool)
    {
        try {
            $department = Department::where("school_branch_id", $currentSchool->id)->find($departmentId);
            if (!$department) {
                return ApiResponseService::error("Department Not found", null, 404);
            }
            $filterData = array_filter($data);
            if ($filterData === []) {
                throw new AppException(
                    "No data provided for update. Please provide at least one field to update.",
                    400,
                    "No Data",
                    "You need to provide at least one field with a valid value to update the department.",
                    null
                );
            }
            if ($filterData['department_name'] ?? false) {
                $existingDepartment = Department::where("department_name", $filterData["department_name"])
                    ->where("school_branch_id", $currentSchool->id)
                    ->where("id", "!=", $departmentId)
                    ->first();
                if ($existingDepartment) {
                    throw new AppException(
                        "A department with this name already exists in the school branch.",
                        409,
                        "Duplicate Department",
                        "Please choose a different name for the department.",
                        "/departments"
                    );
                }
            }
            $department->update($filterData);
            return $department;
        } catch (Exception $e) {
            throw new AppException(
                "An error occurred while updating the department. Please try again.",
                500,
                "Update Error",
                "We encountered an issue while trying to update the department.",
                null
            );
        } catch (AppException $e) {
            throw $e;
        }
    }

    public function deleteDepartment(string $departmentId)
    {
        try {
            $department = Department::findOrFail($departmentId);
            $department->delete();
            return $department;
        } catch (Exception $e) {
            throw new AppException(
                "An error occurred while deleting the department. Please try again.",
                500,
                "Deletion Error",
                "We encountered an issue while trying to delete the department.",
                null
            );
        } catch (AppException $e) {
            throw $e;
        } catch (ModelNotFoundException $e) {
            throw new AppException(
                "The department you are trying to delete was not found.",
                404,
                "Department Not Found",
                "The department may have already been deleted or does not exist.",
                "/departments"
            );
        }
    }

    public function getDepartments($currentSchool)
    {
        $departmentData = Department::where("school_branch_id", $currentSchool->id)
            ->get();
        if ($departmentData->isEmpty()) {
            throw new AppException(
                "No departments found for this school branch.",
                404,
                "No Departments Found",
                "There are no departments available. Please create a department to get started.",
                "/departments"
            );
        }
        return $departmentData;
    }

    public function getDepartmentDetails($currentSchool, $departmentId)
    {
        $findDeparment = Department::where("school_branch_id", $currentSchool->id)
            ->find($departmentId);
        if (!$findDeparment) {
            throw new AppException(
                "The department you are looking for was not found.",
                404,
                "Department Not Found",
                "The department may have been deleted or does not exist.",
                "/departments"
            );
        }
        return $findDeparment;
    }

    public function deactivateDepartment(string $departmentId)
    {
        try {
            $department = Department::findOrFail($departmentId);
            if ($department->status === 'inactive') {
                throw new AppException(
                    "The department is already inactive.",
                    400,
                    "Already Inactive",
                    "The department you are trying to deactivate is already inactive.",
                    null
                );
            }
            $department->status = 'inactive';
            $department->save();
            return $department;
        } catch (ModelNotFoundException $e) {
            throw new AppException(
                "The department you are trying to deactivate was not found.",
                404,
                "Department Not Found",
                "The department may have already been deleted or does not exist.",
                "/departments"
            );
        } catch (Exception $e) {
            throw new AppException(
                "An error occurred while deactivating the department. Please try again.",
                500,
                "Deactivation Error",
                "We encountered an issue while trying to deactivate the department.",
                null
            );
        }
    }

    public function activateDepartment(string $departmentId)
    {
        try {
            $department = Department::findOrFail($departmentId);
            if ($department->status === "active") {
                throw new AppException(
                    "The department is already active.",
                    400,
                    "Already Active",
                    "The department you are trying to activate is already active.",
                    null
                );
            }
            $department->status = "active";
            $department->save();
            return $department;
        } catch (ModelNotFoundException $e) {
            throw new AppException(
                "The department you are trying to activate was not found.",
                404,
                "Department Not Found",
                "The department may have already been deleted or does not exist.",
                "/departments"
            );
        } catch (Exception $e) {
            throw new AppException(
                "An error occurred while activating the department. Please try again.",
                500,
                "Activation Error",
                "We encountered an issue while trying to activate the department.",
                null
            );
        }
    }

    public function bulkDeactivateDepartment(array $departmentIds)
    {
        try{
             foreach ($departmentIds as $departmentId) {
            $department = Department::findOrFail($departmentId['department_id']);
            if( $department->status === 'active'){
                $department->status = 'inactive';
                $department->save();
            }
           }
           return true;
        }
        catch(Exception $e){
            throw new AppException(
                "An error occurred while deactivating one or more departments. Please try again.",
                500,
                "Deactivation Error",
                "We encountered an issue while trying to deactivate the departments.",
                null
            );
        }
        catch(ModelNotFoundException $e){
            throw new AppException(
                "One or more departments you are trying to deactivate were not found.",
                404,
                "Department Not Found",
                "Some departments may have already been deleted or do not exist.",
                "/departments"
            );
        }
    }

    public function bulkActivateDepartment(array $departmentIds)
    {
       try{
        foreach ($departmentIds as $departmentId) {
            $department = Department::findOrFail($departmentId['department_id']);
            if( $department->status === 'inactive'){
                continue;
            }
            $department->status = 'active';
            $department->save();
        }
        return true;
       }
       catch(ModelNotFoundException $e){
            throw new AppException(
                "One or more departments you are trying to activate were not found.",
                404,
                "Department Not Found",
                "Some departments may have already been deleted or do not exist.",
                "/departments"
            );
        }
       catch(Exception $e){
            throw new AppException(
                "An error occurred while activating one or more departments. Please try again.",
                500,
                "Activation Error",
                "We encountered an issue while trying to activate the departments.",
                null
            );
        }
    }


    public function bulkUpdateDepartment(array $updateDataList): array
    {
        $result = [];
        try {
            DB::beginTransaction();
            foreach ($updateDataList as $updateData) {
                $department = Department::findOrFail($updateData['department_id']);
                if ($department) {
                    $cleanedData = array_filter($updateData, function ($value) {
                        return $value !== null && $value !== '';
                    });

                    if (!empty($cleanedData)) {
                        $department->update($cleanedData);
                    }
                    $result[] = $department;
                }
            }
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function bulkDeleteDepartment(array $departmentIds)
    {
        $result = [];
        DB::beginTransaction();
        try {
            foreach ($departmentIds as $departmentId) {
                $department = Department::findOrFail($departmentId['department_id']);
                $department->delete();
                $result[] = [
                    $department
                ];
            }
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw new AppException(
                "An error occurred while activating one or more departments. Please try again.",
                500,
                "Activation Error",
                "We encountered an issue while trying to delete the departments.",
                null
            );
        }
        catch(ModelNotFoundException $e){
            throw new AppException(
                "One or more departments you are trying to delete were not found.",
                404,
                "Department Not Found",
                "Some departments may have already been deleted or do not exist.",
                "/departments"
            );
        }
    }
}
