<?php

namespace App\Services;

use App\Models\AccessedStudent;
use App\Models\Exams;
use App\Models\Marks;
use App\Models\Grades;
use App\Models\Student;
use App\Models\Studentresit;
use App\Models\Examtype;
use App\Models\Resitablecourses;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AddScoreService
{
    // Implement your logic here
    public function addStudentScores(array $studentScores, $currentSchool)
    {
        $results = [];
        DB::beginTransaction();

        try {
            foreach ($studentScores as $scoreData) {
                $student = $this->getStudent($currentSchool->id, $scoreData['student_id']);
                $exam = Exams::find($scoreData['exam_id']);

                $this->validateStudentAndExam($student, $exam);

                if ($this->isDuplicateEntry($currentSchool->id, $scoreData, $student)) {
                    throw new Exception('Duplicate data entry for this student', 409);
                }

                $determinedExam = $this->determineExamType($scoreData['exam_id']);
                if (!$determinedExam) {
                    throw new Exception('Exam type not found', 400);
                }

                $scoreData['score'] = $this->handleExamTypes(
                    $scoreData,
                    $determinedExam,
                    $exam,
                    $student,
                    $currentSchool->id
                );

                $marks = $this->createMarks($scoreData, $student, $currentSchool->id, $exam);
                $results[] = $marks;
            }

            DB::commit();
            return $results;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function validateStudentAndExam($student, $exam)
    {
        if (!$student || !$exam) {
            throw new Exception('Student or Exam not found', 404);
        }
    }

    private function handleExamTypes(array $scoreData, $determinedExam, $exam, $student, $schoolId)
    {
        if ($determinedExam['ca']) {
            $this->validateCaMark($exam, $scoreData['score']);
            return $this->determineLetterGrade($scoreData['score'], $schoolId, $scoreData['exam_id']);
        }

        if ($determinedExam['exam']) {
            $totalScore = $this->calculateTotalScore($schoolId, $student, $scoreData, $exam);
            return $this->determineExamLetterGrade($totalScore, $schoolId, $student, $scoreData['exam_id'], $scoreData['course_id']);
        }

        throw new Exception('Exam type not found', 400);
    }

    private function createMarks(array $scoreData, $student, $schoolId, $exam)
    {
        $gradeData = $this->prepareGradeData($scoreData, $schoolId);
        $accessedStudent = AccessedStudent::findOrFail($scoreData['accessment_id']);
        if($accessedStudent->grades_submitted === false || $accessedStudent->student_accessed === 'pending'){
            $accessedStudent->grades_submitted = true;
            $accessedStudent->student_accessed = 'accessed';
            $accessedStudent->save();
        }
        return Marks::create([
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
            'gratification' => $gradeData['gratification'],
        ]);
    }

    private function prepareGradeData(array $scoreData, $schoolId)
    {
        $grade = $this->determineLetterGrade($scoreData['score'], $schoolId, $scoreData['exam_id']);
        return [
            'grade' => $grade['letterGrade'] ?? null,
            'grade_status' => $grade['gradeStatus'] ?? null,
            'gratification' => $grade['gratification'] ?? null,
            'score' => $scoreData['score']
        ];
    }

    private function getStudent($schoolId, $studentId)
    {
        return Student::where('school_branch_id', $schoolId)->find($studentId);
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

    private function calculateTotalScore($schoolId, $student, $scoreData, $exam)
    {
        $additionalExam = $this->findExamsBasedOnCriteria($scoreData['exam_id']);
        $caScore = $this->retrieveCaScore($schoolId, $student->id, $scoreData['course_id'], $additionalExam->id);

        $totalScore = $scoreData['score'] + $caScore->score;

        if ($totalScore > ($additionalExam->weighted_mark + $exam->weighted_mark)) {
            throw new Exception('Total score exceeds maximum allowed score.', 400);
        }

        return $totalScore;
    }

    private function retrieveCaScore($schoolId, $studentId, $courseId, $examId)
    {
        $caScore = Marks::where('school_branch_id', $schoolId)
            ->where('exam_id', $examId)
            ->where('student_id', $studentId)
            ->where('course_id', $courseId)
            ->first();

        if (!$caScore) {
            throw new Exception('CA mark not found for this course', 400);
        }

        return $caScore;
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
                    'gratification' => $grade->determinant,
                    'score' => $score
                ];
            }
        }

        return ['letterGrade' => 'F', 'gradeStatus' => 'fail', 'gratification' => 'poor', 'score' => $score];
    }

    private function determineExamType(string $examId)
    {
        $exam = Exams::with('examType')->find($examId);
        if (!$exam) return false;

        $examType = $exam->examType;
        if (!$examType) return false;

        return [
            'exam' => $examType->type === 'exam',
            'ca' => $examType->type === 'ca',
            'resit' => $examType->type === 'resit',
        ];
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
            ->orderBy('minimum_score', 'asc')
            ->get();

        if ($grades->isEmpty()) {
            throw new Exception("No grades found for school ID: {$schoolId} and exam ID: {$examId}");
        }

        foreach ($grades as $grade) {
            if ($score >= $grade->minimum_score && $score <= $grade->maximum_score) {
                if ($grade->grade_status === 'resit') {
                    $this->createResitableCourse($courseId, $examId, $student, $schoolId);
                }

                return [
                    'letterGrade' => $grade->lettergrade->letter_grade,
                    'gradeStatus' => $grade->grade_status,
                    'gratification' => $grade->determinant
                ];
            }
        }

        return ['letterGrade' => 'F', 'gradeStatus' => 'fail', 'gratification' => 'poor'];
    }

    public function createResitableCourse($courseId, $examId, $student, $schoolId)
    {
        $resitCourse = Resitablecourses::where("school_branch_id", $schoolId)
            ->where("specialty_id", $student->specialty_id)
            ->where("course_id", $courseId)
            ->where("student_batch_id", $student->student_batch_id)
            ->exists();
        if (!$resitCourse) {
            Resitablecourses::create([
                'school_branch_id' => $schoolId,
                'specialty_id' => $student->specialty_id,
                'course_id' => $courseId,
                'exam_id' => $examId,
                'student_batch_id' => $student->student_batch_id,
                'level_id' => $student->level_id
            ]);
            Studentresit::create([
                'school_branch_id' => $schoolId,
                'student_id' => $student->id,
                'course_id' => $courseId,
                'exam_id' => $examId,
                'student_batch_id' => $student->student_batch_id,
                'level_id' => $student->level_id,
            ]);
        } else {
            Studentresit::create([
                'school_branch_id' => $schoolId,
                'student_id' => $student->id,
                'course_id' => $courseId,
                'exam_id' => $examId,
                'specialty_id' => $student->specialty_id,
                'student_batch_id' => $student->student_batch_id,
                'level_id' => $student->level_id
            ]);
        }
    }

    public function determindStudentGpa(){

    }

    public function addStudentResults() {

    }
}
