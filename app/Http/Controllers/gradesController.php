<?php

namespace App\Http\Controllers;

use App\Services\AddGradesService;
use App\Http\Requests\GradesRequest;
use App\Services\ApiResponseService;
use App\Models\Exams;
use App\Models\Examtype;
use App\Models\Timetable;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\SpecailtyTimeTableRequest;
use App\Services\GradesService;
use App\Rules\TimetableRule;
use Exception;
use Illuminate\Http\Request;

class GradesController extends Controller
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
    public function createExamGrades(GradesRequest $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        try {
            $createGrades = $this->addGradesService->makeGradeForExam($request->grades, $currentSchool);
            return ApiResponseService::success("Exam Grades Created Succefully", $createGrades, null, 201);
        } catch (Exception $e) {
            return ApiResponseService::error($e->getMessage(), null, 500);
        }
    }
    public function getAllGrades(Request $request)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $getGrades = $this->gradesService->getGrades($currentSchool);
        return ApiResponseService::success("Exam Grades Fetched Sucessfully", $getGrades, null, 200);
    }


    public function deleteGrades(Request $request, $examId)
    {
        $currentSchool = $request->attributes->get('currentSchool');
        $deleteGrades = $this->gradesService->deleteGrades($currentSchool, $examId);
        return ApiResponseService::success("Grade Deleted Sucessfully", $deleteGrades, null, 200);
    }

    public function getGradesConfigByExam(Request $request, string $examId){
        $currentSchool = $request->attributes->get('currentSchool');

        $gradesByExam = $this->gradesService->getExamGradesConfiguration($currentSchool, $examId);
        return ApiResponseService::success("Grades By Exam Configuration Fetched Successfully", $gradesByExam, null, 200);
    }

    public function getExamConfigData(Request $request, string $examId){
        $currentSchool = $request->attributes->get('currentSchool');
        $getExamConfig = $this->gradesService->getExamConfigData($currentSchool, $examId);
        return ApiResponseService::success("Exam Grades Configuration Fetched Succesfully", $getExamConfig, null, 200);
    }
    public function getRelatedExams(Request $request, $examId){
        $currentSchool = $request->attributes->get('currentSchool');
        $exam = Exams::where('school_branch_id', $currentSchool->id)
        ->where('id', $examId)
        ->with(['specialty', 'level', 'examType', 'schoolSemester'])
        ->first();

        $examType = $exam->examType;
        if (!$examType || $examType->type == 'exam') {
            $semester = $examType->semester;
            $caExamType = Examtype::where('semester', $semester)
                ->where('type', 'ca')
                ->first();
                if (!$caExamType) {
                    return ApiResponseService::error("exam type not found", null, 404);
                }
            $additionalExams = Exams::where('exam_type_id', $caExamType->id)
            ->where('specialty_id', $exam->specialty_id)
            ->where('semester_id', $exam->semester_id)
            ->where("level_id", $exam->level_id)
            ->with(['examType', 'level', 'specialty', 'schoolSemester'])
            ->get();
            if($additionalExams->isEmpty()){
                return ApiResponseService::error("No related exams found for {$exam->specialty->specialty_name} {$exam->level->level_name}", null, 404);
            }
            return ApiResponseService::success("Related Exams Fetched Sucessfully", [$additionalExams, $exam], null, 200);
        } else {
            return ApiResponseService::error("This is not an exam", null, 400);
        }
    }


}
