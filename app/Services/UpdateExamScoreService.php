<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Exception;
use App\Models\Exams;
use App\Models\Marks;
use App\Models\Grades;
use App\Models\Student;
use App\Models\Studentresit;
use App\Models\StudentResults;
use App\Models\Resitablecourses;
use App\Models\Courses;
use App\Models\Examtype;

class UpdateExamScoreService
{
    public function updateExamScore(array $updateData, $currentSchool)
    {
        $results = [];
        $exam = null;
        $student = null;
        try {
            DB::beginTransaction();
            foreach ($updateData as $data) {
                $score = Marks::where("school_branch_id", $currentSchool->id)->findOrFail($data['mark_id']);
                if ($exam === null) {
                    $exam = Exams::findOrFail($score->exam_id);
                }
                if ($student === null) {
                    $student = Student::findOrFail($score->student_id);
                }
                $course = Courses::findOrFail($score->course_id);
                $totalScore = $this->calculateTotalScore(
                    $currentSchool->id,
                    $student,
                    $data,
                    $exam,
                    $course
                );
                $letterGrade = $this->determineExamLetterGrade(
                    $totalScore,
                    $currentSchool->id,
                    $student,
                    $exam->id,
                    $course->id
                );
                $updatedScore = $this->updateNewScore($letterGrade, $score, $course);
                $results = $updatedScore;
            }
            $this->getStudentScores($student, $exam, $currentSchool, $results);

            $totalScoreAndGpa = $this->recalculateGpa($results);

            $this->updateStudentResults($student, $currentSchool, $totalScoreAndGpa, $exam, $results);
            DB::commit();
            return $results;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    private function getStudentScores($student, $exam, $currentSchool, $results)
    {
        $marks = Marks::where("school_branch_id", $currentSchool->id)
            ->where("student_id", $student->id)
            ->where("student_batch_id", $student->student_batch_id)
            ->where("exam_id", $exam->id)
            ->where("specialty_id", $student->specialty_id)
            ->where("level_id", $student->level_id)
            ->with(['course'])
            ->get();
        foreach ($marks as $score) {
            foreach ($results as $result) {
                if ($score->course_id !== $result['course_id']) {
                    $results[] = [
                        'course_id' => $score->course_id,
                        'course_name' => $score->course->name,
                        'course_code' => $score->course->code,
                        'score' => $score->score,
                        'grade' => $score->grade,
                        'grade_status' => $score->grade_status,
                        'resit_status' => $score->resit_status,
                        'gratification' => $score->gratification,
                        'grade_points' => $score->grade_points,
                        'course_credit' => $score->course->credit,
                    ];
                }
            }
        }
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
    private function calculateTotalScore($schoolId, $student, $scoreData, $exam, $course)
    {
        $additionalExam = $this->findExamsBasedOnCriteria($exam->id);
        $caScore = $this->retrieveCaScore($schoolId, $student, $course->id, $additionalExam->id);

        $totalScore = $scoreData['new_score'] + $caScore->score;

        if ($totalScore > ($additionalExam->weighted_mark + $exam->weighted_mark)) {
            throw new Exception('Total score exceeds maximum allowed score.', 400);
        }

        return $totalScore;
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
                    'resitStatus' => $grade->resit_Status,
                    'score' => $grade->score,

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
    private function updateNewScore(array $updateScoreData, $score, $course)
    {
        $score->score = $updateScoreData['score'];
        $score->grade_status = $updateScoreData['gradeStatus'];
        $score->gratification = $updateScoreData['gratification'];
        $score->grade = $updateScoreData['letterGrade'];
        $score->resit_status = $updateScoreData['resitStatus'];
        $score->grade_points = $updateScoreData['gradePoints'];
        $score->save();

        return [
            'course_id' => $course->id,
            'course_name' => $course->name,
            'course_code' => $course->code,
            'score' => $updateScoreData['score'],
            'grade' => $updateScoreData['letterGrade'],
            'grade_status' => $updateScoreData['gradeStatus'],
            'gratification' => $updateScoreData['gratification'],
            'grade_points' => $updateScoreData['gradePoints'],
            'resit_status' => $updateScoreData['resitStatus'],
            'course_credit' => $course->credit
        ];
    }
    private function recalculateGpa(array $results)
    {
        $totalWeightedPoints = 0;
        $totalCredits = 0;

        foreach ($results as $mark) {
            $credits = $mark['course_credit'];
            $gradePoints = $mark['grade_points']; // Corrected key to match results
            $totalWeightedPoints += $gradePoints * $credits;
            $totalCredits += $credits;
        }

        $gpa = $totalCredits ? $totalWeightedPoints / $totalCredits : 0;

        return [
            'totalScore' => array_sum(array_column($results, 'score')),
            'gpa' => $gpa,
        ];
    }
    private function updateStudentResults($student, $currentSchool, $totalScoreAndGpa, $exam, $results)
    {
        $studentResult = StudentResults::where("school_branch_id", $currentSchool->id)
            ->where("student_id", $student->id)
            ->where("exam_id", $exam->id)
            ->where("specialty_id", $student->specialty_id)
            ->where("level_id", $student->level_id)
            ->where("student_batch_id", $student->student_batch_id)
            ->firstOrFail();

        $studentResult->gpa = $totalScoreAndGpa['gpa'];
        $studentResult->total_score = $totalScoreAndGpa['totalScore'];
        $studentResult->score_details = json_encode($results);
        $studentResult->save();

        return $studentResult;
    }
}
