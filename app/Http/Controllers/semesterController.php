<?php

namespace App\Http\Controllers;

use App\Services\SemesterService;
use App\Http\Requests\SemesterRequest;
use App\Http\Requests\Semester\CreateSemesterRequest;
use App\Http\Requests\Semester\UpdateSemesterRequest;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;

class SemesterController extends Controller
{
    //work on the update validation
    protected SemesterService $semesterService;
    public function __construct(SemesterService $semesterService){
            $this->semesterService = $semesterService;
    }
    public function createSemester(CreateSemesterRequest $request){

        $createSemester =  $this->semesterService->createSemester($request->validated());
        return ApiResponseService::success("Semester Created Sucessfully", $createSemester, null, 201);
    }

    public function deleteSemester(Request $request, $semesterId){
        $deleteSemester = $this->semesterService->deleteSemester($semesterId);
        return ApiResponseService::success("Semester Deleted Succefully", $deleteSemester, null, 200);
    }

    public function updateSemester(UpdateSemesterRequest $request, string $semesterId){
        $updateSemester = $this->semesterService->updateSemester($semesterId, $request->validated());
        return ApiResponseService::success("Semester Updated Sucessfully", $updateSemester, null, 200);
    }

    public function getSemesters(Request $request) {

        $currentSchool = $request->attributes->get('currentSchool');
        $getSemesters = $this->semesterService->getSemester($currentSchool);
        return ApiResponseService::success('Semesters fetched Successfully', $getSemesters, null, 200);
    }
}
