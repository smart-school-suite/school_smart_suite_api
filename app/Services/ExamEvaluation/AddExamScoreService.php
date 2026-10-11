<?php

namespace App\Services\ExamEvaluation;

use Exception;
use Illuminate\Support\Facades\DB;
use App\Models\Grades;
use App\Models\Examtype;
use App\Models\Exam\ExamCandidate;
use App\Models\Exam\ExamScore;
use App\Models\Exam\Exam;
use App\Exceptions\AppException;
use App\Models\Course\CourseSpecialty;
use Illuminate\Support\Collection;
use App\Models\Studentresit;
use Illuminate\Support\Str;
use App\Jobs\Resit\CreateResitExamJob;
use Illuminate\Support\Facades\Log;
class AddExamScoreService
{
    public function addExamScores(array $payload, object $currentSchool, object $authAdmin): array
    {
        $scores = $payload["scores"];
        $candidateId = $payload["candidate_id"];

        if (empty($scores)) {
            throw new AppException(
                'No scores submitted.',
                400,
                'No Scores',
                'Please provide at least one course score to submit.',
                '/scores/exam'
            );
        }

        DB::beginTransaction();

        try {
            $candidate = ExamCandidate::where("school_branch_id", $currentSchool->id)
                ->with([
                    'exam.examType',
                    'exam.examGradeScale.schoolGradeScale.grade',
                    'exam.schoolYear.schoolSemester.semester',
                    'exam.schoolYear.specialty',
                    'examScores',
                    'student'
                ])
                ->find($candidateId);

            if (!$candidate) {
                throw new AppException(
                    "The selected candidate could not be found.",
                    404,
                    "Candidate Not Found",
                    "Please verify that the selected candidate exists or has not been removed.",
                    "/candidates"
                );
            }

            if ($candidate->examScores->isNotEmpty()) {
                throw new AppException(
                    "The Exam Candidate Already Accessed.",
                    400,
                    "Exam Candidate Already Accessed",
                    "The Exam Candidate Has Already Been Accessed. You Cannot Submit Scores Again.",
                    "/accessed-students"
                );
            }

            $exam = $candidate->exam;

            if (!$exam) {
                throw new AppException(
                    "The exam associated with this candidate could not be found.",
                    404,
                    "Exam Not Found",
                    "Please verify that the candidate is assigned to a valid exam.",
                    "/exams"
                );
            }

            if (!$exam->examType) {
                throw new AppException(
                    "Exam type not set for this exam.",
                    400,
                    "Exam Type Not Set",
                    "The exam type for this exam has not been set. Please configure it before proceeding.",
                    "/exams/{$exam->id}/edit"
                );
            }

            $schoolSemester = $exam->schoolYear->schoolSemester->first();

            if (!$schoolSemester || !$schoolSemester->semester) {
                throw new AppException(
                    'Semester Not Set',
                    400,
                    'Semester Not Set',
                    'The semester for this school year has not been set. Please set the semester to proceed.',
                    "/school-years/{$exam->schoolYear->id}/edit"
                );
            }

            $semesterId = $schoolSemester->semester->id;

            $courses = CourseSpecialty::where("school_branch_id", $currentSchool->id)
                ->where("specialty_id", $exam->schoolYear->specialty->id)
                ->whereHas('course', function ($query) use ($semesterId) {
                    $query->where("semester_id", $semesterId);
                })
                ->with(['course' => function ($query) use ($semesterId) {
                    $query->where("semester_id", $semesterId);
                }])
                ->get()
                ->pluck('course')
                ->filter()
                ->unique('id')
                ->values();

            $requiredCourseIds = $courses->pluck('id')->sort()->values();

            $submittedCourseIds = collect($scores)
                ->pluck('course_id')
                ->filter()
                ->unique()
                ->sort()
                ->values();

            if ($submittedCourseIds->count() !== count($scores)) {
                throw new AppException(
                    'Duplicate course entries in payload.',
                    400,
                    'Duplicate Courses',
                    'Each course may only be submitted once per exam.',
                    '/scores/exam/duplicate-course'
                );
            }

            if ($submittedCourseIds->count() !== $requiredCourseIds->count()) {
                throw new AppException(
                    'Course count mismatch.',
                    400,
                    'Incomplete Course Scores',
                    "This exam requires scores for {$requiredCourseIds->count()} course(s), but {$submittedCourseIds->count()} were submitted.",
                    "/exams/{$exam->id}"
                );
            }

            $missingCourseIds = $requiredCourseIds->diff($submittedCourseIds)->values();
            $extraCourseIds   = $submittedCourseIds->diff($requiredCourseIds)->values();

            if ($missingCourseIds->isNotEmpty() || $extraCourseIds->isNotEmpty()) {
                throw new AppException(
                    'Course mismatch.',
                    400,
                    'Course Mismatch',
                    'The submitted courses do not match the required courses for this exam.',
                    "/exams/{$exam->id}"
                );
            }

            $gradeScales = Grades::with('lettergrade')
                ->where('school_branch_id', $currentSchool->id)
                ->where('grades_category_id', $exam->grades_category_id)
                ->orderBy('minimum_score', 'desc')
                ->get();

            if ($gradeScales->isEmpty()) {
                throw new AppException(
                    'Exam Grade Scale Not Configured',
                    404,
                    'Grade Scale Not Configured',
                    'The grade scale has been set for this exam, but this grade scale has not been configured yet. Please ensure that grades have been set up for this scale.',
                    "/grades-categories"
                );
            }

            $relatedCaExam = $this->getRelatedCa($exam, $currentSchool->id);

            $caScores = ExamScore::where("school_branch_id", $currentSchool->id)
                ->where("exam_id", $relatedCaExam->id)
                ->whereHas('candidate.student', function ($query) use ($candidate, $currentSchool) {
                    $query->where("school_branch_id", $currentSchool->id)
                        ->where("id", $candidate->student_id);
                })
                ->get()
                ->keyBy('course_id');

            if ($caScores->isEmpty()) {
                throw new AppException(
                    "Missing CA Scores",
                    404,
                    "Missing CA Scores",
                    "No Continuous Assessment scores were found for this candidate. Please submit CA scores before the final exam.",
                    "/accessed-students"
                );
            }

            $examScores = [];
            $now = now();
            $totalFinalScore = 0.0;
            $student = $candidate->student;
            foreach ($scores as $score) {
                $examScore = (float) $score["score"];
                $courseId = $score["course_id"];

                $caScoreRow = $caScores->get($courseId);

                if (!$caScoreRow) {
                    throw new AppException(
                        "Missing CA score for one or more courses.",
                        400,
                        "Missing CA Score",
                        "No Continuous Assessment score was found for one of the submitted courses. Please ensure CA scores exist for all courses.",
                        "/accessed-students"
                    );
                }

                $caScoreValue = (float) $caScoreRow->score;
                $finalScore = $caScoreValue + $examScore;

                $this->validateScore($exam, $finalScore);

                $grade = $this->determineGrade($finalScore, $gradeScales);

                if ($grade === null) {
                    throw new AppException(
                        'No matching grade found.',
                        400,
                        'Grade Not Matched',
                        "No grade scale range matches the computed score of {$finalScore}.",
                        "/grades-categories"
                    );
                }

                if ($grade->result === "failed") {

                    $this->createResit($courseId, $student->id, $exam->id, $currentSchool->id);
                }

                $examScores[] = [
                    "id" => Str::uuid()->toString(),
                    "school_branch_id" => $currentSchool->id,
                    "candidate_id" => $candidate->id,
                    "course_id" => $courseId,
                    "exam_id" => $exam->id,
                    "grade_id" => $grade->id,
                    "score" => $finalScore,
                    "updated_at" => $now,
                    "created_at" => $now,
                ];

                $totalFinalScore += $finalScore;
            }

            DB::table("exam_scores")->insert($examScores);
            if ($this->isExamFullyEvaluated($exam->id)) {
                CreateResitExamJob::dispatch($exam->id, $currentSchool->id)->afterCommit();
            }

            DB::commit();

            return [
                'candidate_id' => $candidate->id,
                'exam_id' => $exam->id,
                'courses_submitted' => count($examScores),
                'total_score' => round($totalFinalScore, 2),
                'average_score' => round($totalFinalScore / count($examScores), 2),
                'submitted_at' => $now->toIso8601String(),
                'message' => 'Exam scores submitted successfully.',
            ];
        } catch (AppException $e) {
            DB::rollBack();
            throw $e;
        } catch (Exception $e) {
            DB::rollBack();
              Log::error('Failed to add Exam scores.', [
                'message' => $e->getMessage(),
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'candidate_id' => $candidateId,
                'school_branch_id' => $currentSchool->id,
                'auth_admin_id' => $authAdmin->id ?? null,
                'payload_scores_count' => is_array($scores) ? count($scores) : null,
                'submitted_course_ids' => $submittedCourseIds ?? null,
                'required_course_ids' => $requiredCourseIds ?? null,
                'exam_id' => $exam->id ?? null,
                'semester_id' => $semesterId ?? null,
            ]);
            throw new AppException(
                'An unexpected error occurred while adding exam scores. Please try again later.',
                500,
                'Server Error',
                'We encountered an unexpected issue while saving the scores. This has been logged for investigation.',
                null
            );
        }
    }

    private function isExamFullyEvaluated(string $examId): bool
    {
        $exam = Exam::with('examCandidate.examScores')->findOrFail($examId);

        return $exam->examCandidate->every(
            fn($candidate) => $candidate->examScores->isNotEmpty()
        );
    }

    private function validateScore(object $exam, float $score): void
    {
        if ($score < 0 || $score > $exam->max_score) {
            throw new AppException(
                'Score exceeds maximum exam mark.',
                400,
                'Validation Error',
                "The entered score of $score is not valid. The maximum allowed mark for this exam is $exam->max_score.",
                "/exam"
            );
        }
    }

    private function createResit(string $courseId, string $studentId, string $examId, string $schoolBranchId)
    {
        $existingResit = Studentresit::where("school_branch_id", $schoolBranchId)
            ->where("student_id", $studentId)
            ->where("exam_id", $examId)
            ->where("course_id", $courseId)
            ->exists();
        if (!$existingResit) {
            Studentresit::create([
                'school_branch_id' => $schoolBranchId,
                'student_id' => $studentId,
                'course_id' => $courseId,
                'exam_id' => $examId,
            ]);
        }
    }
    private function getRelatedCa(Exam $exam, string $schoolBranchId): Exam
    {
        if ($exam->examType->type !== 'exam') {
            throw new AppException(
                'Invalid Exam Type',
                400,
                'Exam Type Error',
                'The selected evaluation is not a final exam. Please select a valid exam to proceed.',
                '/exams'
            );
        }

        $caExamType = Examtype::where('semester_id', $exam->examType->semester_id)
            ->where('type', 'ca')
            ->first();

        if (!$caExamType) {
            throw new AppException(
                'No Continuous Assessment Type Found',
                404,
                'Continuous Assessment Not Configured',
                'No Continuous Assessment type is set up for this semester. Please configure it in the settings before proceeding.',
                '/exam-types'
            );
        }

        $relatedCaExam = Exam::where('school_branch_id', $schoolBranchId)
            ->where('exam_type_id', $caExamType->id)
            ->where('school_year_id', $exam->school_year_id)
            ->first();

        if (!$relatedCaExam) {
            throw new AppException(
                'No Continuous Assessment Found',
                404,
                'Continuous Assessment Missing',
                'No corresponding Continuous Assessment exam exists for this academic year and semester. Please create one to proceed.',
                '/exams'
            );
        }

        return $relatedCaExam;
    }

    private function determineGrade(
        float $score,
        Collection $gradeScales
    ): ?object {
        foreach ($gradeScales as $gradeScale) {
            if ($score >= $gradeScale->minimum_score && $score <= $gradeScale->maximum_score) {
                return $gradeScale;
            }
        }

        return null;
    }
}
