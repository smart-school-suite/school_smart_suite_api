<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AccessedStudentResitService;
use App\Http\Resources\AccessedStudentResitResource;
use App\Services\ApiResponseService;

class AccessedResitStudentController extends Controller
{
    //
    protected $accessedStudentResitService;
    public function __construct(AccessedStudentResitService $accessedStudentResitService){
         $this->accessedStudentResitService = $accessedStudentResitService;
    }

    public function getResitExamCandidates(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $candidates = $this->accessedStudentResitService->getAccessedResitStudent($currentSchool);
        return ApiResponseService::success("Resit Candidates Fetched Succesfully",  AccessedStudentResitResource::collection($candidates), null, 200);
    }

    public function deleteCandidate(Request $request, $candidateId){
        $currentSchool = $request->attributes->get('currentSchool');
        $deleteCandidate = $this->accessedStudentResitService->deleteAccessedResitStudent($currentSchool, $candidateId);
        return ApiResponseService::success("Resit Candidate Deleted Succesfully", $deleteCandidate, null, 200);
    }
}
