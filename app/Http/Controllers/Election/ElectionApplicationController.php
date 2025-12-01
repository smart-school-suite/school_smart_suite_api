<?php

namespace App\Http\Controllers\Election;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Election\ElectionApplicationService;
use App\Http\Requests\ElectionApplication\ElectionApplicationIdRequest;
use App\Http\Resources\ElectionApplicationResource;
use App\Http\Requests\ElectionApplication\CreateApplicationRequest;
use App\Http\Requests\ElectionApplication\UpdateElectionApplicationRequest;
use App\Services\ApiResponseService;

class ElectionApplicationController extends Controller
{
    protected ElectionApplicationService $electionApplicationService;
    public function __construct(ElectionApplicationService $electionApplicationService)
    {
        $this->electionApplicationService = $electionApplicationService;
    }

    public function createElectionApplication(CreateApplicationRequest $request)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $electionApplication = $this->electionApplicationService->createApplication($request->validated(), $currentSchool);
        return ApiResponseService::success("Election Application Created Succesfully", $electionApplication, null, 201);
    }

    public function approveApplication(Request $request)
    {
        $applicationId = $request->route('applicationId');
        $currentSchool = $request->attributes->get('currentSchool');
        $authAdmin = $this->resolveUser();
        $approveApplication = $this->electionApplicationService->approveApplication($applicationId, $currentSchool, $authAdmin);
        return ApiResponseService::success('Application Approved Successfully', $approveApplication, null, 200);
    }

    public function deleteApplication(Request $request)
    {
        $applicationId = $request->route('applicationId');
        $currentSchool = $request->attributes->get('currentSchool');
        $authAdmin = $this->resolveUser();
        $application = $this->electionApplicationService->deleteApplication($applicationId, $currentSchool, $authAdmin);
        return ApiResponseService::success('Application Deleted Succefully', $application, null, 200);
    }

    public function getApplications(Request $request, $electionId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $electionApplications = $this->electionApplicationService->getApplicationsByElection($currentSchool, $electionId);
        return ApiResponseService::success('Election Applications Fetched Succefully', ElectionApplicationResource::collection($electionApplications), null, 200);
    }
    public function updateApplication(UpdateElectionApplicationRequest $request)
    {
        $applicationId = $request->route('applicationId');
        $updateElectionApplication = $this->electionApplicationService->updateApplication($request->validated(), $applicationId);
        return ApiResponseService::success('Election Application Updated Successfully', $updateElectionApplication, null, 200);
    }
    public function getAllElectionApplication(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $electionApplication = $this->electionApplicationService->getAllApplications($currentSchool);
        return ApiResponseService::success('Election Application Fetched Succesfully', ElectionApplicationResource::collection($electionApplication), null, 200);
    }
    public function bulkDeleteApplication(ElectionApplicationIdRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $authAdmin = $this->resolveUser();
        $bulkDeleteApplication = $this->electionApplicationService->bulkDeleteApplication($request->electionApplicationIds, $currentSchool, $authAdmin);
        return ApiResponseService::success("Application Deleted Successfully", $bulkDeleteApplication, null, 200);
    }
    public function bulkApproveApplication(ElectionApplicationIdRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $authAdmin = $this->resolveUser();
        $bulkApproveApplication = $this->electionApplicationService->bulkApproveApplication($request->electionApplicationIds, $currentSchool, $authAdmin);
        return ApiResponseService::success("Applications Approved Successfully", $bulkApproveApplication, null, 200);
    }
    public function getApplicationsByStudentId(Request $request, $studentId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $getMyApplications = $this->electionApplicationService->getApplicationsByStudent($currentSchool, $studentId);
        return ApiResponseService::success("Applications Fetched Successfully", $getMyApplications, null, 200);
    }
    public function getApplicationDetails(Request $request, $applicationId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $applicationDetails = $this->electionApplicationService->getApplicationDetails($applicationId, $currentSchool);
        return ApiResponseService::success("Application Details Fetched Successfully", $applicationDetails, null, 200);
    }

    public function getStudentElectionApplication(Request $request, $electionId)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $student = $this->resolveUser();
        $electionApplications = $this->electionApplicationService->getStudentElectionApplications($currentSchool, $student, $electionId);
        return ApiResponseService::success("Election Applications Fetched Successfully", $electionApplications, null, 200);
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
