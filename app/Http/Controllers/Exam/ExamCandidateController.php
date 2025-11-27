<?php

namespace App\Http\Controllers\Exam;

use App\Http\Controllers\Controller;
use App\Http\Resources\ExamCandidateResource;
use Illuminate\Http\Request;
use App\Services\Exam\ExamCandidateService;
use App\Services\ApiResponseService;

class ExamCandidateController extends Controller
{
    protected ExamCandidateService $accessedStudentService;
    public function __construct(ExamCandidateService $accessedStudentService)
    {
        $this->accessedStudentService = $accessedStudentService;
    }
    public function getAccessedStudent(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $accessedStudents = $this->accessedStudentService->getAccessedStudents($currentSchool);
        return ApiResponseService::success("Accessed student fetched Sucessfully", ExamCandidateResource::collection($accessedStudents), null, 200);
    }

    public function deleteAccessedStudent(Request $request, $candidateId)
    {
        $deleteCandidate =  $this->accessedStudentService->deleteAccessedStudent($candidateId);
        ApiResponseService::success("Accessed Student Deleted Successfully", $deleteCandidate, null, 200);
    }
}
