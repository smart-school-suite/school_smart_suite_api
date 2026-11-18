<?php

namespace App\Services\ResitResult;

use App\Exceptions\AppException;
use App\Models\ResitExam;
use App\Models\ResitResultPublication;
use Carbon\Carbon;

/**
 * Resit result management, like exam result management (ExamResultService)
 * without standings: which resit exams have results, how far their
 * evaluation has got, and publishing their results to students now or at
 * a set time.
 */
class ResitResultService
{
    /** Resit exams with what the results page needs (ResitResultResource). */
    public function getResitExams(object $currentSchool)
    {
        return ResitExam::where('school_branch_id', $currentSchool->id)
            ->with([
                'examType.semesters',
                'schoolYear.specialty.level',
                'schoolYear.systemAcademicYear',
                'resitCandidates.resitScores',
                'resultPublication',
            ])
            ->latest()
            ->get();
    }

    /**
     * Publishes a resit exam's results now ({ status: active }) or at
     * published_at ({ status: scheduled }). Every candidate must be
     * evaluated first, and results already released stay released.
     */
    public function publishResitResult(object $currentSchool, object $authUser, array $payload): array
    {
        $exam = $this->findExam($currentSchool, $payload['resit_exam_id'], ['resitCandidates.resitScores', 'resultPublication', 'examType']);
        $progress = self::progressCounts($exam);

        if ($progress['total'] === 0) {
            throw new AppException(
                "This resit exam has no candidates.",
                409,
                "Nothing To Publish",
                "This resit exam has no candidates, so there are no results to publish.",
                null
            );
        }
        if ($progress['not_evaluated'] > 0) {
            $remaining = $progress['not_evaluated'] === 1 ? '1 candidate has' : "{$progress['not_evaluated']} candidates have";
            throw new AppException(
                "Not every candidate has been evaluated.",
                409,
                "Not Ready To Publish",
                "{$remaining} not been evaluated yet. Evaluate every candidate before publishing the results.",
                null
            );
        }

        $existing = $exam->resultPublication;
        if ($existing?->published_at && $existing->published_at->lte(now())) {
            throw new AppException(
                "These results are already published.",
                409,
                "Already Published",
                "The results of this resit exam were published on {$existing->published_at->format('j M Y, H:i')}. They stay published.",
                null
            );
        }

        $publishedAt = $payload['status'] === 'scheduled'
            ? Carbon::parse($payload['published_at'])
            : now();

        ResitResultPublication::updateOrCreate(
            ['resit_exam_id' => $exam->id, 'school_branch_id' => $currentSchool->id],
            [
                'published_at' => $publishedAt,
                'authorable_id' => $authUser->id,
                'authorable_type' => get_class($authUser),
            ]
        );

        return $this->getPublishDetails($currentSchool, $exam->id);
    }

    /** The publication: whether, when and by whom, with the evaluation counts. */
    public function getPublishDetails(object $currentSchool, string $resitExamId): array
    {
        $exam = $this->findExam($currentSchool, $resitExamId, ['resitCandidates.resitScores', 'resultPublication', 'examType', 'schoolYear.systemAcademicYear', 'schoolYear.specialty.level']);
        $publication = $exam->resultPublication;
        $progress = self::progressCounts($exam);
        $author = $publication?->authorable_type && class_exists($publication->authorable_type)
            ? $publication->authorable_type::find($publication->authorable_id)
            : null;

        return [
            'resit_exam_id' => $exam->id,
            'exam_name' => $exam->examType?->exam_name,
            'specialty_name' => $exam->schoolYear?->specialty?->specialty_name,
            'level_name' => $exam->schoolYear?->specialty?->level?->name,
            'school_year' => $exam->schoolYear?->systemAcademicYear?->name,
            'publication_status' => self::publicationStatus($exam),
            'published_at' => $publication?->published_at,
            'published_by' => $author ? ($author->name ?? trim(($author->first_name ?? '') . ' ' . ($author->last_name ?? '')) ?: null) : null,
            'scheduled_at' => $publication?->created_at,
            'total_candidates' => $progress['total'],
            'evaluated_candidates' => $progress['evaluated'],
            'not_evaluated_candidates' => $progress['not_evaluated'],
        ];
    }

    /** How far evaluation has got, with the students on each side. */
    public function getEvaluationProgress(object $currentSchool, string $resitExamId): array
    {
        $exam = $this->findExam($currentSchool, $resitExamId, ['resitCandidates.resitScores', 'resitCandidates.student']);
        $progress = self::progressCounts($exam);
        [$evaluated, $notEvaluated] = $exam->resitCandidates->partition(fn($candidate) => $candidate->resitScores->isNotEmpty());
        $percent = fn(int $count) => $progress['total'] > 0 ? round($count / $progress['total'] * 100, 2) : 0.0;
        $student = fn($candidate) => [
            'candidate_id' => $candidate->id,
            'student_id' => $candidate->student_id,
            'name' => $candidate->student?->name,
            'username' => $candidate->student?->username,
            'scores_recorded' => $candidate->resitScores->count(),
        ];

        return [
            'summary' => [
                'total_candidates' => $progress['total'],
                'total_evaluated' => $progress['evaluated'],
                'total_not_evaluated' => $progress['not_evaluated'],
                'evaluated_percentage' => $percent($progress['evaluated']),
                'not_evaluated_percentage' => $percent($progress['not_evaluated']),
            ],
            'evaluated_students' => $evaluated->map($student)->sortBy('name')->values(),
            'not_evaluated_students' => $notEvaluated->map($student)->sortBy('name')->values(),
        ];
    }

    /** { total, evaluated, not_evaluated }: a candidate is evaluated once any resit score is recorded. */
    public static function progressCounts(ResitExam $exam): array
    {
        $candidates = $exam->resitCandidates ?? collect();
        $evaluated = $candidates->filter(fn($candidate) => $candidate->resitScores?->isNotEmpty())->count();

        return ['total' => $candidates->count(), 'evaluated' => $evaluated, 'not_evaluated' => $candidates->count() - $evaluated];
    }

    /** published | scheduled | ready_to_publish | pending_evaluation */
    public static function publicationStatus(ResitExam $exam): string
    {
        $publishedAt = $exam->resultPublication?->published_at;
        if ($publishedAt) {
            return $publishedAt->lte(now()) ? 'published' : 'scheduled';
        }
        $progress = self::progressCounts($exam);

        return $progress['total'] > 0 && $progress['not_evaluated'] === 0 ? 'ready_to_publish' : 'pending_evaluation';
    }

    private function findExam(object $currentSchool, string $resitExamId, array $with): ResitExam
    {
        $exam = ResitExam::where('school_branch_id', $currentSchool->id)->with($with)->find($resitExamId);
        if (!$exam) {
            throw new AppException(
                "The requested resit exam could not be found for this school branch.",
                404,
                "Resit Exam Not Found",
                "We could not locate a resit exam matching the provided ID for your current school branch.",
                null
            );
        }

        return $exam;
    }
}
