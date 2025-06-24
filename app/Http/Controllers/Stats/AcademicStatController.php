<?php

namespace App\Http\Controllers\Stats;

use App\Http\Controllers\Controller;
use App\Services\ApiResponseService;
use Illuminate\Http\Request;
use App\Services\Stats\StudentExamStandingStats;
use App\Services\Stats\ExamStatService;
use App\Services\Stats\CaExamStatService;
use App\Services\Stats\StudentCaExamStatService;
use App\Services\Stats\StudentExamStatService;
use App\Services\Stats\AcademicStatService;
class AcademicStatController extends Controller
{
    protected StudentExamStandingStats $studentExamStandingStats;
    protected ExamStatService $examStatService;
    protected CaExamStatService $caExamStatService;
    protected StudentCaExamStatService $studentCaExamStatService;
    protected StudentExamStatService $studentExamStatService;
    protected AcademicStatService $academicStatService;

    public function __construct(
        StudentExamStandingStats $studentExamStandingStats,
        ExamStatService $examStatService,
        CaExamStatService $caExamStatService,
        StudentCaExamStatService $studentCaExamStatService,
        StudentExamStatService $studentExamStatService,
        AcademicStatService $academicStatService
    ){
        $this->studentExamStandingStats = $studentExamStandingStats;
        $this->examStatService = $examStatService;
        $this->caExamStatService = $caExamStatService;
        $this->studentCaExamStatService = $studentCaExamStatService;
        $this->studentExamStatService = $studentExamStatService;
        $this->academicStatService = $academicStatService;
    }

    public function getStudentExamStandings(Request $request, string $examId){
        $currentSchool = $request->attributes->get('currentSchool');
        $studentStandings = $this->studentExamStandingStats->getStudentStandingsByExam($examId, $currentSchool);
        return ApiResponseService::success("Student Exam Standings Fetched Successfully", $studentStandings, null, 200);
    }

    public function getExamStats(Request $request, string $examId){
        $currentSchool = $request->attributes->get('currentSchool');
        $examStats = $this->examStatService->getExamStat($currentSchool, $examId);
        return ApiResponseService::success("School Exam Stats Fetched Successfully", $examStats, null, 200);
    }

    public function getCaExamStats(Request $request, string $examId){
        $currentSchool = $request->attributes->get('currentSchool');
        $caExamStats = $this->caExamStatService->getCaExamStats($currentSchool, $examId);
        return ApiResponseService::success("School CA exam stats fetched Successfully", $caExamStats, null, 200);
    }

    public function getStudentCaExamStats(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $examId = $request->route('examId');
        $studentId = $request->route('studentId');
        $studentCaExamStats = $this->studentCaExamStatService->getStudentCaExamStat($currentSchool, $examId, $studentId);
        return ApiResponseService::success("Student CA Results Fetched Successfully", $studentCaExamStats, null, 200);
    }

    public function getStudentExamStats(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $examId = $request->route('examId');
        $studentId = $request->route('studentId');
        $studentExamStats = $this->studentExamStatService->getStudentExamStat($currentSchool, $examId, $studentId);
        return ApiResponseService::success("Student Exam Results Fetched Successfully", $studentExamStats, null, 200);
    }

    public function getSchoolAcademicStats(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $year = $request->route('year');
        $academicStats = $this->academicStatService->getAcademicStats($currentSchool, $year);
        return ApiResponseService::success("School Academic Stats Fetched Successfully", $academicStats, null, 200);
    }

}
