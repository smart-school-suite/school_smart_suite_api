<?php

namespace App\Services;

use App\Jobs\DataCreationJob\CreateResitExamJob;
use App\Jobs\NotificationJobs\SendExamResultsReleasedNotificationJob;
use App\Jobs\StatisticalJobs\AcademicJobs\ExamStatsJob;
use App\Jobs\StatisticalJobs\AcademicJobs\StudentExamStatsJob;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Models\StudentResults;
use App\Models\Exams;
use App\Models\Grades;
use App\Models\Student;
use App\Models\Marks;
use App\Models\Examtype;
use App\Models\Courses;
use App\Models\AccessedStudent;
use App\Models\SchoolBranchSetting;
use App\Models\Studentresit;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class AddExamScoresService
{
    public function addExamScores(array $studentScores, $currentSchool): array
    {
        $results = [];
        $examDetails = null;
        $targetStudent = null;
        $allStudentsEvaluated = false;
        DB::beginTransaction();
        try {
            foreach ($studentScores as $scoreData) {
                $student = $this->getStudent($currentSchool->id, $scoreData['student_id']);
                $targetStudent = $student;
                $exam = Exams::with('examtype')->findOrFail($scoreData['exam_id']);
                $examDetails = $exam;

                $this->validateStudentAndExam($student, $exam);

                if ($this->isDuplicateEntry($currentSchool->id, $scoreData, $student)) {
                    throw new Exception('Duplicate data entry for this student', 409);
                }

                $totalScore = $this->calculateTotalScore($currentSchool->id, $student, $scoreData, $exam);
                $gradeData = $this->determineExamLetterGrade(
                    $totalScore,
                    $currentSchool->id,
                    $student,
                    $exam->id,
                    $scoreData['course_id']
                );

                $marks = $this->createMarks(
                    $gradeData,
                    $totalScore,
                    $student,
                    $currentSchool->id,
                    $exam,
                    $scoreData['course_id']
                );

                $accessedStudent = AccessedStudent::findOrFail($scoreData['accessment_id']);
                $this->updateAccessedStudent($accessedStudent);

                $results[] = $marks;
            }

            $totalScoreAndGpa = $this->calculateGpaAndTotalScore(collect($results));
            $examStatus = $this->determineExamResultsStatus(collect($results));

            $this->addStudentResultRecords(
                $student,
                $currentSchool,
                $totalScoreAndGpa,
                $examDetails,
                $results,
                $examStatus
            );

            $allStudentsEvaluated = $this->updateEvaluatedStudentCount($examDetails);
            DB::commit();
            StudentExamStatsJob::dispatch($examDetails, $targetStudent);
            if ($allStudentsEvaluated) {
                CreateResitExamJob::dispatch($examDetails);
                ExamStatsJob::dispatch($examDetails);
                $this->sendExamResultsNotification($examDetails);
            }
            return $results;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    private function createMarks(array $gradeData, float $score, Student $student, string $schoolId, object $exam, string $courseId): array
    {
        $course = Courses::findOrFail($courseId);
        Marks::create([
            'student_batch_id' => $student->student_batch_id,
            'courses_id' => $courseId,
            'student_id' => $student->id,
            'exam_id' => $exam->id,
            'level_id' => $student->level_id,
            'score' => $score,
            'specialty_id' => $student->specialty_id,
            'school_branch_id' => $schoolId,
            'grade' => $gradeData['letterGrade'],
            'grade_status' => $gradeData['gradeStatus'],
            'grade_points' => $gradeData['gradePoints'],
            'resit_status' => $gradeData['resitStatus'],
            'gratification' => $gradeData['gratification'],
        ]);

        return [
            'course_id' => $course->id,
            'course_name' => $course->course_title,
            'course_code' => $course->course_code,
            'score' => $gradeData['score'],
            'grade' => $gradeData['letterGrade'],
            'grade_status' => $gradeData['gradeStatus'],
            'gratification' => $gradeData['gratification'],
            'grade_points' => $gradeData['gradePoints'],
            'resit_status' => $gradeData['resitStatus'],
            'course_credit' => $course->credit,
        ];
    }
    private function updateEvaluatedStudentCount(Exams $exam): bool
    {
        $exam->increment('evaluated_candidate_number');
        $exam->refresh();
        return $exam->evaluated_candidate_number >= $exam->expected_candidate_number;
    }

    private function getStudent(string $schoolId, string $studentId): Student
    {
        return Student::where('school_branch_id', $schoolId)->findOrFail($studentId);
    }

    private function validateStudentAndExam(?object $student, ?object $exam): void
    {
        if (!$student || !$exam) {
            throw new Exception('Student or Exam not found', 404);
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

    private function retrieveCaScore(string $schoolId, Student $student, string $courseId, string $examId): Marks
    {
        $caScore = Marks::where('school_branch_id', $schoolId)
            ->where('exam_id', $examId)
            ->where('student_id', $student->id)
            ->where('courses_id', $courseId)
            ->where('specialty_id', $student->specialty_id)
            ->where('level_id', $student->level_id)
            ->where('student_batch_id', $student->student_batch_id)
            ->first();

        if (!$caScore) {
            throw new Exception('CA mark not found for this course', 400);
        }

        return $caScore;
    }

    private function calculateTotalScore(string $schoolId, Student $student, array $scoreData, object $exam): float
    {
        $additionalExam = $this->findCaExamForCurrentExam($scoreData['exam_id']);
        $caScore = $this->retrieveCaScore($schoolId, $student, $scoreData['course_id'], $additionalExam->id);
        $totalScore = $scoreData['score'] + $caScore->score;

        if ($totalScore > ($additionalExam->weighted_mark + $exam->weighted_mark)) {
            throw new Exception('Total score exceeds maximum allowed score.', 400);
        }

        return $totalScore;
    }

    private function findCaExamForCurrentExam(string $examId): Exams
    {
        $exam = Exams::with('examType')->findOrFail($examId);
        if ($exam->examType->type !== 'exam') {
            throw new Exception('Exam type is not valid or not found');
        }

        $caExamType = ExamType::where('semester_id', $exam->examType->semester_id)
            ->where('type', 'ca')
            ->firstOrFail();
        $additionalExams = Exams::where('school_year', $exam->school_year)
            ->where('exam_type_id', $caExamType->id)
            ->where('specialty_id', $exam->specialty_id)
            ->where('level_id', $exam->level_id)
            ->where('student_batch_id', $exam->student_batch_id)
            ->where('semester_id', $exam->semester_id)
            ->first();
        return $additionalExams;
    }

    private function determineExamLetterGrade(float $score, string $schoolId, Student $student, string $examId, string $courseId): array
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
            'score' => $score,
        ];
    }

    private function createResitableCourse(string $courseId, string $examId, Student $student, string $schoolId): void
    {


        if (!Studentresit::where("student_id", $student->id)
            ->where("level_id", $student->level_id)
            ->where("specialty_id", $student->specialty_id)
            ->where("course_id", $courseId)
            ->exists()) {
            Studentresit::create([
                'school_branch_id' => $schoolId,
                'student_id' => $student->id,
                'specialty_id' => $student->specialty_id,
                'course_id' => $courseId,
                'exam_id' => $examId,
                'student_batch_id' => $student->student_batch_id,
                'level_id' => $student->level_id,
            ]);
        }
    }

    protected function determineResitFee($student, $schoolBranchId)
    {
        $settings = SchoolBranchSetting::where('school_branch_id', $schoolBranchId)
            ->whereHas('settingDefination', function ($query) {
                $query->whereHas('settingCategory', function ($query) {
                    $query->where('name', "Resit Settings");
                });
            })
            ->with(['settingDefination' => function ($query) {
                $query->with('settingCategory');
            }])
            ->get();

        $generalResitBilling = $settings->first(function ($setting) {
            return $setting->settingDefination && $setting->settingDefination->key === "resitFee.generalBilling";
        });
        if ($generalResitBilling->value == true) {
            $generalResitBillingFee = $settings->first(function ($setting) {
                return $setting->settingDefination && $setting->settingDefination->key === "resitFee.generalBillingFee";
            });
            return $generalResitBillingFee->value;
        }
        $levelResitBilling = $settings->first(function ($setting) {
            return $setting->settingDefination && $setting->settingDefination->key === "resitFee.levelBilling";
        });
        if($levelResitBilling){
             $levelResitBillingFee = $settings->first(function ($setting) {
                return $setting->settingDefination && $setting->settingDefination->key === "resitFee.levelBillingFee";
            });

        }
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
        $studentResult = StudentResults::create([
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
        return $studentResult;
    }

    private function determineExamResultsStatus(Collection $marks): array
    {
        $failedCourses = $marks->filter(fn($mark) => ($mark['grade_status'] ?? $mark->grade_status ?? '') === 'failed');

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
