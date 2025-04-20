<?php

namespace App\Services;

use App\Models\Marks;
use App\Models\Student;
use App\Models\Examtimetable;
use App\Models\Exams;

class MarkService
{
    // Implement your logic here
    public function deleteMark(string $mark_id, $currentSchool)
    {

        $markExists = Marks::Where('school_branch_id', $currentSchool->id)->find($mark_id);
        if (!$markExists) {
            return ApiResponseService::error('Student Mark Not found', null, 404);
        }
        $markExists->delete();
        return $markExists;
    }
    public function getStudentScores(string $studentId, $currentSchool, string $examId)
    {
        $findStudent = Student::where('school_branch_id', $currentSchool->id)->find($studentId);
        $findExam = Exams::where('school_branch_id', $currentSchool->id)->find($examId);
        if (!$findStudent || !$findExam) {
            return ApiResponseService::error('The Provided Credentials Are InValid', null, 404);
        }
        $scoresData = Marks::where('school_branch_id', $currentSchool->id)
            ->where('exam_id', $examId)
            ->where('student_id', $studentId)
            ->with(['student', 'course', 'exams.examtype', 'level'])
            ->get();

        return $scoresData;
    }
    public function getScoreDetails(string $markId, $currentSchool)
    {
        $findScore = Marks::where("school_branch_id", $currentSchool->id)
            ->with(['student', 'course', 'exams', 'specialty', 'level'])
            ->find($markId);
        if (!$findScore) {
            return ApiResponseService::error("Mark Not found", null, 404);
        }
        return $findScore;
    }
    public function getAcessedCourses(string $examId, string $studentId, $currentSchool)
    {
        $findStudent = Student::find($studentId);
        $findExam = Exams::find($examId);
        if (!$findExam || !$findStudent) {
            return ApiResponseService::error("The provided Credentials are invalid", null, 404);
        }
        $examCourses = Examtimetable::where("school_branch_id", $currentSchool->id)
            ->where("exam_id", $examId)
            ->where("specialty_id", $findStudent->specialty_id)
            ->with(["course"])
            ->get();

        return $examCourses && $findStudent && $findExam;
    }
    public function getAllStudentsScores($currentSchool)
    {
        $studentScores = Marks::where("school_branch_id", $currentSchool->id)->with(['course', 'student', 'exams.examtype', 'level', 'specialty'])->get();
        return $studentScores;
    }

}
