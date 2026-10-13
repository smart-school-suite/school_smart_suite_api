<?php

namespace App\Services\Exam;

use App\Models\Exam\Exam;
use App\Models\Exam\ExamResultPublication;
use App\Models\SchoolBranchSetting;
use App\Exceptions\AppException;

class ExamResultService
{
    public function getExams(object $currentSchool)
    {
        return Exam::where('school_branch_id', $currentSchool->id)
            ->with([
                'examType.semesters',
                'schoolYear.specialty.level',
                'examGradeScale',
                'schoolYear.systemAcademicYear',
                'examCandidate.examScores'
            ])
            ->get();
    }

    public function publishExamResult(object $currentSchool, object $authUser, array $payload)
    {
        $exam = Exam::where('school_branch_id', $currentSchool->id)
            ->where('id', $payload['exam_id'])
            ->first();

        if (!$exam) {
            throw new AppException(
                "The requested exam could not be found for this school branch.",
                404,
                "Exam Not Found",
                "We could not locate an exam matching the provided ID for your current school branch. Please verify the exam ID and try again.",
                null
            );
        }

        $publishedAt = $payload['status'] === 'scheduled'
            ? $payload['published_at']
            : now();

        return ExamResultPublication::updateOrCreate(
            [
                'exam_id'          => $exam->id,
                'school_branch_id' => $currentSchool->id,
            ],
            [
                'published_at'     => $publishedAt,
                'authorable_id'    => $authUser->id,
                'authorable_type'  => get_class($authUser),
            ]
        );
    }
    public function getExamEvaluationProgress(object $currentSchool, string $examId)
    {
        $exam = Exam::where("school_branch_id", $currentSchool->id)
            ->where("id", $examId)
            ->with(['examCandidate.examScores', 'examCandidate.student'])
            ->first();

        if (!$exam) {
            return [
                'summary' => [
                    'total_candidates'          => 0,
                    'total_evaluated'           => 0,
                    'total_not_evaluated'       => 0,
                    'evaluated_percentage'      => 0.00,
                    'not_evaluated_percentage'  => 0.00,
                ],
                'evaluated_students'     => [],
                'not_evaluated_students' => [],
            ];
        }

        $candidates = $exam->examCandidate;
        $totalCandidates = $candidates->count();

        $partitioned = $candidates->partition(function ($candidate) {
            return $candidate->examScores->isNotEmpty();
        });

        $evaluatedCandidates = $partitioned[0];
        $notEvaluatedCandidates = $partitioned[1];

        $totalEvaluated = $evaluatedCandidates->count();
        $totalNotEvaluated = $notEvaluatedCandidates->count();

        $evaluatedPercentage = $totalCandidates > 0
            ? round(($totalEvaluated / $totalCandidates) * 100, 2)
            : 0.00;

        $notEvaluatedPercentage = $totalCandidates > 0
            ? round(($totalNotEvaluated / $totalCandidates) * 100, 2)
            : 0.00;

        return [
            'summary' => [
                'total_candidates'          => $totalCandidates,
                'total_evaluated'           => $totalEvaluated,
                'total_not_evaluated'       => $totalNotEvaluated,
                'evaluated_percentage'      => $evaluatedPercentage,
                'not_evaluated_percentage'  => $notEvaluatedPercentage,
            ],
            'evaluated_students' => $evaluatedCandidates->pluck('student')->values(),
            'not_evaluated_students' => $notEvaluatedCandidates->pluck('student')->values(),
        ];
    }

    public function getExamStandings(object $currentSchool, string $examId)
    {
        $exam = Exam::where("school_branch_id", $currentSchool->id)
            ->where("id", $examId)
            ->with([
                'examCandidate.examScores.course',
                'examCandidate.student',
                'examCandidate.examScores.grade'
            ])
            ->first();

        if (!$exam) {
            return collect();
        }

        $maxGpa = (float) ($this->getMaxGpa($currentSchool->id) ?? 4.0);

        $standings = $exam->examCandidate->map(function ($candidate) {
            $totalWeightedPoints = 0;
            $totalCredits = 0;
            $totalRawScore = 0;
            $scoresCount = 0;

            foreach ($candidate->examScores as $score) {
                $credit = (float) ($score->course->credit ?? 1);
                $rawScore = (float) ($score->score ?? 0);
                $gradePoints = (float) ($score->grade->grade_points ?? 0);

                $totalWeightedPoints += ($gradePoints * $credit);
                $totalCredits += $credit;
                $totalRawScore += $rawScore;
                $scoresCount++;
            }

            $gpa = $totalCredits > 0 ? round($totalWeightedPoints / $totalCredits, 2) : 0.00;
            $averageScore = $scoresCount > 0 ? round($totalRawScore / $scoresCount, 2) : 0.00;

            return [
                'candidate_id'  => $candidate->id,
                'student'       => $candidate->student,
                'total_credits' => $totalCredits,
                'gpa'           => $gpa,
                'average_score' => $averageScore,
            ];
        });

        return $standings
            ->sortByDesc(fn($item) => [$item['gpa'], $item['average_score']])
            ->values()
            ->map(function ($item, $index) {
                $item['position'] = $index + 1;
                return $item;
            });
    }

    private function getMaxGpa(string $schoolBranchId)
    {
        return SchoolBranchSetting::where('school_branch_id', $schoolBranchId)
            ->whereHas('settingDefination', function ($query) {
                $query->where('key', 'grade.max_gpa');
            })
            ->value('value');
    }
}
