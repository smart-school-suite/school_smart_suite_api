<?php

namespace App\Http\Controllers;

use App\Models\Semester;
use App\Services\SemesterService;
use App\Http\Requests\SemesterRequest;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;

class semesterController extends Controller
{
    //work on the update validation
    protected SemesterService $semesterService;
    public function __construct(SemesterService $semesterService){
            $this->semesterService = $semesterService;
    }
    public function create_semester(SemesterRequest $request){

        $createSemester =  $this->semesterService->createSemester($request->validated());
        return ApiResponseService::success("Semester Created Sucessfully", $createSemester, null, 201);
    }

    public function delete_semester(Request $request, $semester_id){
        $deleteSemester = $this->semesterService->deleteSemester($semester_id);
        return ApiResponseService::success("Semester Deleted Succefully", $deleteSemester, null, 200);
    }

    public function update_semester(Request $request, string $semester_id){
        $updateSemester = $this->semesterService->updateSemester($semester_id, $request->validated());
        return ApiResponseService::success("Semester Updated Sucessfully", $updateSemester, null, 200);
    }

    public function get_all_semesters(Request $request) {

        $currentSchool = $request->attributes->get('currentSchool');
        $getSemesters = $this->semesterService->getSemester($currentSchool);
        return ApiResponseService::success('Semesters fetched Successfully', $getSemesters, null, 200);
    }
}
