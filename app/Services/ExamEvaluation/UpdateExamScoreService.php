<?php

namespace App\Services\ExamEvaluation;

use App\Exceptions\AppException;
use App\Jobs\Resit\SyncResitExamJob;
use App\Models\Exam\Exam;
use App\Models\Exam\ExamCandidate;
use App\Models\Exam\ExamScore;
use App\Models\Examtype;
use App\Models\Grades;
use App\Models\Studentresit;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateExamScoreService
{
    public function updateExamScore(array $payload, object $currentSchool, object $authAdmin): array
    {
        $candidateId = $payload['candidate_id'] ?? null;
        $submittedScores = collect($payload['scores'] ?? []);

        if ($submittedScores->isEmpty()) {
            throw new AppException(
                'No scores submitted.',
                400,
                'No Scores',
                'Please provide at least one course score to submit.',
                '/scores/exam'
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
                    'The Exam Candidate Not Accessed.',
                    400,
                    'Exam Candidate Not Accessed',
                    'The Exam Candidate has not been accessed or configured for score submissions.',
                    '/accessed-students'
                );
            }

            $exam = $candidate->exam;

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

            $relatedCaExam = $this->getRelatedCa($exam, $currentSchool->id);

            $caScores = ExamScore::where('school_branch_id', $currentSchool->id)
                ->where('exam_id', $relatedCaExam->id)
                ->whereHas('candidate.student', function ($query) use ($candidate, $currentSchool) {
                    $query->where('school_branch_id', $currentSchool->id)
                        ->where('id', $candidate->student_id);
                })
                ->get()
                ->keyBy('course_id');

            if ($caScores->isEmpty()) {
                throw new AppException(
                    'Missing CA Scores',
                    404,
                    'Missing CA Scores',
                    'No Continuous Assessment scores were found for this candidate. Please submit CA scores before evaluating final exams.',
                    '/accessed-students'
                );
            }

            $scoreIds = $submittedScores->pluck('score_id')->filter()->toArray();

            $examScores = ExamScore::where('school_branch_id', $currentSchool->id)
                ->where('exam_id', $exam->id)
                ->whereIn('id', $scoreIds)
                ->get()
                ->keyBy('id');

            $updatedCount = 0;
            $resitsCreated = 0;
            $resitsRemoved = 0;

            foreach ($submittedScores as $scoreItem) {
                $scoreValue = (float) ($scoreItem['score'] ?? 0);
                $scoreId = $scoreItem['score_id'] ?? null;

                $targetScoreRecord = $examScores->get($scoreId);

                if (! $targetScoreRecord) {
                    throw new AppException(
                        'Invalid score record.',
                        400,
                        'Record Not Found',
                        'One of the requested score records could not be matched.',
                        '/scores/exam'
                    );
                }

                $caScoreRow = $caScores->get($targetScoreRecord->course_id);

                if (! $caScoreRow) {
                    throw new AppException(
                        'Missing CA score for one or more courses.',
                        400,
                        'Missing CA Score',
                        'No Continuous Assessment score was found for one of the submitted courses.',
                        '/accessed-students'
                    );
                }

                $caScoreValue = (float) $caScoreRow->score;
                $finalScore = $caScoreValue + $scoreValue;

                $this->validateScore($exam, $finalScore);

                $grade = $this->determineGrade($finalScore, $gradeScales);

                if ($grade === null) {
                    throw new AppException(
                        'No matching grade found.',
                        400,
                        'Grade Not Matched',
                        'No grade scale range matches the computed score.',
                        '/grades-categories'
                    );
                }

                $targetScoreRecord->update([
                    'score' => $scoreValue,
                    'grade_id' => $grade->id,
                ]);

                $resitAction = $this->syncResit(
                    $targetScoreRecord->course_id,
                    $candidate->student_id,
                    $exam->id,
                    $currentSchool->id,
                    $grade->result === 'failed'
                );

                if ($resitAction === 'created') {
                    $resitsCreated++;
                } elseif ($resitAction === 'removed') {
                    $resitsRemoved++;
                }

                $updatedCount++;
            }

            DB::commit();
            if ($this->isExamFullyEvaluated($exam->id)) {
                SyncResitExamJob::dispatch($exam->id, $currentSchool->id)->afterCommit();
            }

            return [
                'records_updated' => $updatedCount,
                'resits_created' => $resitsCreated,
                'resits_removed' => $resitsRemoved,
            ];
        } catch (AppException | ModelNotFoundException $e) {
            DB::rollBack();
            throw $e;
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Failed to update Exam scores.', [
                'message' => $e->getMessage(),
                'exception' => get_class($e),
                'candidate_id' => $candidateId,
                'school_branch_id' => $currentSchool->id ?? null,
                'auth_admin_id' => $authAdmin->id ?? null,
            ]);

            throw new AppException(
                'An unexpected error occurred while updating exam scores.',
                500,
                'Server Error',
                'We encountered an issue saving the scores. Please try again later.',
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
    private function syncResit(
        string $courseId,
        string $studentId,
        string $examId,
        string $schoolBranchId,
        bool $hasFailed
    ): ?string {
        $existingResit = Studentresit::where('school_branch_id', $schoolBranchId)
            ->where('student_id', $studentId)
            ->where('exam_id', $examId)
            ->where('course_id', $courseId)
            ->first();

        if ($hasFailed) {
            if (! $existingResit) {
                Studentresit::create([
                    'school_branch_id' => $schoolBranchId,
                    'student_id' => $studentId,
                    'course_id' => $courseId,
                    'exam_id' => $examId,
                ]);

                return 'created';
            }
        } else {
            if ($existingResit) {
                $existingResit->delete();

                return 'removed';
            }
        }

        return null;
    }

    private function validateScore(object $exam, float $score): void
    {
        if ($score < 0 || $score > $exam->max_score) {
            throw new AppException(
                'Score exceeds maximum exam mark.',
                400,
                'Validation Error',
                "The entered computed score of {$score} exceeds the maximum allowed mark of {$exam->max_score}.",
                '/exam'
            );
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

        if (! $caExamType) {
            throw new AppException(
                'No Continuous Assessment Type Found',
                404,
                'Continuous Assessment Not Configured',
                'No Continuous Assessment type is set up for this semester.',
                '/exam-types'
            );
        }

        $relatedCaExam = Exam::where('school_branch_id', $schoolBranchId)
            ->where('exam_type_id', $caExamType->id)
            ->where('school_year_id', $exam->school_year_id)
            ->first();

        if (! $relatedCaExam) {
            throw new AppException(
                'No Continuous Assessment Found',
                404,
                'Continuous Assessment Missing',
                'No corresponding Continuous Assessment exam exists for this academic year and semester.',
                '/exams'
            );
        }

        return $relatedCaExam;
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
