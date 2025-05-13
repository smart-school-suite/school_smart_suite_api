<?php

namespace App\Http\Controllers;

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

    public function getApplications(Request $request, $election_id)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $election_id = $request->route('election_id');
        $electionApplications = $this->electionApplicationService->getApplications($election_id, $currentSchool);
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

    public function bulkDeleteApplication($applicationIds){
        $idsArray = explode(',', $applicationIds);

        $idsArray = array_map('trim', $idsArray);
        if (empty($idsArray)) {
            return ApiResponseService::error("No IDs provided", null, 422);
        }
        $validator = Validator::make(['ids' => $idsArray], [
            'ids' => 'required|array',
            'ids.*' => 'string|exists:election_application,id',
        ]);
        if ($validator->fails()) {
            return ApiResponseService::error($validator->errors(), null, 422);
        }
        try{
          $bulkDeleteApplication = $this->electionApplicationService->bulkDeleteApplication($idsArray);
          return ApiResponseService::success("Application Deleted Successfully", $bulkDeleteApplication, null, 200);
        }
        catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }

    public function bulkApproveApplication(Request $request, $applicationIds){
        $currentSchool = $request->attributes->get("currentSchool");
        $idsArray = explode(',', $applicationIds);

        $idsArray = array_map('trim', $idsArray);
        if (empty($idsArray)) {
            return ApiResponseService::error("No IDs provided", null, 422);
        }
        $validator = Validator::make(['ids' => $idsArray], [
            'ids' => 'required|array',
            'ids.*' => 'string|exists:election_application,id',
        ]);
        if ($validator->fails()) {
            return ApiResponseService::error($validator->errors(), null, 422);
        }
        try{
          $bulkApproveApplication = $this->electionApplicationService->bulkApproveApplication($idsArray, $currentSchool);
          return ApiResponseService::success("Applications Approved Successfully", $bulkApproveApplication, null, 200);
        }
        catch(Exception $e){
            return ApiResponseService::error($e->getMessage(), null, 400);
        }
    }
}
