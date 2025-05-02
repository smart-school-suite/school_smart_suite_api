<?php

namespace App\Http\Controllers;


use App\Services\ApiResponseService;
use App\Services\SchoolAdminService;
use App\Models\SchoolBranchApiKey;
use App\Http\Requests\Auth\UpdateProfilePictureRequest;
use App\Http\Requests\SchoolAdmin\BulkUpdateSchoolAdminRequest;
use App\Http\Requests\SchoolAdmin\CreateSchoolAdminRequest;
use App\Http\Requests\SchoolAdmin\UpdateSchoolAdminRequest;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Http\Request;


class SchoolAdminController extends Controller
{
    //
    protected SchoolAdminService $schoolAdminService;
    public function __construct(SchoolAdminService $schoolAdminService)
    {
        $this->schoolAdminService = $schoolAdminService;
    }
    public function updateSchoolAdmin(UpdateSchoolAdminRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $school_admin_id = $request->route("school_admin_id");
        $updateSchoolAdmin = $this->schoolAdminService->updateSchoolAdmin($request->validated(), $school_admin_id, $currentSchool);
        return ApiResponseService::success("Admin Updated Sucessfully", $updateSchoolAdmin, null, 200);
    }

    public function deleteSchoolAdmin(Request $request, $school_admin_id)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $deleteSchoolAdmin = $this->schoolAdminService->deleteSchoolAdmin($school_admin_id, $currentSchool);
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
        $schoolAdminId = $request->route('school_admin_id');
        $schoolAdminDetails = $this->schoolAdminService->getSchoolAdminDetails($currentSchool, $schoolAdminId);
        return ApiResponseService::success("School Admin Details Fetched Successfully", $schoolAdminDetails, null, 200);
    }

    public function createAdminOnSignup(CreateSchoolAdminRequest $request)
    {
        $schoolBranchApiKey = $request->header('API-KEY');
        if (!$schoolBranchApiKey) {
            ApiResponseService::error("School Branch Api Key is required please provide a valid api key", null, 400);
        }
        $schoolBranch = SchoolBranchApiKey::where("api_key", $schoolBranchApiKey)->with(['schoolBranch'])->first();
        $createSchoolAdmin = $this->schoolAdminService->createSchoolAdmin($request->validated(), $schoolBranch->schoolBranch->id);
        return ApiResponseService::success("School Admin Created Sucessfully", $createSchoolAdmin, null, 201);
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
        $this->schoolAdminService->deactivateAccount($schoolAdminId);
    }

    public function activateAccount(string $schoolAdminId)
    {
        $this->schoolAdminService->activateAccount($schoolAdminId);
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

    public function bulkDeleteSchoolAdmin($schoolAdminIds)
    {
        $idsArray = explode(',', $schoolAdminIds);

        $idsArray = array_map('trim', $idsArray);
        if (empty($idsArray)) {
            return ApiResponseService::error("No IDs provided", null, 422);
        }
        $validator = Validator::make(['ids' => $idsArray], [
            'ids' => 'required|array',
            'ids.*' => 'string|exists:school_admin,id',
        ]);
        if ($validator->fails()) {
            return ApiResponseService::error($validator->errors(), null, 422);
        }
        try {
            $bulkDeleteSchoolAdmin = $this->schoolAdminService->bulkDeleteSchoolAdmin($idsArray);
            return ApiResponseService::success("School Admin Deleted Succesfully", $bulkDeleteSchoolAdmin, null, 200);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }

    public function bulkDeactivateSchoolAdmin($schoolAdminIds)
    {
        $idsArray = explode(',', $schoolAdminIds);

        $idsArray = array_map('trim', $idsArray);
        if (empty($idsArray)) {
            return ApiResponseService::error("No IDs provided", null, 422);
        }
        $validator = Validator::make(['ids' => $idsArray], [
            'ids' => 'required|array',
            'ids.*' => 'string|exists:school_admin,id',
        ]);
        if ($validator->fails()) {
            return ApiResponseService::error($validator->errors(), null, 422);
        }
        try {
            $bulkDeactivateSchoolAdmin = $this->schoolAdminService->bulkDeactivateSchoolAdmin($idsArray);
            return ApiResponseService::success("School Admin Deactivated Succesfully", $bulkDeactivateSchoolAdmin, null, 200);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }

    public function bulkActivateSchoolAdmin($schoolAdminIds)
    {
        $idsArray = explode(',', $schoolAdminIds);

        $idsArray = array_map('trim', $idsArray);
        if (empty($idsArray)) {
           return ApiResponseService::error("No IDs provided", null, 422);
       }
       $validator = Validator::make(['ids' => $idsArray], [
           'ids' => 'required|array',
           'ids.*' => 'string|exists:school_admin,id',
       ]);
       if ($validator->fails()) {
           return ApiResponseService::error($validator->errors(), null, 422);
       }
        try {
            $bulkActivateSchoolAdmin = $this->schoolAdminService->bulkActivateSchoolAdmin($idsArray);
            return ApiResponseService::success("School Admin Activated Succesfully", $bulkActivateSchoolAdmin, null, 200);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }
}
