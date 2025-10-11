<?php

namespace App\Http\Controllers\Election;

use App\Http\Controllers\Controller;
use App\Http\Requests\Election\CreateVoteRequest;
use App\Services\Election\ElectionVoteService;
use App\Services\ApiResponseService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

class ElectionVoteController extends Controller
{
    protected ElectionVoteService $electionVoteService;

    public function __construct(ElectionVoteService $electionVoteService)
    {
        $this->electionVoteService = $electionVoteService;
    }

    public function castVote(CreateVoteRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $authUser = $this->getAuthenticatedUser();
        $castVote = $this->electionVoteService->castVote($request->validated(), $currentSchool, $authUser);
        return ApiResponseService::success('Voted Casted Succfully', $castVote, null, 200);
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
