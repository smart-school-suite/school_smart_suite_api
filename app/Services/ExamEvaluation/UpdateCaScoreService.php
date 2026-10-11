<?php

namespace App\Services\ExamEvaluation;

use App\Exceptions\AppException;
use App\Models\Exam\ExamCandidate;
use App\Models\Exam\ExamScore;
use App\Models\Grades;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateCaScoreService
{
    public function updateCaScore(array $payload, object $currentSchool): array
    {
        $candidateId = $payload['candidate_id'] ?? null;
        $submittedScores = collect($payload['scores'] ?? []);

        if ($submittedScores->isEmpty()) {
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

            $candidate = ExamCandidate::where('school_branch_id', $currentSchool->id)
                ->with([
                    'exam.examGradeScale.schoolGradeScale.grade',
                    'exam.schoolYear.schoolSemester.semester',
                    'exam.schoolYear.specialty',
                    'examScores',
                    'student',
                ])
                ->find($candidateId);

            if (! $candidate) {
                throw new AppException(
                    'The selected candidate could not be found.',
                    404,
                    'Candidate Not Found',
                    'Please verify that the selected candidate exists or has not been removed.',
                    '/candidates'
                );
            }

            if ($candidate->examScores->isEmpty()) {
                throw new AppException(
                    'The CA Exam Candidate Not Accessed.',
                    400,
                    'CA Exam Candidate Not Accessed',
                    'The CA Exam Candidate has not been accessed or configured for score submissions.',
                    '/accessed-students'
                );
            }

            $exam = $candidate->exam;
            $scoreIds = $submittedScores->pluck('score_id')->filter()->toArray();

            $examScores = ExamScore::where('school_branch_id', $currentSchool->id)
                ->where('exam_id', $exam->id)
                ->whereIn('id', $scoreIds)
                ->get()
                ->keyBy('id');

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
                    'The grade scale has been set for this exam, but this grade scale has not been configured yet.',
                    '/grades-categories'
                );
            }

            $updatedCount = 0;

            foreach ($submittedScores as $scoreItem) {
                $scoreValue = (float) ($scoreItem['score'] ?? 0);
                $scoreId = $scoreItem['score_id'] ?? null;

                $this->validateCaMark($exam, $scoreValue);

                $gradeId = $this->determineGrade($scoreValue, $gradeScales);

                if ($gradeId === null) {
                    throw new AppException(
                        'No matching grade found.',
                        400,
                        'Grade Not Matched',
                        'One or more entered scores fall outside the configured grading scale.',
                        '/grades-categories'
                    );
                }

                $targetScoreRecord = $examScores->get($scoreId);

                if (! $targetScoreRecord) {
                    throw new AppException(
                        'Invalid score entry.',
                        400,
                        'Record Not Found',
                        'One of the requested score records could not be updated.',
                        '/scores/ca'
                    );
                }

                $targetScoreRecord->update([
                    'score' => $scoreValue,
                    'grade_id' => $gradeId,
                ]);

                $updatedCount++;
            }

            DB::commit();

            return [
                'status' => 'success',
                'message' => 'CA scores updated successfully.',
                'records_updated' => $updatedCount,
            ];

        } catch (AppException|ModelNotFoundException $e) {
            DB::rollBack();
            throw $e;
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Failed to update CA scores.', [
                'message' => $e->getMessage(),
                'exception' => get_class($e),
                'candidate_id' => $candidateId,
                'school_branch_id' => $currentSchool->id ?? null,
            ]);

            throw new AppException(
                'An unexpected error occurred while updating CA scores.',
                500,
                'Server Error',
                'We encountered an issue saving the scores. Please try again or contact support.',
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
                "The entered score of {$score} exceeds the maximum allowed mark of {$exam->max_score} for this exam.",
                '/exam'
            );
        }
    }

    private function determineGrade(float $score, Collection $gradeScales): ?string
    {
        foreach ($gradeScales as $gradeScale) {
            if ($score >= $gradeScale->minimum_score && $score <= $gradeScale->maximum_score) {
                return (string) $gradeScale->id;
            }
        }

        return null;
    }
}
