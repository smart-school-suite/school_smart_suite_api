<?php

namespace App\Services\PromotionRules;

use App\DTOs\Promotion\StudentRuleLogDTO;
use App\DTOs\Promotion\StudentRuleLogReasonDTO;
use Illuminate\Support\Collection;

class EvaluationRuleService
{
    public function gpaRule(
        Collection $scores,
        Collection $promotionSettings,
        Collection $examTypes
    ): StudentRuleLogDTO {
        $promMinGpaSetting = $promotionSettings->first(
            fn($s) => $s->settingDefination?->key === 'promotion.min_gpa'
        );
        $promMinGpa = $promMinGpaSetting ? (float) $promMinGpaSetting->value : null;

        if ($promMinGpa === null) {
            return new StudentRuleLogDTO(
                rule: 'gpa',
                passed: false,
                reasons: [
                    new StudentRuleLogReasonDTO(
                        reason: 'Promotion minimum GPA setting is not defined.',
                        details: ['required_min_gpa' => null]
                    ),
                ],
                needsReview: true
            );
        }

        $scoresByExamType = $scores->groupBy(fn($score) => $score->exam?->exam_type_id);
        $gpaByExamType = [];
        $totalGpaSum = 0.0;
        $satExamTypesCount = 0;

        foreach ($examTypes as $examType) {
            $typeScores = $scoresByExamType->get($examType->id, collect());

            if ($typeScores->isEmpty()) {
                continue;
            }

            $totalWeightedPoints = 0.0;
            $totalCredits = 0;

            foreach ($typeScores as $scoreItem) {
                $credits = (float) ($scoreItem->course?->credit ?? 0);

                if ($credits <= 0) {
                    continue;
                }

                if ($scoreItem->grade?->grade_points !== null) {
                    $gradePoint = (float) $scoreItem->grade->grade_points;
                } else {
                    $obtainedScore = (float) ($scoreItem->score ?? 0);
                    $maxScore = (float) ($scoreItem->exam?->max_score ?? 0);

                    $gradePoint = $maxScore > 0
                        ? (($obtainedScore / $maxScore) * 100 / 100) * 4.0
                        : 0.0;
                }

                $totalWeightedPoints += $gradePoint * $credits;
                $totalCredits += $credits;
            }

            if ($totalCredits > 0) {
                $gpa = round($totalWeightedPoints / $totalCredits, 2);
                $gpaByExamType[$examType->id] = [
                    'name' => $examType->name ?? "Exam Type #{$examType->id}",
                    'gpa' => $gpa,
                    'total_credits' => $totalCredits,
                ];
                $totalGpaSum += $gpa;
                $satExamTypesCount++;
            }
        }

        if ($satExamTypesCount === 0) {
            return new StudentRuleLogDTO(
                rule: 'gpa',
                passed: false,
                reasons: [
                    new StudentRuleLogReasonDTO(
                        reason: 'Student has no valid exam scores or course credits to calculate GPA.',
                        details: [
                            'average_gpa' => 0.0,
                            'required_min_gpa' => $promMinGpa,
                        ]
                    ),
                ],
            );
        }

        $overallAverageGpa = round($totalGpaSum / $satExamTypesCount, 2);
        $hasPassed = $overallAverageGpa >= $promMinGpa;

        $reasonMessage = $hasPassed
            ? sprintf(
                'Achieved average GPA of %.2f across %d exam type(s) (Required minimum: %.2f).',
                $overallAverageGpa,
                $satExamTypesCount,
                $promMinGpa
            )
            : sprintf(
                'Achieved average GPA of %.2f, which is below the required minimum of %.2f.',
                $overallAverageGpa,
                $promMinGpa
            );

        $reasonDetails = [
            'average_gpa' => $overallAverageGpa,
            'required_min_gpa' => $promMinGpa,
            'exam_type_gpas' => $gpaByExamType,
        ];

        if (!$hasPassed) {
            $reasonDetails['gpa_deficit'] = round($promMinGpa - $overallAverageGpa, 2);
        }

        return new StudentRuleLogDTO(
            rule: 'gpa',
            passed: $hasPassed,
            reasons: [
                new StudentRuleLogReasonDTO(
                    reason: $reasonMessage,
                    details: $reasonDetails,
                ),
            ],
        );
    }
}
