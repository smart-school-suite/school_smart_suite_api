<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use App\Models\StudentResults;
use App\Models\Resitablecourses;
use App\Models\Exams;
use App\Models\Grades;
use App\Models\Student;
use App\Models\Marks;
use App\Models\Examtype;
use App\Models\Courses;
use App\Models\AccessedStudent;
use App\Jobs\CreateResitExamJob;
use App\Models\Schoolbranches;
use App\Models\Studentresit;

class AddExamScoresService
{
    // Implement your logic here
    public function addExamScores(array $studentScores, $currentSchool)
    {
        $result = [];
        $examDetails = null;
        try {
            DB::beginTransaction();
            foreach ($studentScores as $scoreData) {
                $student = $this->getStudent($currentSchool->id, $scoreData['student_id']);
                $exam = Exams::with(['examtype'])->find($scoreData['exam_id']);
                $examDetails = $exam;
                $this->validateStudentAndExam($student, $exam);
                if ($this->isDuplicateEntry($currentSchool->id, $scoreData, $student)) {
                    throw new Exception('Duplicate data entry for this student', 409);
                }
                $totalScore = $this->calculateTotalScore($currentSchool->id, $student, $scoreData, $exam);
                $determineGrade = $this->determineExamLetterGrade(
                    $totalScore,
                    $currentSchool->id,
                    $student,
                    $exam->id,
                    $scoreData['course_id']
                );
                $marks = $this->createMarks(
                    $determineGrade,
                    $totalScore,
                    $student,
                    $currentSchool->id,
                    $exam,
                    $scoreData['course_id'],
                );
                $accessedStudent = AccessedStudent::findOrFail($scoreData['accessment_id']);
                $this->updateAccessedStudent($accessedStudent);
                $result[] = $marks;
            }
            $totalScoreAndGpa = $this->calculateGpaAndTotalScore($result);
            $this->addStudentResultRecords($student, $currentSchool, $totalScoreAndGpa, $examDetails, $result);
            $this->updateEvaluatedStudentCount($examDetails);
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    private function createMarks(array $gradeData, $score, $student, $schoolId, $exam, $courseId)
    {
        $course = Courses::find($courseId);
        Marks::create([
            'student_batch_id' => $student->student_batch_id,
            'course_id' => $courseId,
            'student_id' => $student->id,
            'exam_id' => $exam->id,
            'level_id' => $student->level_id,
            'score' => $score,
            'specialty_id' => $student->specialty_id,
            'school_branch_id' => $schoolId,
            'grade' => $gradeData['grade'],
            'grade_status' => $gradeData['grade_status'],
            'grade_points' => $gradeData['grade_points'],
            'resit_status' => $gradeData['resitStatus'],
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
    private function updateEvaluatedStudentCount($exam)
    {
        $exam->increment('evaluated_candidate_number');
        $exam->refresh();
        if ($exam->evaluated_candidate_number == $exam->expected_candidate_number) {
            dispatch(new CreateResitExamJob($exam));
        }
    }
    private function getStudent($schoolId, $studentId)
    {
        return Student::where('school_branch_id', $schoolId)->find($studentId);
    }
    private function validateStudentAndExam($student, $exam)
    {
        if (!$student || !$exam) {
            throw new Exception('Student or Exam not found', 404);
        }
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
    private function retrieveCaScore($schoolId, $student, $courseId, $examId)
    {
        $caScore = Marks::where('school_branch_id', $schoolId)
            ->where('exam_id', $examId)
            ->where('student_id', $student->id)
            ->where('course_id', $courseId)
            ->where('specialty_id', $student->specialty_id)
            ->where('level_id', $student->level_id)
            ->where('student_batch_id', $student->student_batch_id)
            ->first();

        if (!$caScore) {
            throw new Exception('CA mark not found for this course', 400);
        }

        return $caScore;
    }
    private function calculateTotalScore($schoolId, $student, $scoreData, $exam)
    {
        $additionalExam = $this->findExamsBasedOnCriteria($scoreData['exam_id']);
        $caScore = $this->retrieveCaScore($schoolId, $student, $scoreData['course_id'], $additionalExam->id);

        $totalScore = $scoreData['score'] + $caScore->score;

        if ($totalScore > ($additionalExam->weighted_mark + $exam->weighted_mark)) {
            throw new Exception('Total score exceeds maximum allowed score.', 400);
        }

        return $totalScore;
    }
    public function findExamsBasedOnCriteria(string $examId)
    {
        $exam = Exams::with('examType')->findOrFail($examId);
        if ($exam->examType->type !== 'exam') {
            throw new Exception('Exam type is not valid or not found');
        }

        $caExamType = ExamType::where('semester', $exam->examType->semester)
            ->where('type', 'ca')
            ->first();

        if (!$caExamType) {
            throw new Exception('Corresponding CA exam type not found');
        }

        $additionalExam = Exams::where('school_year', $exam->school_year)
            ->where('exam_type_id', $caExamType->id)
            ->where('specialty_id', $exam->specialty_id)
            ->where('level_id', $exam->level_id)
            ->where('semester_id', $exam->semester_id)
            ->where('department_id', $exam->department_id)
            ->first();

        if (!$additionalExam) {
            throw new Exception('No additional exam found');
        }

        return $additionalExam;
    }
    private function determineExamLetterGrade($score, $schoolId, $student, $examId, $courseId)
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
                if ($grade->resit_status === 'resit') {
                    $this->createResitableCourse($courseId, $examId, $student, $schoolId);
                }

                return [
                    'letterGrade' => $grade->lettergrade->letter_grade,
                    'gradeStatus' => $grade->grade_status,
                    'gratification' => $grade->determinant,
                    'gradePoints' => $grade->grade_points,
                    'resitStatus' => $grade->resit_status,
                    'score' => $score,
                ];
            }
        }

        return [
            'letterGrade' => 'F',
            'gradeStatus' => 'fail',
            'gratification' => 'poor',
            'gradePoints' => 0.0,
            'resitStatus' => 'resit',
            'score' => $score
        ];
    }
    public function createResitableCourse($courseId, $examId, $student, $schoolId)
    {
        $studentResit = Studentresit::where("student_id", $student->id)
            ->where("level_id", $student->level_id)
            ->where("specialty_id", $student->specialty_id)
            ->where("course_id", $courseId)
            ->exists();
        if (!$studentResit) {
            Studentresit::create([
                'school_branch_id' => $schoolId,
                'student_id' => $student->id,
                'course_id' => $courseId,
                'exam_id' => $examId,
                'student_batch_id' => $student->student_batch_id,
                'level_id' => $student->level_id,

            ]);
        }
    }
    private function updateAccessedStudent($accessedStudent)
    {
        if ($accessedStudent->grades_submitted === false || $accessedStudent->student_accessed === 'pending') {
            $accessedStudent->grades_submitted = true;
            $accessedStudent->student_accessed = 'accessed';
            $accessedStudent->save();
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
    private function addStudentResultRecords($student, $currentSchool, $totalScoreAndGpa, $exam, $result)
    {

        $studentResult = StudentResults::create([
            'student_id' => $student->id,
            'specialty_id' => $student->specialty_id,
            'student_batch_id' => $student->student_batch_id,
            'level_id' => $student->level_id,
            'exam_id' => $exam->id,
            'school_branch_id' => $currentSchool->id,
            'total_score' => $totalScoreAndGpa['totalScore'],
            'gpa' => $totalScoreAndGpa['gpa'],
            'exam_status' => ($totalScoreAndGpa['gpa'] / 2) >= $currentSchool->max_gpa / 2 ? "Passed" : "Failed",
            'score_details' => json_encode($result)
        ]);
        return $studentResult;
    }
}
