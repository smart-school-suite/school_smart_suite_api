<?php

namespace App\Http\Controllers\ResitExam;

use App\Http\Controllers\Controller;
use App\Http\Resources\ResitExamCandidateResource;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use App\Services\Resit\ResitCandidateService;

class ResitCandidateController extends Controller
{
       protected ResitCandidateService $resitCandidateService;
   public function __construct(ResitCandidateService $resitCandidateService){
      $this->resitCandidateService = $resitCandidateService;
   }

   public function getResitCandidates(Request $request){
      $currentSchool = $request->attributes->get('currentSchool');
      $resitCandidates = $this->resitCandidateService->getResitCandidates($currentSchool);
      return ApiResponseService::success("Resit Candidates Fetched Successfully",
       ResitExamCandidateResource::collection($resitCandidates), null, 200);
   }

   public function deleteResitCandidate(Request $request, $candidateId){
      $currentSchool = $request->attributes->get('currentSchool');
       $this->resitCandidateService->deleteCandidates($currentSchool, $candidateId);
       return ApiResponseService::success("Student Deleted Successfully", null, null, 200);

   }
}
