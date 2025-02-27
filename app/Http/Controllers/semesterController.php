<?php

namespace App\Http\Controllers;

use App\Models\Semester;
use App\Services\SemesterService;
use App\Http\Requests\SemesterRequest;
use App\Http\Requests\UpdateSemesterRequest;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;

class SemesterController extends Controller
{
    //work on the update validation
    protected SemesterService $semesterService;
    public function __construct(SemesterService $semesterService){
            $this->semesterService = $semesterService;
    }
    public function createSemester(SemesterRequest $request){

        $createSemester =  $this->semesterService->createSemester($request->validated());
        return ApiResponseService::success("Semester Created Sucessfully", $createSemester, null, 201);
    }

    public function deleteSemester(Request $request, $semester_id){
        $deleteSemester = $this->semesterService->deleteSemester($semester_id);
        return ApiResponseService::success("Semester Deleted Succefully", $deleteSemester, null, 200);
    }

    public function updateSemester(UpdateSemesterRequest $request, string $semester_id){
        $updateSemester = $this->semesterService->updateSemester($semester_id, $request->validated());
        return ApiResponseService::success("Semester Updated Sucessfully", $updateSemester, null, 200);
    }

    public function getSemesters(Request $request) {

        $currentSchool = $request->attributes->get('currentSchool');
        $getSemesters = $this->semesterService->getSemester($currentSchool);
        return ApiResponseService::success('Semesters fetched Successfully', $getSemesters, null, 200);
    }
}
