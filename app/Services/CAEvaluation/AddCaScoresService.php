<?php

namespace App\Services\CAEvaluation;

use App\Jobs\NotificationJobs\SendExamResultsReleasedNotificationJob;
use App\Jobs\StatisticalJobs\AcademicJobs\StudentCaStatsJob;
use App\Jobs\StatisticalJobs\AcademicJobs\CaStatsJob;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Models\Exams;
use App\Models\Student;
use App\Models\AccessedStudent;
use App\Models\Courses;
use App\Models\Marks;
use App\Models\Grades;
use App\Models\StudentResults;
use Illuminate\Support\Collection;
use App\Exceptions\AppException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AddCaScoresService
{
    public function addCaScore(array $studentScores, $currentSchool): array
    {
        $results = [];
        $examDetails = null;
        $studentTarget = null;
        $allStudentsEvaluated = false;

        try {
            DB::beginTransaction();
            foreach ($studentScores as $scoreData) {

                $accessedStudent = AccessedStudent::findOrFail($scoreData['accessment_id']);
                if($accessedStudent->grades_submitted === true) {
                    throw new AppException(
                        "The CA Exam Candidate Already Accessed.",
                        400,
                        "CA Exam  Candidate Already Accessed",
                        "The CA Exam Candidate Has Already Been Accessed You Can Not Submit Scores Again",
                        "/accessed-students"
                    );
                }
                $student = $this->getStudent($currentSchool->id, $scoreData['student_id']);
                $studentTarget = $student;

                $exam = Exams::with(['student'])->findOrFail($scoreData['exam_id']);
                $examDetails = $exam;

                $this->validateStudentAndExam($student, $exam);

                if ($this->isDuplicateEntry($currentSchool->id, $scoreData, $student)) {
                    throw new AppException(
                        'Duplicate score entry.',
                        409,
                        'Conflict',
                        'A score for this student, course, and exam has already been entered. Cannot submit a duplicate entry.',
                        '/scores/ca/duplicate-entry'
                    );
                }

                $this->validateCaMark($exam, $scoreData['score']);
                $marks = $this->createCaMarks($scoreData, $student, $currentSchool->id, $exam);
                $this->updateAccessedStudent($accessedStudent);
                $results[] = $marks;
            }

            $totalScoreAndGpa = $this->calculateGpaAndTotalScore(collect($results));
            $examStatus = $this->determineExamResultsStatus(collect($results));

            $this->addStudentResultRecords($studentTarget, $currentSchool, $totalScoreAndGpa, $examDetails, $results, $examStatus);
            $allStudentsEvaluated = $this->updateEvaluatedStudentCount($examDetails);

            DB::commit();

            StudentCaStatsJob::dispatch($examDetails, $studentTarget);
            if ($allStudentsEvaluated) {
                CaStatsJob::dispatch($examDetails);
                $this->sendExamResultsNotification($examDetails);
            }

            return $results;
        } catch (AppException | ModelNotFoundException $e) {
            DB::rollBack();
            throw $e;
        } catch (Exception $e) {
            DB::rollBack();
            throw new AppException(
                'An unexpected error occurred while adding CA scores. Please try again later.',
                500,
                'Server Error',
                'We encountered an unexpected issue while saving the scores. This has been logged for investigation.',
                null
            );
        }
    }

    private function getStudent(string $schoolId, string $studentId): Student
    {
        try {
            return Student::where('school_branch_id', $schoolId)->findOrFail($studentId);
        } catch (ModelNotFoundException $e) {
            throw new AppException(
                "Student with ID {$studentId} not found.",
                404,
                "Student Not Found",
                "The specified student could not be found within the school branch. Please verify the student has not been deleted",
                "/students"
            );
        }
    }

    private function validateStudentAndExam(?object $student, ?object $exam): void
    {
        if (!$student || !$exam) {
            throw new AppException(
                'Student or Exam not found.',
                404,
                'Record Not Found',
                'The student or exam record could not be found. Please check the provided IDs.',
                '/exams'
            );
        }
    }

    private function isDuplicateEntry(string $schoolId, array $scoreData, Student $student): bool
    {
        return Marks::where('school_branch_id', $schoolId)
            ->where('courses_id', $scoreData['course_id'])
            ->where('exam_id', $scoreData['exam_id'])
            ->where('level_id', $student->level_id)
            ->where('specialty_id', $student->specialty_id)
            ->where('student_id', $student->id)
            ->exists();
    }

    private function validateCaMark(object $exam, float $score): void
    {
        if ($score > $exam->weighted_mark) {
            throw new AppException(
                'Score exceeds maximum exam mark.',
                400,
                'Validation Error',
                "The entered score of $score exceeds the maximum allowed mark of  $exam->weighted_mark for this exam.'",
                "/exam"
            );
        }
    }

    private function createCaMarks(array $scoreData, Student $student, string $schoolId, object $exam): array
    {
        try {
            $gradeData = $this->determineCaLetterGrade($scoreData['score'], $schoolId, $exam->id);
            $course = Courses::findOrFail($scoreData['course_id']);
            Marks::create([
                'student_batch_id' => $student->student_batch_id,
                'courses_id' => $course->id,
                'student_id' => $student->id,
                'exam_id' => $exam->id,
                'level_id' => $student->level_id,
                'score' => $scoreData['score'],
                'specialty_id' => $student->specialty_id,
                'school_branch_id' => $schoolId,
                'grade' => $gradeData['letterGrade'] ?? null,
                'grade_status' => $gradeData['gradeStatus'] ?? null,
                'resit_status' => $gradeData['resitStatus'] ?? null,
                'grade_points' => $gradeData['gradePoints'] ?? null,
                'gratification' => $gradeData['gratification'] ?? null,
            ]);

            return [
                'course_id' => $course->id,
                'course_name' => $course->course_title,
                'course_code' => $course->course_code,
                'score' => $scoreData['score'],
                'grade' => $gradeData['letterGrade'] ?? 'N/A',
                'grade_status' => $gradeData['gradeStatus'] ?? 'N/A',
                'gratification' => $gradeData['gratification'] ?? 'N/A',
                'grade_points' => $gradeData['gradePoints'] ?? 0.0,
                'resit_status' => $gradeData['resitStatus'] ?? 'N/A',
                'course_credit' => $course->credit,
            ];
        } catch (ModelNotFoundException $e) {
            throw new AppException(
                "Course  not found during score creation.",
                404,
                "Course Not Found",
                "The specified course could not be found. Please check the course has not been deleted",
                "/courses"
            );
        }
    }

    private function determineCaLetterGrade(float $score, string $schoolId, string $examId): array
    {
        $exam = Exams::findOrFail($examId);
        $grades = Grades::with('lettergrade')
            ->where('school_branch_id', $schoolId)
            ->where('grades_category_id', $exam->grades_category_id)
            ->orderBy('minimum_score', 'desc')
            ->get();

        if ($grades->isEmpty()) {
            throw new AppException(
                "Grading scale not configured.",
                500,
                "Grading scale not configured",
                "No grading scale was found for this school and exam category. Please Configure Grade scales",
                "/settings/grades"
            );
        }

        foreach ($grades as $grade) {
            if ($score >= $grade->minimum_score && $score <= $grade->maximum_score) {
                return [
                    'letterGrade' => $grade->lettergrade->letter_grade ?? 'N/A',
                    'gradeStatus' => $grade->grade_status,
                    'gradePoints' => $grade->grade_points,
                    'gratification' => $grade->determinant,
                    'resitStatus' => $grade->resit_status,
                    'score' => $score,
                ];
            }
        }

        return [
            'letterGrade' => 'F',
            'gradeStatus' => 'failed',
            'gratification' => 'poor',
            'score' => $score,
            'resitStatus' => 'high_resit_potential',
            'gradePoints' => 0.0,
        ];
    }

    private function updateAccessedStudent(object $accessedStudent): void
    {
        if (!$accessedStudent->grades_submitted || $accessedStudent->student_accessed === 'pending') {
            $accessedStudent->grades_submitted = true;
            $accessedStudent->student_accessed = 'accessed';
            $accessedStudent->save();
        }
    }

    private function calculateGpaAndTotalScore(Collection $marks): array
    {
        $totalWeightedPoints = 0;
        $totalCredits = 0;
        $overallTotalScore = 0;

        foreach ($marks as $mark) {
            $credits = $mark['course_credit'] ?? ($mark->course->credit ?? 0);
            $gradePoints = $mark['grade_points'] ?? 0;
            $score = $mark['score'] ?? 0;

            $totalWeightedPoints += $gradePoints * $credits;
            $totalCredits += $credits;
            $overallTotalScore += $score;
        }

        $gpa = $totalCredits > 0 ? round($totalWeightedPoints / $totalCredits, 2) : 0.00;

        return [
            'totalScore' => round($overallTotalScore, 2),
            'gpa' => $gpa,
        ];
    }

    private function addStudentResultRecords(Student $student, $currentSchool, array $totalScoreAndGpa, object $exam, array $result, array $examStatus): StudentResults
    {
        return StudentResults::create([
            'student_id' => $student->id,
            'specialty_id' => $student->specialty_id,
            'student_batch_id' => $student->student_batch_id,
            'level_id' => $student->level_id,
            'exam_id' => $exam->id,
            'school_branch_id' => $currentSchool->id,
            'total_score' => $totalScoreAndGpa['totalScore'],
            'gpa' => $totalScoreAndGpa['gpa'],
            'exam_status' => $examStatus['passed'] ? 'Passed' : 'Failed',
            'score_details' => json_encode($result),
        ]);
    }

    private function updateEvaluatedStudentCount($exam): bool
    {
        $exam->increment('evaluated_candidate_number');
        $exam->refresh();
        return $exam->evaluated_candidate_number >= $exam->expected_candidate_number;
    }

    private function determineExamResultsStatus(Collection $marks): array
    {
        $failedCourses = $marks->filter(fn ($mark) => ($mark['grade_status'] ?? $mark->grade_status ?? '') === 'failed');

        if ($failedCourses->isEmpty()) {
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

    private function sendExamResultsNotification(Exams $exam)
    {
        $examDetails = Exams::with('specialty', 'level', 'examtype')->find($exam->id);
        $examCandidates = AccessedStudent::where('exam_id', $exam->id)->with('student')->get();
        SendExamResultsReleasedNotificationJob::dispatch($examCandidates, $examDetails);
    }
}
