<?php

namespace App\Services\ResitExamEvaluation;

use App\Exceptions\AppException;
use App\Models\Exam\Exam;
use App\Models\Exam\ExamScore;
use App\Models\Examtype;
use App\Models\ResitCandidates;
use App\Models\ResitMarks;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class UpdateResitScoreService
{
    public function updateStudentResitScores(array $payload, object $currentSchool): array
    {
        $scores = $payload["scores"] ?? [];
        $candidateId = $payload["candidate_id"] ?? null;

        if (empty($scores)) {
            throw new AppException(
                'No scores submitted for update.',
                400,
                'No Scores Submitted',
                'Please select or enter at least one score entry to update.',
                '/scores/exam'
            );
        }

        return DB::transaction(function () use ($scores, $candidateId, $currentSchool) {
            $candidate = ResitCandidates::where("school_branch_id", $currentSchool->id)
                ->with([
                    'resitExam.examType',
                    'resitExam.examGradeScale.schoolGradeScale.grade',
                    'student'
                ])
                ->find($candidateId);

            if (!$candidate) {
                throw new AppException(
                    "The selected candidate record was not found.",
                    404,
                    "Candidate Record Not Found",
                    "The requested candidate record could not be found. Please refresh and try again.",
                    "/candidates"
                );
            }

            $resitExam = $candidate->resitExam;

            if (!$resitExam || !$resitExam->examType) {
                throw new AppException(
                    "Exam configuration or type missing.",
                    400,
                    "Invalid Exam Setup",
                    "The exam assigned to this candidate is missing required type or configuration settings.",
                    "/exams"
                );
            }

            $gradeScales = $resitExam->examGradeScale?->schoolGradeScale;

            if (!$gradeScales || $gradeScales->isEmpty()) {
                throw new AppException(
                    'Exam Grade Scale Not Configured',
                    404,
                    'Grade Scale Missing',
                    'A grade scale is linked to this exam but its grading ranges have not been set up yet.',
                    "/grades-categories"
                );
            }

            $scoreIds = collect($scores)->pluck("score_id")->filter()->toArray();

            $existingResitMarks = ResitMarks::where("school_branch_id", $currentSchool->id)
                ->where("candidate_id", $candidate->id)
                ->whereIn("id", $scoreIds)
                ->with(['resit' => function ($query) {
                    $query->withTrashed();
                }])
                ->get()
                ->keyBy('id');

            $now = now();
            $updatedScores = [];

            foreach ($scores as $scoreItem) {
                $scoreId = $scoreItem["score_id"];
                $newScoreValue = (float) $scoreItem["score"];

                /** @var ResitMarks|null $resitMark */
                $resitMark = $existingResitMarks->get($scoreId);

                if (!$resitMark) {
                    throw new AppException(
                        'Resit Score Record Not Found',
                        404,
                        'Score Entry Not Found',
                        'One or more score entries could not be matched with our records. Please reload the page and try again.',
                        "/resits"
                    );
                }

                $this->validateScore($resitExam, $newScoreValue);

                $newResitGrade = $this->determineGrade($newScoreValue, $gradeScales);

                if (!$newResitGrade) {
                    throw new AppException(
                        'No matching grade found.',
                        400,
                        'Out of Grading Range',
                        "The score {$newScoreValue} does not fall within any configured grade scale range for this exam.",
                        "/grades-categories"
                    );
                }

                $resitMark->update([
                    "grade_id" => $newResitGrade->id,
                    "score" => $newScoreValue,
                    "updated_at" => $now,
                ]);

                $resit = $resitMark->resit;

                if ($resit && $resit->exam) {
                    $relatedCa = $this->getRelatedCa($resit->exam, $currentSchool->id);

                    $newCaScale = $this->determineNewScale(
                        $relatedCa->examGradeScale->schoolGradeScale,
                        $newResitGrade->grade->letter_grade
                    );

                    $newExamScale = $this->determineNewScale(
                        $resit->exam->examGradeScale->schoolGradeScale,
                        $newResitGrade->grade->letter_grade
                    );

                    if ($newCaScale) {
                        $this->updateTargetScore(
                            $relatedCa->id,
                            $candidate->student_id,
                            $resit->course_id,
                            $newCaScale,
                            $currentSchool->id
                        );
                    }

                    if ($newExamScale) {
                        $this->updateTargetScore(
                            $resit->exam->id,
                            $candidate->student_id,
                            $resit->course_id,
                            $newExamScale,
                            $currentSchool->id
                        );
                    }

                    $isPassed = strtolower($newResitGrade->result ?? '') === 'passed'
                        || strtolower($newResitGrade->status ?? '') === 'passed';

                    if ($resit) {
                        if ($isPassed) {
                            $resit->delete();
                        } else {
                            if ($resit->trashed()) {
                                $resit->restore();
                            }

                            $resit->update([
                                'payment_status' => 'unpaid',
                                'updated_at' => $now,
                            ]);
                        }
                    }
                }
                $updatedScores[] = $resitMark;
            }

            return [
                'message' => 'Resit scores updated successfully.',
                'candidate_id' => $candidate->id,
                'total_updated' => count($updatedScores),
            ];
        });
    }

    private function updateTargetScore(
        string $examId,
        string $studentId,
        string $courseId,
        object $newGradeScale,
        string $schoolBranchId
    ): void {
        $existingScore = ExamScore::where("school_branch_id", $schoolBranchId)
            ->where('exam_id', $examId)
            ->where("course_id", $courseId)
            ->whereHas('candidate', function ($query) use ($studentId, $schoolBranchId) {
                $query->where("school_branch_id", $schoolBranchId)
                    ->where("student_id", $studentId);
            })
            ->first();

        if ($existingScore) {
            $existingScore->update([
                'grade_id' => $newGradeScale->id,
                'updated_at' => now(),
            ]);
        }
    }

    private function getRelatedCa(Exam $exam, string $schoolBranchId): Exam
    {
        if ($exam->examType?->type !== 'exam') {
            throw new AppException(
                'Invalid Exam Type',
                400,
                'Invalid Evaluation Type',
                'The selected evaluation item is not configured as a standard final exam.',
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
                'Continuous Assessment Missing',
                'Continuous Assessment configuration for this semester could not be found.',
                '/exam-types'
            );
        }

        $relatedCaExam = Exam::where('school_branch_id', $schoolBranchId)
            ->where('exam_type_id', $caExamType->id)
            ->where('school_year_id', $exam->school_year_id)
            ->with(['examGradeScale.schoolGradeScale.grade', "examType"])
            ->first();

        if (!$relatedCaExam) {
            throw new AppException(
                'No Continuous Assessment Found',
                404,
                'Assessment Setup Incomplete',
                'No corresponding Continuous Assessment exam record exists for the selected academic year.',
                '/exams'
            );
        }

        return $relatedCaExam;
    }

    private function determineNewScale(Collection $gradeScales, string $resitLetterGrade): ?object
    {
        return $gradeScales->firstWhere(fn($gs) => $gs->grade?->letter_grade === $resitLetterGrade);
    }

    private function validateScore(object $exam, float $score): void
    {
        if ($score < 0 || ($exam->max_score !== null && $score > $exam->max_score)) {
            throw new AppException(
                'Score exceeds maximum exam mark.',
                400,
                'Invalid Score Value',
                "The score {$score} is out of bounds. Please provide a score between 0 and {$exam->max_score}.",
                "/exam"
            );
        }
    }

    private function determineGrade(float $score, Collection $gradeScales): ?object
    {
        foreach ($gradeScales as $gradeScale) {
            if ($score >= $gradeScale->minimum_score && $score <= $gradeScale->maximum_score) {
                return $gradeScale;
            }
        }

        return null;
    }
}
