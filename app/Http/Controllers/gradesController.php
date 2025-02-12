<?php

namespace App\Http\Controllers;

use App\Services\AddGradesService;
use App\Http\Requests\GradesRequest;
use App\Services\ApiResponseService;
use App\Services\GradesService;
use Exception;
use Illuminate\Http\Request;

class gradesController extends Controller
{
    //when creating grades for EXAM (FIRST_SEMESTER, SECOND_SEMESTER, THIRD_SEMESTER, NTH_SEMESETER)
    //You need to take into consideration the related weighted mark of the ca
    // eg if weighted mark for ca = 30 and weighted_exam_mark = 70 then means your grades of the exam will be based on
    // 30 + 70 = 100 which is 100

    protected AddGradesService  $addGradesService;
    protected GradesService $gradesService;
    public function __construct(AddGradesService $addGradesService, GradesService $gradesService)
    {
        $this->addGradesService = $addGradesService;
        $this->gradesService = $gradesService;
    }
    public function makeGradeForExamScoped(GradesRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');

        try {
            $createGrades = $this->addGradesService->makeGradeForExam($request->grades, $currentSchool);
            return ApiResponseService::success("Exam Grades Created Succefully", $createGrades, null, 201);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, $e->getCode() ?: 500);
        }
    }
    public function get_all_grades_scoped(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $getGrades = $this->gradesService->getGrades($currentSchool);
        return ApiResponseService::success("Exam Grades Fetched Sucessfully", $getGrades, null, 200);
    }


    public function delete_grades_scoped(Request $request, $grades_id)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $deleteGrades = $this->gradesService->deleteGrades($currentSchool, $grades_id);
        return ApiResponseService::success("Grade Deleted Sucessfully", $deleteGrades, null, 200);
    }
}
