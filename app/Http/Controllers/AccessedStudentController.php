<?php

namespace App\Http\Controllers;

use App\Http\Resources\ExamCandidateResource;
use Illuminate\Http\Request;
use App\Services\AccessedStudentService;
use App\Services\ApiResponseService;

class AccessedStudentController extends Controller
{
    //
    protected AccessedStudentService $accessedStudentService;
    public function __construct(AccessedStudentService $accessedStudentService){
         $this->accessedStudentService = $accessedStudentService;
    }

    public function getAccessedStudent(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $accessedStudents = $this->accessedStudentService->getAccessedStudents($currentSchool);
        return ApiResponseService::success("Accessed student fetched Sucessfully", ExamCandidateResource::collection($accessedStudents), null, 200);
    }

    public function deleteAccessedStudent(Request $request, $candidateId){
      $deleteCandidate =  $this->accessedStudentService->deleteAccessedStudent($candidateId);
        ApiResponseService::success("Accessed Student Deleted Successfully", $deleteCandidate, null, 200);
    }
}
