<?php

namespace App\Http\Controllers;

use App\Http\Requests\ElectionApplication\ElectionApplicationIdRequest;
use App\Services\ElectionApplicationService;
use App\Http\Resources\ElectionApplicationResource;
use App\Http\Requests\ElectionApplication\CreateApplicationRequest;
use App\Http\Requests\ElectionApplication\UpdateElectionApplicationRequest;
use App\Services\ApiResponseService;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

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
        $approveApplication = $this->electionApplicationService->approveApplication($applicationId, $currentSchool);
        return ApiResponseService::success('Application approval was sucessfull', $approveApplication, null, 200);
    }

    public function deleteApplication(Request $request)
    {
        $applicationId = $request->route('applicationId');
        $application = $this->electionApplicationService->deleteApplication($applicationId);
        return ApiResponseService::success('Application Deleted Succefully', $application, null, 200);
    }

    public function getApplications(Request $request, $electionId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $electionId = $request->route('electionId');
        $electionApplications = $this->electionApplicationService->getApplications($electionId, $currentSchool);
        return ApiResponseService::success('Election Applicaitons Fetched Succefully', ElectionApplicationResource::collection($electionApplications), null, 200);
    }

    public function updateApplication(UpdateElectionApplicationRequest $request)
    {
        $applicationId = $request->route('applicationId');
        $updateElectionApplication = $this->electionApplicationService->updateApplication($request->validated(), $applicationId);
        return ApiResponseService::success('Election Application Updated Successfully', $updateElectionApplication, null, 200);
    }

    public function getAllElectionApplication(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $electionApplication = $this->electionApplicationService->getAllApplications($currentSchool);
        return ApiResponseService::success('Election Application Fetched Succesfully', $electionApplication, null, 200);
    }

    public function bulkDeleteApplication(ElectionApplicationIdRequest $request){
        try{
          $bulkDeleteApplication = $this->electionApplicationService->bulkDeleteApplication($request->electionApplicationIds);
          return ApiResponseService::success("Application Deleted Successfully", $bulkDeleteApplication, null, 200);
        }
        catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }

    public function bulkApproveApplication(ElectionApplicationIdRequest $request){
        try{
            $currentSchool = $request->attributes->get('currentSchool');
          $bulkApproveApplication = $this->electionApplicationService->bulkApproveApplication($request->electionApplicationIds, $currentSchool);
          return ApiResponseService::success("Applications Approved Successfully", $bulkApproveApplication, null, 200);
        }
        catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }
}
