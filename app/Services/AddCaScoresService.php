<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use App\Models\Exams;
use App\Models\Student;
use App\Models\AccessedStudent;
use App\Models\Courses;
use App\Models\Marks;
use App\Models\Grades;
use App\Models\StudentResults;

class AddCaScoresService
{
    // Implement your logic here

    public function addCaScore(array $studentScores, $currentSchool)
    {
        $result = [];
        $examDetails = null;
        try {
            DB::beginTransaction();
            foreach ($studentScores as $scoreData) {
                $student = $this->getStudent($currentSchool->id, $scoreData['student_id']);
                $exam = Exams::find($scoreData['exam_id']);
                $examDetails = $exam;
                $this->validateStudentAndExam($student, $exam);
                if ($this->isDuplicateEntry($currentSchool->id, $scoreData, $student)) {
                    throw new Exception('Duplicate data entry for this student', 409);
                }
                $this->validateCaMark($exam, $scoreData['score']);
                $marks = $this->createMarks($scoreData, $student, $currentSchool->id, $exam);
                $accessedStudent = AccessedStudent::findOrFail($scoreData['accessment_id']);
                $this->updateAccessedStudent($accessedStudent);
                $result[] = $marks;
            }
            $totalScoreAndGpa = $this->calculateGpaAndTotalScore($result);
            $this->addStudentResultRecords($student, $currentSchool, $totalScoreAndGpa, $examDetails, $result);
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    private function createMarks(array $scoreData, $student, $schoolId, $exam)
    {
        $gradeData = $this->prepareGradeData($scoreData, $schoolId);
        $course = Courses::find($scoreData['course_id']);
        Marks::create([
            'student_batch_id' => $student->student_batch_id,
            'course_id' => $scoreData['course_id'],
            'student_id' => $student->id,
            'exam_id' => $exam->id,
            'level_id' => $student->level_id,
            'score' => $gradeData['score']['score'],
            'specialty_id' => $student->specialty_id,
            'school_branch_id' => $schoolId,
            'grade' => $gradeData['grade'],
            'grade_status' => $gradeData['grade_status'],
            'resit_status' => $gradeData['resitStatus'],
            'grade_points' => $gradeData['grade_points'],
            'gratification' => $gradeData['gratification'],
        ]);
        return [
            'course_id' => $course->id,
            'course_name' => $course->name,
            'course_code' => $course->code,
            'score' => $gradeData['score'],
            'grade' => $gradeData['letterGrade'],
            'grade_status' => $gradeData['gradeStatus'],
            'gratification' => $gradeData['gratification'],
            'grade_points' => $gradeData['gradePoints'],
            'resit_status' => $gradeData['resitStatus'],
            'course_credit' => $course->credit
        ];
    }
    private function updateAccessedStudent($accessedStudent)
    {
        if ($accessedStudent->grades_submitted === false || $accessedStudent->student_accessed === 'pending') {
            $accessedStudent->grades_submitted = true;
            $accessedStudent->student_accessed = 'accessed';
            $accessedStudent->save();
        }
    }
    private function validateStudentAndExam($student, $exam)
    {
        if (!$student || !$exam) {
            throw new Exception('Student or Exam not found', 404);
        }
    }
    private function prepareGradeData(array $scoreData, $schoolId)
    {
        $grade = $this->determineLetterGrade($scoreData['score'], $schoolId, $scoreData['exam_id']);
        return [
            'grade' => $grade['letterGrade'] ?? null,
            'grade_status' => $grade['gradeStatus'] ?? null,
            'gratification' => $grade['gratification'] ?? null,
            'grade_points' => $grade['gradePoints'] ?? null,
            'score' => $scoreData['score']
        ];
    }
    private function getStudent($schoolId, $studentId)
    {
        return Student::where('school_branch_id', $schoolId)->find($studentId);
    }
    private function determineLetterGrade($score, $schoolId, $examId)
    {
        $exam = Exams::findOrFail($examId);
        $grades = Grades::with('lettergrade')
            ->where('school_branch_id', $schoolId)
            ->where('grades_category_id', $exam->grades_category_id)
            ->orderBy('minimum_score', 'desc')
            ->get();

        if ($grades->isEmpty()) {
            throw new Exception("No grades found for school ID: {$schoolId} and exam ID: {$examId}");
        }

        foreach ($grades as $grade) {
            if ($score >= $grade->minimum_score && $score <= $grade->maximum_score) {
                return [
                    'letterGrade' => $grade->lettergrade->letter_grade ?? 'N/A',
                    'gradeStatus' => $grade->grade_status,
                    'gradePoints' => $grade->grade_points,
                    'gratification' => $grade->determinant,
                    'resitStatus' => $grade->resit_status,
                    'score' => $score
                ];
            }
        }

        return [
             'letterGrade' => 'F',
             'gradeStatus' => 'fail',
             'gratification' => 'poor',
             'score' => $score,
             'resitStatus' => 'high_resit_potential',
             'grade_points' => 0.0
         ];
    }
    private function isDuplicateEntry($schoolId, $scoreData, $student)
    {
        return Marks::where('school_branch_id', $schoolId)
            ->where('course_id', $scoreData['course_id'])
            ->where('exam_id', $scoreData['exam_id'])
            ->where('level_id', $student->level_id)
            ->where('specialty_id', $student->specialty_id)
            ->where('student_id', $student->id)
            ->exists();
    }
    private function validateCaMark($exam, $score)
    {
        if ($score > $exam->weighted_mark) {
            throw new Exception('Score exceeds maximum exam mark.', 400);
        }
    }
    private function calculateGpaAndTotalScore(array $results)
    {
        $totalWeightedPoints = 0;
        $totalCredits = 0;

        foreach ($results as $mark) {
            $credits = $mark->course_credit;
            $gradePoints = $mark->grade_points;
            $totalWeightedPoints += $gradePoints * $credits;
            $totalCredits += $credits;
        }
        $gpa = $totalCredits ? $totalWeightedPoints / $totalCredits : 0;

        return [
            'totalScore' => array_sum(array_column($results, 'score')),
            'gpa' => $gpa,
        ];
    }
    private function addStudentResultRecords($student, $currentSchool, $totalScoreAndGpa, $exam, $result){

       $studentResult = StudentResults::create([
            'student_id' => $student->id,
            'specialty_id' => $student->specialty_id,
            'student_batch_id' => $student->student_batch_id,
            'level_id' => $student->level_id,
            'exam_id' => $exam->id,
            'school_branch_id' => $currentSchool->id,
            'total_score' => $totalScoreAndGpa['totalScore'],
            'exam_status' => ($totalScoreAndGpa['gpa'] / 2) >= $currentSchool->max_gpa / 2 ? "Passed" : "Failed",
            'gpa' => $totalScoreAndGpa['gpa'],
            'score_details' => json_encode($result)
        ]);
        return $studentResult;
    }
}
