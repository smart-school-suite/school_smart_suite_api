<?php

namespace App\Services\Exam;

use App\Models\Exam\Exam;
use App\Models\Exam\ExamScore;

class ExamResultService
{
    public function getExams(object $currentSchool)
    {
        $exams = Exam::where('school_branch_id', $currentSchool->id)
            ->with([
                'examType.semesters',
                'schoolYear.specialty.level',
                'examGradeScale',
                'schoolYear.systemAcademicYear'
            ])
            ->get();
    }

    public function getExamEvaluationProgress(object $currentSchool, string $examId){
         $candidates = Exam::where("school_branch_id", $currentSchool->id)
                              ->where("id", $examId)
                              ->first();
    }

    public function getExamStandings(object $currentSchool, string $examId){

    }
}
