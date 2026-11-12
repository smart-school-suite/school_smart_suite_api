<?php

namespace App\Http\Controllers\Election;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Election\Concerns\ResolvesElectionUser;
use App\Http\Requests\ElectionApplication\CreateApplicationRequest;
use App\Http\Requests\ElectionApplication\ElectionApplicationIdRequest;
use App\Http\Requests\ElectionApplication\ReviewElectionApplicationRequest;
use App\Http\Requests\ElectionApplication\UpdateElectionApplicationRequest;
use App\Models\Election\ElectionApplication;
use App\Services\ApiResponseService;
use App\Services\Election\ElectionApplicationService;
use Illuminate\Http\Request;

class ElectionApplicationController extends Controller
{
    use ResolvesElectionUser;

    public function __construct(protected ElectionApplicationService $applicationService) {}

    public function createElectionApplication(CreateApplicationRequest $request)
    {
        $application = $this->applicationService->createApplication($request->validated(), $this->currentSchool($request), $this->authUser());
        return ApiResponseService::success("Election Application Submitted Successfully", $this->applicationService->present($application), null, 201);
    }

    public function updateApplication(UpdateElectionApplicationRequest $request, string $applicationId)
    {
        $application = $this->applicationService->updateApplication($request->validated(), $applicationId, $this->currentSchool($request), $this->authUser());
        return ApiResponseService::success("Election Application Updated Successfully", $this->applicationService->present($application), null, 200);
    }

    public function withdrawApplication(Request $request, string $applicationId)
    {
        $application = $this->applicationService->withdrawApplication($applicationId, $this->currentSchool($request), $this->authUser());
        return ApiResponseService::success("Election Application Withdrawn Successfully", $this->applicationService->present($application), null, 200);
    }

    public function deleteApplication(Request $request, string $applicationId)
    {
        $this->adminUser();
        $application = $this->applicationService->deleteApplication($applicationId, $this->currentSchool($request));
        return ApiResponseService::success("Election Application Deleted Successfully", $application, null, 200);
    }

    public function bulkDeleteApplication(ElectionApplicationIdRequest $request)
    {
        $this->adminUser();
        $applications = $this->applicationService->bulkDeleteApplications($this->ids($request, 'electionApplicationIds', 'election_application_id'), $this->currentSchool($request));
        return ApiResponseService::success("Election Applications Deleted Successfully", $applications, null, 200);
    }

    public function approveApplication(ReviewElectionApplicationRequest $request, string $applicationId)
    {
        $application = $this->applicationService->approveApplication($applicationId, $this->currentSchool($request), $this->adminUser(), $request->validated()['remarks'] ?? null);
        return ApiResponseService::success("Election Application Approved Successfully", $this->applicationService->present($application), null, 200);
    }

    public function rejectApplication(ReviewElectionApplicationRequest $request, string $applicationId)
    {
        $application = $this->applicationService->rejectApplication($applicationId, $this->currentSchool($request), $this->adminUser(), $request->validated()['remarks'] ?? null);
        return ApiResponseService::success("Election Application Rejected Successfully", $this->applicationService->present($application), null, 200);
    }

    public function bulkApproveApplication(ElectionApplicationIdRequest $request)
    {
        return $this->bulkReview($request, ElectionApplication::STATUS_APPROVED, "Election Applications Approved Successfully");
    }

    public function bulkRejectApplication(ElectionApplicationIdRequest $request)
    {
        return $this->bulkReview($request, ElectionApplication::STATUS_REJECTED, "Election Applications Rejected Successfully");
    }

    public function getAllElectionApplication(Request $request)
    {
        $applications = $this->applicationService->getApplications($this->currentSchool($request), $request->only(['election_id', 'election_role_id', 'status', 'applicant_type']));
        return ApiResponseService::success("Election Applications Fetched Successfully", $applications, null, 200);
    }

    public function getApplications(Request $request, string $electionId)
    {
        $filters = array_merge($request->only(['election_role_id', 'status', 'applicant_type']), ['election_id' => $electionId]);
        return ApiResponseService::success("Election Applications Fetched Successfully", $this->applicationService->getApplications($this->currentSchool($request), $filters), null, 200);
    }

    public function getApplicationDetails(Request $request, string $applicationId)
    {
        return ApiResponseService::success("Election Application Details Fetched Successfully", $this->applicationService->getApplicationDetails($applicationId, $this->currentSchool($request)), null, 200);
    }

    /** Applications of the signed-in participant (optionally for one election). */
    public function getMyApplications(Request $request, ?string $electionId = null)
    {
        $applications = $this->applicationService->getParticipantApplications($this->currentSchool($request), $this->authUser(), $electionId);
        return ApiResponseService::success("Election Applications Fetched Successfully", $applications, null, 200);
    }

    private function bulkReview(ElectionApplicationIdRequest $request, string $decision, string $message)
    {
        $applications = $this->applicationService->bulkReview(
            $this->ids($request, 'electionApplicationIds', 'election_application_id'),
            $decision,
            $this->currentSchool($request),
            $this->adminUser(),
            $request->validated()['remarks'] ?? null
        );
        return ApiResponseService::success($message, $applications, null, 200);
    }
}
