<?php

namespace App\Http\Controllers\SchoolAdmin;

use App\Services\Import\ImportJobDispatcher;

use App\Http\Controllers\Controller;
use App\Http\Requests\SchoolAdmin\SchoolAdminIdRequest;
use App\Services\ApiResponseService;
use App\Http\Requests\SchoolAdmin\BulkUpdateSchoolAdminRequest;
use App\Http\Requests\SchoolAdmin\ImportSchoolAdminRequest;
use App\Http\Requests\SchoolAdmin\UpdateSchoolAdminRequest;
use App\Jobs\SchoolAdmin\SchoolAdminImportJob;
use App\Services\SchoolAdmin\SchoolAdminService;
use Illuminate\Http\Request;
use App\Models\Schooladmin;

class SchoolAdminController extends Controller
{
    protected SchoolAdminService $schoolAdminService;
    public function __construct(SchoolAdminService $schoolAdminService)
    {
        $this->schoolAdminService = $schoolAdminService;
    }
    public function updateSchoolAdmin(UpdateSchoolAdminRequest $request)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get('currentSchool');
        $schoolAdminId = $request->route("schoolAdminId");
        $updateSchoolAdmin = $this->schoolAdminService->updateSchoolAdmin($request->validated(), $schoolAdminId, $currentSchool, $authAdmin);
        return ApiResponseService::success("Admin Updated Sucessfully", $updateSchoolAdmin, null, 200);
    }
    public function deleteSchoolAdmin(Request $request, string $schoolAdminId)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get('currentSchool');
        $deleteSchoolAdmin = $this->schoolAdminService->deleteSchoolAdmin($schoolAdminId, $currentSchool, $authAdmin);
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
    public function deactivateAccount(Request $request, string $schoolAdminId)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get('currentSchool');
        $deactivateSchoolAdmin = $this->schoolAdminService->deactivateAccount($schoolAdminId, $currentSchool, $authAdmin);
        return ApiResponseService::success("Account successfully Deactivated", $deactivateSchoolAdmin, null, 200);
    }
    public function activateAccount(Request $request, string $schoolAdminId)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get('currentSchool');
        $activateSchoolAdmin =  $this->schoolAdminService->activateAccount($schoolAdminId, $currentSchool, $authAdmin);
        return ApiResponseService::success("Account successfully Activated", $activateSchoolAdmin, null, 200);
    }
    public function bulkUpdateSchoolAdmin(BulkUpdateSchoolAdminRequest $request)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get('currentSchool');
        $bulkUpdateSchoolAdmin = $this->schoolAdminService->bulkUpdateSchoolAdmin($request->school_admins, $currentSchool, $authAdmin);
        return ApiResponseService::success("School Admin Updated Succesfully", $bulkUpdateSchoolAdmin, null, 200);
    }
    public function bulkDeleteSchoolAdmin(SchoolAdminIdRequest $request)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get('currentSchool');
        $bulkDeleteSchoolAdmin = $this->schoolAdminService->bulkDeleteSchoolAdmin($request->schoolAdminIds, $currentSchool, $authAdmin);
        return ApiResponseService::success("School Admin Deleted Succesfully", $bulkDeleteSchoolAdmin, null, 200);
    }
    public function bulkDeactivateSchoolAdmin(SchoolAdminIdRequest $request)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get('currentSchool');
        $bulkDeactivateSchoolAdmin = $this->schoolAdminService->bulkDeactivateSchoolAdmin($request->schoolAdminIds, $currentSchool, $authAdmin);
        return ApiResponseService::success("School Admin Deactivated Succesfully", $bulkDeactivateSchoolAdmin, null, 200);
    }
    public function bulkActivateSchoolAdmin(SchoolAdminIdRequest $request)
    {
        $authAdmin = $this->resolveUser();
        $currentSchool = $request->attributes->get('currentSchool');
        $bulkActivateSchoolAdmin = $this->schoolAdminService->bulkActivateSchoolAdmin($request->schoolAdminIds, $currentSchool, $authAdmin);
        return ApiResponseService::success("School Admin Activated Succesfully", $bulkActivateSchoolAdmin, null, 200);
    }

    public function importSchoolAdmins(ImportSchoolAdminRequest $request)
    {
        $job = app(ImportJobDispatcher::class)->dispatch(
            SchoolAdminImportJob::class,
            $request->file('file'),
            $request->mapping(),
            $request->attributes->get('currentSchool'),
            $this->resolveUser()
        );

        return ApiResponseService::success('School admin import started', $job, null, 202);
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
