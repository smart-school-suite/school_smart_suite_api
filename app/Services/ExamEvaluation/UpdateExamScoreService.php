<?php

namespace App\Services\ExamEvaluation;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Models\Exams;
use App\Models\Marks;
use App\Models\Grades;
use App\Models\Student;
use App\Models\Studentresit;
use App\Models\StudentResults;
use App\Models\Courses;
use App\Models\Examtype;
use App\Events\Actions\AdminActionEvent;
class UpdateExamScoreService
{
    public function updateExamScore(array $updateData, $currentSchool, $authAdmin): array
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

                $course = Courses::findOrFail($score->courses_id);

                $totalScore = $this->calculateTotalScoreForExam(
                    $currentSchool->id,
                    $student,
                    $data['score'],
                    $exam,
                    $course
                );

                $letterGradeData = $this->determineExamLetterGrade($totalScore, $currentSchool->id, $student, $exam->id, $course->id);

                $updatedScore = $this->updateMarkRecord($letterGradeData, $score, $course);

                $results[] = $updatedScore;
            }

            $this->getAllStudentScoresForExam($student, $exam, $currentSchool, $results);

            $totalScoreAndGpa = $this->calculateGpaAndTotalScore($results);

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
                    "message" => "Exam Results Updated",
                ]
            );
            return $results;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function getAllStudentScoresForExam(Student $student, Exams $exam, $currentSchool, array &$results): void
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

    private function findExamsBasedOnCriteria(string $examId): Exams
    {
        $exam = Exams::with('examType')->findOrFail($examId);
        if ($exam->examType->type !== 'exam') {
            throw new Exception('Exam type is not valid or not found');
        }

        $caExamType = ExamType::where('semester_id', $exam->semester_id)
            ->where('type', 'ca')
            ->firstOrFail();

        return Exams::where('school_year', $exam->school_year)
            ->where('exam_type_id', $caExamType->id)
            ->where('specialty_id', $exam->specialty_id)
            ->where('level_id', $exam->level_id)
            ->where("student_batch_id", $exam->student_batch_id)
            ->where('semester_id', $exam->semester_id)
            ->firstOrFail();
    }

    private function retrieveCaScore(string $schoolId, Student $student, string $courseId, string $examId): Marks
    {
        return Marks::where('school_branch_id', $schoolId)
            ->where('exam_id', $examId)
            ->where('student_id', $student->id)
            ->where('courses_id', $courseId)
            ->where('specialty_id', $student->specialty_id)
            ->where('level_id', $student->level_id)
            ->where('student_batch_id', $student->student_batch_id)
            ->firstOrFail();
    }

    private function calculateTotalScoreForExam(string $schoolId, Student $student, float $newExamScore, Exams $exam, Courses $course): float
    {
        $additionalExam = $this->findExamsBasedOnCriteria($exam->id);
        $caScoreRecord = $this->retrieveCaScore($schoolId, $student, $course->id, $additionalExam->id);
        $totalScore = $newExamScore + $caScoreRecord->score;

        if ($totalScore > ($additionalExam->weighted_mark + $exam->weighted_mark)) {
            throw new Exception("Total score {$totalScore} exceeds maximum allowed score $additionalExam->weighted_mark + $exam->weighted_mark", 400);
        }

        return $totalScore;
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
                if ($grade->resit_status === 'no_resit') {
                    $this->checkAndRemoveStudentResit($courseId, $examId, $student, $schoolId);
                }
                return [
                    'letterGrade' => $grade->lettergrade->letter_grade ?? 'N/A',
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

    public function createResitableCourse(string $courseId, string $examId, Student $student, string $schoolId): void
    {
        if (
            !Studentresit::where("student_id", $student->id)
                ->where("level_id", $student->level_id)
                ->where("specialty_id", $student->specialty_id)
                ->where("course_id", $courseId)
                ->exists()
        ) {
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

    private function updateMarkRecord(array $updateScoreData, object $score, object $course): array
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

    private function calculateGpaAndTotalScore(array $results): array
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
        $studentResult->score_details = json_encode($results);
        $studentResult->exam_status = $examStatus['passed'] ? 'Passed' : 'Failed';
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

    private function checkAndRemoveStudentResit($courseId, $examId, $student, $schoolId)
    {
        $studentResit = Studentresit::where("student_id", $student->id)
            ->where("level_id", $student->level_id)
            ->where("specialty_id", $student->specialty_id)
            ->where("course_id", $courseId)
            ->where("exam_id", $examId)
            ->where("school_branch_id", $schoolId)
            ->first();
        if ($studentResit) {
            $studentResit->delete();
        }
    }
}
