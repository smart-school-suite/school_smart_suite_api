<?php

namespace App\Services;

use App\Models\StudentResults;

class StudentResultService
{
    // Implement your logic heres

    public function getMyResults($currentSchool, $examId, $studentId)
    {
        $examResults = StudentResults::where("school_branch_id", $currentSchool->id)
            ->where("exam_id", $examId)
            ->where("student_id", $studentId)
            ->with(['student', 'specialty', 'level', 'exam'])
            ->get();
        return $examResults;
    }

    public function getExamStandings($examId, $currentSchool)
    {
        $examResults = StudentResults::where("school_branch_id", $currentSchool->id)
            ->where("exam_id", $examId)
            ->orderBy('gpa', 'desc')
            ->with(['student', 'specialty', 'level'])
            ->get();
        return $examResults;
    }

    //generate pdf of student results and exam standings
}
