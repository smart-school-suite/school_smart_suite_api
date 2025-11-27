<?php

namespace App\Http\Controllers\SchoolAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SchoolAdmin\SchoolAdminIdRequest;
use App\Services\ApiResponseService;
use App\Models\SchoolBranchApiKey;
use App\Http\Requests\Auth\UpdateProfilePictureRequest;
use App\Http\Requests\SchoolAdmin\BulkUpdateSchoolAdminRequest;
use App\Http\Requests\SchoolAdmin\CreateSchoolSuperAdminRequest;
use App\Http\Requests\SchoolAdmin\UpdateSchoolAdminRequest;
use App\Services\SchoolAdmin\SchoolAdminService;
use Exception;
use Illuminate\Http\Request;

class SchoolAdminController extends Controller
{
        protected SchoolAdminService $schoolAdminService;
    public function __construct(SchoolAdminService $schoolAdminService)
    {
        $this->schoolAdminService = $schoolAdminService;
    }
    public function updateSchoolAdmin(UpdateSchoolAdminRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $schoolAdminId = $request->route("schoolAdminId");
        $updateSchoolAdmin = $this->schoolAdminService->updateSchoolAdmin($request->validated(), $schoolAdminId, $currentSchool);
        return ApiResponseService::success("Admin Updated Sucessfully", $updateSchoolAdmin, null, 200);
    }

    public function deleteSchoolAdmin(Request $request, $schoolAdminId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $deleteSchoolAdmin = $this->schoolAdminService->deleteSchoolAdmin($schoolAdminId, $currentSchool);
        return ApiResponseService::success('School Admin Deleled Sucessfully', $deleteSchoolAdmin, null, 200);
    }

    public function getSchoolAdmin(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $schoolAdmins = $this->schoolAdminService->getSchoolAdmins($currentSchool);
        return ApiResponseService::success("School Admin Fetched Successfully", $schoolAdmins, null, 200);
    }

    public function getSchoolAdminDetails(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $schoolAdminId = $request->route('schoolAdminId');
        $schoolAdminDetails = $this->schoolAdminService->getSchoolAdminDetails($currentSchool, $schoolAdminId);
        return ApiResponseService::success("School Admin Details Fetched Successfully", $schoolAdminDetails, null, 200);
    }

    public function createAdminOnSignup(CreateSchoolSuperAdminRequest $request)
    {
      try{
          $schoolBranchApiKey = $request->header('API-KEY');
        if (!$schoolBranchApiKey) {
            ApiResponseService::error("School Branch Api Key is required please provide a valid api key", null, 400);
        }
        $schoolBranch = SchoolBranchApiKey::where("api_key", $schoolBranchApiKey)->with(['schoolBranch'])->first();
        $createSchoolAdmin = $this->schoolAdminService->createSchoolAdmin($request->validated(), $schoolBranch->schoolBranch->id);
        return ApiResponseService::success("School Admin Created Sucessfully", $createSchoolAdmin, null, 201);
      }
      catch(Exception $e){
        return ApiResponseService::error($e->getMessage(), null, 400);
      }
    }

    public function uploadProfilePicture(UpdateProfilePictureRequest $request)
    {
        $authSchoolAdmin = auth()->guard('schooladmin')->user();
        try {
            $updateProfilePicture = $this->schoolAdminService->uploadProfilePicture($request, $authSchoolAdmin);
            if ($updateProfilePicture) {
                return ApiResponseService::success("School Admin Profile Picture Updated Succesfully", $updateProfilePicture, null, 201);
            }
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }

    public function deleteProfilePicture()
    {
        $authSchoolAdmin = auth()->guard('schooladmin')->user();
        try {
            $deleteProfilePicture = $this->schoolAdminService->deleteProfilePicture($authSchoolAdmin);
            return ApiResponseService::success("School Admin Profile Picture Deleted Succesfully", $deleteProfilePicture, null, 200);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }

    public function deactivateAccount(string $schoolAdminId)
    {
       $deactivateSchoolAdmin = $this->schoolAdminService->deactivateAccount($schoolAdminId);
        return ApiResponseService::success("Account successfully Deactivated", $deactivateSchoolAdmin, null, 200);
    }

    public function activateAccount(string $schoolAdminId)
    {
        $activateSchoolAdmin =  $this->schoolAdminService->activateAccount($schoolAdminId);
        return ApiResponseService::success("Account successfully Activated", $activateSchoolAdmin, null, 200);
    }

    public function bulkUpdateSchoolAdmin(BulkUpdateSchoolAdminRequest $request)
    {
        try {
            $bulkUpdateSchoolAdmin = $this->schoolAdminService->bulkUpdateSchoolAdmin($request->school_admins);
            return ApiResponseService::success("School Admin Updated Succesfully", $bulkUpdateSchoolAdmin, null, 200);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }

    public function bulkDeleteSchoolAdmin(SchoolAdminIdRequest $request)
    {
        try {
            $bulkDeleteSchoolAdmin = $this->schoolAdminService->bulkDeleteSchoolAdmin($request->schoolAdminIds);
            return ApiResponseService::success("School Admin Deleted Succesfully", $bulkDeleteSchoolAdmin, null, 200);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }

    public function bulkDeactivateSchoolAdmin(SchoolAdminIdRequest $request)
    {
        try {
            $bulkDeactivateSchoolAdmin = $this->schoolAdminService->bulkDeactivateSchoolAdmin($request->schoolAdminIds);
            return ApiResponseService::success("School Admin Deactivated Succesfully", $bulkDeactivateSchoolAdmin, null, 200);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }

    public function bulkActivateSchoolAdmin(SchoolAdminIdRequest $request)
    {
        try {
            $bulkActivateSchoolAdmin = $this->schoolAdminService->bulkActivateSchoolAdmin($request->schoolAdminIds);
            return ApiResponseService::success("School Admin Activated Succesfully", $bulkActivateSchoolAdmin, null, 200);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }
}
