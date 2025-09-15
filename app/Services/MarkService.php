<?php

namespace App\Services;

use App\Models\Marks;
use App\Models\Examtype;
use Exception;
use App\Models\Student;
use App\Models\Examtimetable;
use App\Models\Exams;
use App\Models\Grades;
use App\Models\StudentResults;
use App\Models\Timetable;
use App\Models\Courses;
use App\Models\AccessedStudent;
use Illuminate\Support\Facades\Log;

class MarkService
{
    // Implement your logic here

    public function getMarksByCandidate(string $candidateId, $currentSchool){
        $candidate = AccessedStudent::find($candidateId);
        $marks = Marks::where("school_branch_id", $currentSchool->id)
                          ->where("student_id", $candidate->student_id)
                          ->where("level_id", $candidate->level_id)
                          ->where("specialty_id", $candidate->specialty_id)
                          ->get();
        return $marks;
    }
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
    public function prepareCaDataByExam($currentSchool, $studentId, $examId): array
    {
        $exam = Exams::where("school_branch_id", $currentSchool->id)->findOrFail($examId);
        $student = Student::where("school_branch_id", $currentSchool->id)->findOrFail($studentId);
        $caExam = $this->findExamsBasedOnCriteria($exam->id);
        $caScores = Marks::where("school_branch_id", $currentSchool->id)
            ->where("student_id", $student->id)
            ->where("exam_id", $caExam->id)
            ->with(['course'])
            ->get();
        $caResult = StudentResults::where("school_branch_id", $currentSchool->id)
            ->where("student_id", $student->id)
            ->where("exam_id", $caExam->id)
            ->get();
        return [
            'exam' => $exam,
            'student' => $student,
            'caExam' => $caExam,
            'caScores' => $caScores,
            'caResult' => $caResult,
        ];
    }
    public function prepareCaData($currentSchool, $examId, $studentId): array
    {
        $exam = Exams::where("school_branch_id", $currentSchool->id)->findOrFail($examId);
        $student = Student::where("school_branch_id", $currentSchool->id)->findOrFail($studentId);
        $caScores = Marks::where("school_branch_id", $currentSchool->id)
            ->where("student_id", $student->id)
            ->where("exam_id", $exam->id)
            ->with(['course'])
            ->get();
        $caResult = StudentResults::where("school_branch_id", $currentSchool->id)
            ->where("student_id", $student->id)
            ->where("exam_id", $exam->id)
            ->get();
        return [
            'exam' => $exam,
            'student' => $student,
            'ca_scores' => $caScores,
            'ca_result' => $caResult
        ];
    }
    public function prepareExamData($currentSchool, $examId, $studentId)
    {
        $exam = Exams::where("school_branch_id", $currentSchool->id)->findOrFail($examId);
        $caExam = $this->findExamsBasedOnCriteria($exam->id);
        $student = Student::where("school_branch_id", $currentSchool->id)->findOrFail($studentId);
        $examScores = Marks::where("school_branch_id", $currentSchool->id)
            ->where("student_id", $student->id)
            ->where("exam_id", $exam->id)
            ->with(['course'])
            ->get();
        $examResult = StudentResults::where("school_branch_id", $currentSchool->id)
            ->where("student_id", $student->id)
            ->where("exam_id", $exam->id)
            ->get();
        $caScores = Marks::where("school_branch_id", $currentSchool->id)
            ->where("student_id", $student->id)
            ->where("exam_id", $caExam->id)
            ->with(['course'])
            ->get();
        $caResult = StudentResults::where("school_branch_id", $currentSchool->id)
            ->where("student_id", $student->id)
            ->where("exam_id", $caExam->id)
            ->get();
        return [
            'exam' => $exam,
            'student' => $student,
            'exam_scores' => $examScores,
            'exam_result' => $examResult,
            'ca_scores' => $caScores,
            'ca_result' => $caResult
        ];
    }
    private function findExamsBasedOnCriteria(string $examId)
    {
        $exam = Exams::with('examType')->findOrFail($examId);
        if ($exam->examType->type !== 'exam') {
            throw new Exception('Exam type is not valid or not found');
        }

        $caExamType = ExamType::where('semester_id', $exam->examType->semester_id)
            ->where('type', 'ca')
            ->firstOrFail();

        if (!$caExamType) {
            throw new Exception('Corresponding CA exam type not found');
        }

        $additionalExam = Exams::where('exam_type_id', $caExamType->id)
            ->where('specialty_id', $exam->specialty_id)
            ->where('level_id', $exam->level_id)
            ->where('semester_id', $exam->semester_id)
            ->where("student_batch_id", $exam->student_batch_id)
            ->first();

        if (!$additionalExam) {
            throw new Exception('No additional exam found');
        }

        return $additionalExam;
    }

    public function getCaExamEvaluationHelperData($currentSchool, $examId){
         try{
            $exam = Exams::where("school_branch_id", $currentSchool->id)->findorFail($examId);
            $examGrades = Grades::where("school_branch_id", $currentSchool->id)
                                    ->where("grades_category_id", $exam->grades_category_id)
                                    ->with(['lettergrade'])
                                    ->get();
            $timetableSlots = Examtimetable::where("school_branch_id", $currentSchool->id)
                          ->where("specialty_id", $exam->specialty_id)
                          ->where("student_batch_id", $exam->student_batch_id)
                          ->where("level_id", $exam->level_id)
                          ->where("exam_id", $exam->id)
                          ->pluck('course_id')->toArray();
            $courses  = Courses::where("school_branch_id", $currentSchool->id)
                                  ->whereIn('id', array_unique($timetableSlots))
                                  ->get();

            return [
                'exam_grading' => $examGrades,
                'courses' => $courses,
                'max_gpa' => $currentSchool->max_gpa ?? 4.00
            ];
         }
         catch(Exception $e){
           throw $e;
         }
    }

    public function getExamEvaluationHelperData($currentSchool, $examId, $studentId){
         try{
             $exam = Exams::where("school_branch_id", $currentSchool->id)->findorFail($examId);
             $relatedCA = $this->findExamsBasedOnCriteria($examId);
             $examGrades = Grades::where("school_branch_id", $currentSchool->id)
                                    ->where("grades_category_id", $exam->grades_category_id)
                                    ->with(['lettergrade'])
                                    ->get();
            $caScores = Marks::where("school_branch_id", $currentSchool->id)
                              ->where("student_id", $studentId)
                              ->where('exam_id', $relatedCA->id)
                              ->with(['course'])
                              ->get();
            return [
                'exam_grading' => $examGrades,
                'ca_scores' => $caScores,
                'max_gpa' => $currentSchool->max_gpa ?? 4.00
            ];

         }
         catch(Exception $e){
            throw $e;
         }
    }
}
