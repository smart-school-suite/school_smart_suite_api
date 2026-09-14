<?php

namespace App\Services\ExamEvaluation;

use Exception;
use Illuminate\Support\Facades\DB;
use App\Models\Course\CourseSpecialty;
use App\Models\Grades;
use App\Models\Exam\ExamCandidate;
use App\Exceptions\AppException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class AddCaScoresService
{
    public function addCaScore(array $payload, object $currentSchool, object $authAdmin): array
    {
        $scores = $payload['scores'];
        $candidateId = $payload['candidate_id'];

        if (empty($scores)) {
            throw new AppException(
                'No scores submitted.',
                400,
                'No Scores',
                'Please provide at least one course score to submit.',
                '/scores/ca'
            );
        }

        try {
            DB::beginTransaction();

            $candidate = ExamCandidate::where("school_branch_id", $currentSchool->id)
                ->with([
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
                    "The CA Exam Candidate Already Accessed.",
                    400,
                    "CA Exam  Candidate Already Accessed",
                    "The CA Exam Candidate Has Already Been Accessed You Can Not Submit Scores Again",
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
                    '/scores/ca/duplicate-course'
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

            $examScores = [];
            $now = now();

            foreach ($scores as $score) {
                $this->validateCaMark($exam, (float) $score['score']);

                $gradeId = $this->determineGrade((float) $score['score'], $gradeScales);

                if ($gradeId === null) {
                    throw new AppException(
                        'No matching grade found.',
                        400,
                        'Grade Not Matched',
                        "No grade scale range matches the submitted score of {$score['score']}.",
                        "/grades-categories"
                    );
                }

                $examScores[] = [
                    "id" => Str::uuid()->toString(),
                    "school_branch_id" => $currentSchool->id,
                    "candidate_id" => $candidate->id,
                    "course_id" => $score["course_id"],
                    "exam_id" => $exam->id,
                    "grade_id" => $gradeId,
                    "score" => $score["score"],
                    "updated_at" => $now,
                    "created_at" => $now,
                ];
            }

            DB::table("exam_scores")->insert($examScores);

            DB::commit();

            return [
                'candidate_id' => $candidate->id,
                'exam_id' => $exam->id,
                'courses_submitted' => $submittedCourseIds->count(),
                'total_score' => collect($scores)->sum(fn($s) => (float) $s['score']),
                'average_score' => round(collect($scores)->avg(fn($s) => (float) $s['score']), 2),
            ];
        } catch (AppException | ModelNotFoundException $e) {
            DB::rollBack();
            throw $e;
        } catch (Exception $e) {
            Log::error('Failed to add CA scores.', [
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
                'An unexpected error occurred while adding CA scores. Please try again later.',
                500,
                'Server Error',
                'We encountered an unexpected issue while saving the scores. This has been logged for investigation.',
                null
            );
        }
    }

    private function validateCaMark(object $exam, float $score): void
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

    private function determineGrade(
        float $score,
        Collection $gradeScales
    ): ?string {
        foreach ($gradeScales as $gradeScale) {
            if ($score >= $gradeScale->minimum_score && $score <= $gradeScale->maximum_score) {
                return $gradeScale->id;
            }
        }

        return null;
    }
}
