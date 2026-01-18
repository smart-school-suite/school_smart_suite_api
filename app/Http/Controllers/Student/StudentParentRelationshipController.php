<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use App\Services\Student\StudentParentRelationshipService;

class StudentParentRelationshipController extends Controller
{
    protected StudentParentRelationshipService $studentParentRelationshipService;
    public function __construct(StudentParentRelationshipService $studentParentRelationshipService)
    {
        $this->studentParentRelationshipService = $studentParentRelationshipService;
    }

    public function getActiveStudentParentRelationships(Request $request)
    {
        $relationships = $this->studentParentRelationshipService->getActiveStudentParentRelationship();
        return ApiResponseService::success("Active Student Parent Relationship Fetched Successfully", $relationships, null, 200);
    }

    public function getAllStudentParentRelationship(Request $request)
    {
        $relationships = $this->studentParentRelationshipService->getAllStudentParentRelationship();
        return ApiResponseService::success("All Student Parent Relationships Fetched Successfully", $relationships, null, 200);
    }
}
