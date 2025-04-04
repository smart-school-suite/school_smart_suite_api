<?php

namespace App\Http\Controllers;

use App\Services\ApiResponseService;
use App\Services\StudentResultService;
use App\Http\Resources\StudentResultResource;
use Illuminate\Http\Request;

class StudentResultController extends Controller
{
    //
    protected $studentResultService;
    public function __construct(StudentResultService $studentResultService)
    {
        $this->studentResultService = $studentResultService;
    }
    public function getAllStudentResults(Request $request)
    {
        $currentSchool = $request->attributes->get("currentSchool");
        $studentResults = $this->studentResultService->getAllStudentResults($currentSchool);
        return ApiResponseService::success("Student Results Fetched Successfully", StudentResultResource::collection($studentResults), null, 200);
    }
}
