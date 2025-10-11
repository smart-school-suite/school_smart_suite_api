<?php

namespace App\Http\Controllers\Election;

use App\Http\Controllers\Controller;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use App\Services\Election\ElectionResultService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class ElectionResultController extends Controller
{
    protected ElectionResultService $electionResultService;
    public function __construct(ElectionResultService $electionResultService)
    {
        $this->electionResultService = $electionResultService;
    }
    public function getElectionResults(Request $request, $electionId)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $electionResults = $this->electionResultService->fetchElectionResults($electionId, $currentSchool);
        return ApiResponseService::success("Election Results Fetched Successfully", $electionResults, null, 200);
    }
    public function getLiveElectionResults(Request $request, $electionId)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $electionResults = $this->electionResultService->getLiveElectionResults($electionId, $currentSchool, $this->getAuthenticatedUser());
        return ApiResponseService::success("Live Election Results Fetched Successfully", $electionResults, null, 200);
    }
    private function getAuthenticatedUser()
    {
        $user = Auth::user();

        if ($user instanceof Model) {
            return [
                'userId' => $user->id,
                'userType' => get_class($user),
                'authUser' => $user
            ];
        }

        return [
            'userId' => null,
            'userType' => null,
            'authUser' => $user
        ];
    }
}
