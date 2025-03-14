<?php

namespace App\Http\Controllers;

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
        return ApiResponseService::success("Accessed student fetched Sucessfully", $accessedStudents, null, 200);
    }

    public function deleteAccessedStudent(Request $request, $accessedStudentId){
        $this->accessedStudentService->deleteAccessedStudent($accessedStudentId);
    }
}
