<?php

namespace App\Http\Controllers\Election;

use App\Http\Controllers\Controller;
use App\Http\Requests\ElectionCandidate\ElectionCandidateIdRequest;
use App\Http\Resources\ElectionCandidateResource;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use App\Services\Election\ElectionCandidateService;

class ElectionCandidateController extends Controller
{
    protected ElectionCandidateService $electionCandidateService;
    public function __construct(ElectionCandidateService $electionCandidateService)
    {
        $this->electionCandidateService  = $electionCandidateService;
    }

    public function getCandidatesByElection(Request $request, $electionId)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $candidates = $this->electionCandidateService->getElectionCandidatesByElection($electionId, $currentSchool);
        return ApiResponseService::success("Election Candidates Fetched Successfully", $candidates, null, 200);
    }

    public function getElectionCadidates(Request $request)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $candidates = $this->electionCandidateService->getElectionCandidates($currentSchool);
        return ApiResponseService::success("Election Candidates Fetched Successfully", ElectionCandidateResource::collection($candidates), null, 200);
    }

    public function getElectionCandidateDetails(Request $request, $candidateId)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $candidateDetails = $this->electionCandidateService->getElectionCandidateDetails($currentSchool, $candidateId);
        return ApiResponseService::success("Candidate Details Fetched Successfully", $candidateDetails, null, 200);
    }

    public function disqualifyCandidate(Request $request, $candidateId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $authAdmin = $this->resolveUser();
        $this->electionCandidateService->disqualifyCandidate($currentSchool, $candidateId, $authAdmin);
        return ApiResponseService::success("Candidate Disqualified Successfully", null, null, 200);
    }

    public function reinstateCandidate(Request $request, $candidateId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $authAdmin = $this->resolveUser();
        $this->electionCandidateService->reinstateCandidate($currentSchool, $candidateId, $authAdmin);
        return ApiResponseService::success("Candidate Reinstated Succesfully", null, null, 200);
    }

    public function bulkReinstateCandidate(ElectionCandidateIdRequest $request, $candidateId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $authAdmin = $this->resolveUser();
        $this->electionCandidateService->bulkReinstateCandidate($currentSchool, $request->candidateIds, $authAdmin);
        return ApiResponseService::success("Candidates Reinstated Successfully", null, null, 200);
    }

    public function bulkDisQualifyCandidate(ElectionCandidateIdRequest $request, $candidateId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $authAdmin = $this->resolveUser();
        $this->electionCandidateService->bulkDisqualifyCandidate($currentSchool, $request->candidateIds, $authAdmin);
        return ApiResponseService::success("Candidates Disqualified Successfully", null, null, 200);
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
