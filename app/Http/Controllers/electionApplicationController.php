<?php

namespace App\Http\Controllers;

use App\Http\Requests\ElectionApplicationRequest;
use App\Services\ElectionApplicationService;
use App\Http\Requests\UpdateElectionApplicationRequest;
use App\Http\Resources\ElectionApplicationResource;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;

class ElectionApplicationController extends Controller
{
    protected ElectionApplicationService $electionApplicationService;
    public function __construct(ElectionApplicationService $electionApplicationService)
    {
        $this->electionApplicationService = $electionApplicationService;
    }
    public function createElectionApplication(ElectionApplicationRequest $request)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $electionApplication = $this->electionApplicationService->createApplication($request->validated(), $currentSchool);
        return ApiResponseService::success("Election Application Created Succesfully", $electionApplication, null, 201);
    }

    public function approveApplication(Request $request)
    {
        $applicationId = $request->route('application_id');
        $currentSchool = $request->attributes->get('currentSchool');
        $approveApplication = $this->electionApplicationService->approveApplication($applicationId, $currentSchool);
        return ApiResponseService::success('Application approval was sucessfull', $approveApplication, null, 200);
    }

    public function deleteApplication(Request $request)
    {
        $applicationId = $request->route('application_id');
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
        $applicationId = $request->route('application_id');
        $updateElectionApplication = $this->electionApplicationService->updateApplication($request->validated(), $applicationId);
        return ApiResponseService::success('Election Application Updated Successfully', $updateElectionApplication, null, 200);
    }
}
