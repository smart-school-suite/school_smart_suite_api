<?php

namespace App\Services\ResitExam;

use App\Models\ResitCandidates;
use App\Exceptions\AppException;
use App\Models\ResitMarks;
use Illuminate\Support\Facades\DB;

class ResitScoreService
{
    public function getResitScoresCandidateId(string $candidateId, object $currentSchool): array
    {
        $candidate = ResitCandidates::where("school_branch_id", $currentSchool->id)
            ->with([
                'student',
                'resitExam',
                'resitScores.resit' => function ($query) {
                    $query->withTrashed();
                },
                'resitScores.resit.courses.types',
                'resitScores.grade.grade'
            ])
            ->find($candidateId);

        if (!$candidate) {
            throw new AppException(
                "The requested exam candidate record could not be found.",
                404,
                "Candidate Record Not Found",
                "The requested exam candidate record could not be found for this school branch.",
                "/candidates"
            );
        }

        $scores = $candidate->resitScores;

        if ($scores->isEmpty()) {
            throw new AppException(
                "No marks found for this candidate.",
                404,
                "No Marks Found",
                "There are no marks available for the selected candidate. They may not be evaluated yet or the records were removed.",
                "/candidates"
            );
        }

        $totalScore = 0;
        $totalGradePoints = 0;
        $coursesPassed = 0;
        $coursesFailed = 0;
        $totalCourses = $scores->count();

        $formattedScores = $scores->map(function ($scoreItem) use (&$totalScore, &$totalGradePoints, &$coursesPassed, &$coursesFailed) {
            $scoreValue = (float) $scoreItem->score;
            $totalScore += $scoreValue;

            $gradeScale = $scoreItem->grade;
            $gradePoints = $gradeScale ? (float) $gradeScale->grade_points : 0.0;
            $totalGradePoints += $gradePoints;

            $resultStatus = $gradeScale ? strtolower($gradeScale->result ?? $gradeScale->status ?? '') : null;
            if ($resultStatus === 'pass' || $resultStatus === 'passed') {
                $coursesPassed++;
            } elseif ($resultStatus === 'fail' || $resultStatus === 'failed') {
                $coursesFailed++;
            }

            return [
                'id' => $scoreItem->id,
                'course' => $scoreItem->resit?->courses,
                'score' => $scoreValue,
                'grade' => $gradeScale ? [
                    'id' => $gradeScale->id,
                    'letter_grade' => $gradeScale->grade?->letter_grade,
                    'grade_points' => $gradePoints,
                    'result' => $gradeScale->result,
                    'performance' => $gradeScale->performance,
                ] : null,
            ];
        });

        $gpa = $totalCourses > 0 ? round($totalGradePoints / $totalCourses, 2) : 0.0;

        return [
            "resit_exam" => $candidate->resitExam,
            "candidate" => $candidate,
            "scores" => $formattedScores,
            "summary" => [
                "gpa" => $gpa,
                "courses_passed" => $coursesPassed,
                "courses_failed" => $coursesFailed,
                "total_grade_points_earned" => round($totalGradePoints, 2),
                "total_score" => round($totalScore, 2)
            ]
        ];
    }
    public function deleteResitScoresCandidateId(string $candidateId, object $currentSchool)
    {
        return DB::transaction(function () use ($candidateId, $currentSchool) {
            $candidate = ResitCandidates::where("school_branch_id", $currentSchool->id)
                ->with(['resitScores.resit' => fn($query) => $query->withTrashed()])
                ->find($candidateId);

            if (!$candidate) {
                throw new AppException(
                    "Candidate not found.",
                    404,
                    "Candidate Not Found",
                    "The requested exam candidate record could not be found for this school branch.",
                    "/candidates"
                );
            }

            if ($candidate->resitScores->isEmpty()) {
                throw new AppException(
                    "No marks found for this candidate.",
                    404,
                    "No Marks Found",
                    "There are no marks available to delete for the selected candidate.",
                    "/candidates"
                );
            }

            foreach ($candidate->resitScores as $resitMark) {
                $resit = $resitMark->resit;

                if ($resit) {
                    if ($resit->trashed()) {
                        $resit->restore();
                    }

                    $resit->update([
                        'payment_status' => 'unpaid',
                        'updated_at' => now(),
                    ]);
                }
            }

            $deletedCount = ResitMarks::where('candidate_id', $candidateId)
                ->where('school_branch_id', $currentSchool->id)
                ->delete();

            return [
                "message" => "Successfully deleted all exam scores for the candidate.",
                "deleted_count" => $deletedCount
            ];
        });
    }
}
