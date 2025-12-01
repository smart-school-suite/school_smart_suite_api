<?php

namespace App\Services\CAEvaluation;

use App\Models\Courses;
use Exception;
use App\Models\Marks;
use Illuminate\Support\Facades\DB;
use App\Models\Grades;
use App\Models\Exams;
use App\Models\Student;
use App\Models\StudentResults;
use App\Events\Actions\AdminActionEvent;
use App\Events\Actions\StudentActionEvent;

class UpdateCaScoreService
{
    public function updateCaScore(array $updateData, $currentSchool, $authAdmin): array
    {
        $results = [];
        $exam = null;
        $student = null;

        DB::beginTransaction();
        try {
            foreach ($updateData as $data) {
                $score = Marks::where("school_branch_id", $currentSchool->id)->findOrFail($data['mark_id']);

                if (!$exam) {
                    $exam = Exams::findOrFail($score->exam_id);
                }
                if (!$student) {
                    $student = Student::findOrFail($score->student_id);
                }

                $course = Courses::findOrFail($data['course_id']);

                $letterGrades = $this->determineLetterGrade($data['score'], $exam->id, $currentSchool->id);

                $updatedScore = $this->updateMarkRecord($letterGrades, $score, $course);

                $results[] = $updatedScore;
            }

            $this->getAllStudentScoresForExam(
                $student,
                $exam,
                $currentSchool,
                $results
            );

            $totalScoreAndGpa = $this->recalculateGpa($results);

            $examStatus = $this->determineExamResultStatus($results);

            $this->updateStudentResultsRecord(
                $student,
                $currentSchool,
                $totalScoreAndGpa,
                $exam,
                $results,
                $examStatus
            );

            DB::commit();
            AdminActionEvent::dispatch(
                [
                    "permissions" =>  ["schoolAdmin.examEvaluation.updateScore"],
                    "roles" => ["schoolSuperAdmin", "schoolAdmin"],
                    "schoolBranch" =>  $currentSchool->id,
                    "feature" => "examEvaluation",
                    "authAdmin" => $authAdmin,
                    "data" => $results,
                    "message" => "Student CA Results Updated",
                ]
            );
            StudentActionEvent::dispatch([
                'schoolBranch' => $currentSchool->id,
                'studentIds'   => [$student->id],
                'feature'      => 'examResultUpdate',
                'message'      => 'Exam Result Updated',
                'data'         => $results,
            ]);
            return $results;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    private function getAllStudentScoresForExam(Student $student, Exams $exam, $currentSchool, array &$results)
    {
        $marks = Marks::with('course')
            ->where('school_branch_id', $currentSchool->id)
            ->where('student_id', $student->id)
            ->where('student_batch_id', $student->student_batch_id)
            ->where('exam_id', $exam->id)
            ->where('specialty_id', $student->specialty_id)
            ->where('level_id', $student->level_id)
            ->get();
        foreach ($marks as $mark) {
            $courseId = $mark->course->id;
            $courseAlreadyExists = false;

            foreach ($results as $result) {
                if ($result['course_id'] === $courseId) {
                    $courseAlreadyExists = true;
                    break;
                }
            }

            if (!$courseAlreadyExists) {
                $results[] = [
                    'course_id' => $courseId,
                    'course_name' => $mark->course->course_title,
                    'course_code' => $mark->course->course_code,
                    'score' => $mark->score,
                    'grade' => $mark->grade,
                    'grade_status' => $mark->grade_status,
                    'gratification' => $mark->gratification,
                    'grade_points' => $mark->grade_points,
                    'resit_status' => $mark->resit_status,
                    'course_credit' => $mark->course->credit,
                ];
            }
        }
    }
    private function determineLetterGrade(float $score, string $examId, string $schoolId): array
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
                    'resitStatus' => $grade->resit_status,
                    'gratification' => $grade->determinant,
                    'score' => $score,
                ];
            }
        }

        return [
            'letterGrade' => 'F',
            'gradeStatus' => 'fail',
            'gratification' => 'poor',
            'score' => $score,
            'resitStatus' => 'high_resit_potential',
            'gradePoints' => 0.0,
        ];
    }

    private function updateMarkRecord(array $updateScoreData, object $score, object $course): array
    {
        $score->score = $updateScoreData['score'];
        $score->grade_status = $updateScoreData['gradeStatus'];
        $score->gratification = $updateScoreData['gratification'];
        $score->grade = $updateScoreData['letterGrade'];
        $score->grade_points = $updateScoreData['gradePoints'];
        $score->resit_status = $updateScoreData['resitStatus'];
        $score->save();

        return [
            'course_id' => $course->id,
            'course_name' => $course->course_title,
            'course_code' => $course->course_code,
            'score' => $updateScoreData['score'],
            'grade' => $updateScoreData['letterGrade'],
            'grade_status' => $updateScoreData['gradeStatus'],
            'gratification' => $updateScoreData['gratification'],
            'grade_points' => $updateScoreData['gradePoints'],
            'resit_status' => $updateScoreData['resitStatus'],
            'course_credit' => $course->credit,
        ];
    }

    private function recalculateGpa(array $results): array
    {
        $totalWeightedPoints = 0;
        $totalCredits = 0;
        $overallTotalScore = 0;

        foreach ($results as $mark) {
            $credits = $mark['course_credit'];
            $gradePoints = $mark['grade_points'];
            $overallTotalScore += $mark['score'];
            $totalWeightedPoints += $gradePoints * $credits;
            $totalCredits += $credits;
        }

        $gpa = $totalCredits > 0 ? round($totalWeightedPoints / $totalCredits, 2) : 0.00;

        return [
            'totalScore' => round($overallTotalScore, 2),
            'gpa' => $gpa,
        ];
    }

    private function updateStudentResultsRecord(Student $student, $currentSchool, array $totalScoreAndGpa, Exams $exam, array $results, array $examStatus): StudentResults
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
        $studentResult->exam_status = $examStatus['passed'] ? 'passed' : 'failed';
        $studentResult->score_details = json_encode($results);
        $studentResult->save();

        return $studentResult;
    }

    private function determineExamResultStatus(array $results): array
    {
        $failedCourses = array_filter($results, function ($result) {
            return ($result['grade_status'] ?? '') === 'failed';
        });

        if (empty($failedCourses)) {
            return [
                'exam_status' => 'Passed',
                'passed' => true,
                'failed' => false,
            ];
        }

        return [
            'exam_status' => 'Failed',
            'passed' => false,
            'failed' => true,
        ];
    }
}
