<?php

namespace App\Services\PromotionRules;

use App\DTOs\Promotion\StudentRuleLogDTO;
use App\DTOs\Promotion\StudentRuleLogReasonDTO;
use Illuminate\Support\Collection;

class ExamScoreRuleService
{
    public  function incompleteExamScoresRule(
        Collection $scores,
        Collection $examTypes
    ): StudentRuleLogDTO {
        $scoresByExamType = $scores->groupBy(fn($score) => $score->exam?->exam_type_id);
        $missingExamTypes = [];

        foreach ($examTypes as $examType) {
            $typeScores = $scoresByExamType->get($examType->id, collect());
            $examTypeName = $examType->name ?? "Exam Type #{$examType->id}";

            if ($typeScores->isEmpty()) {
                $missingExamTypes[] = [
                    'exam_type_id' => $examType->id,
                    'name' => $examTypeName,
                ];
                continue;
            }

            // Verify there is at least one valid score item with positive credits
            $hasValidCredits = $typeScores->contains(
                fn($scoreItem) => ((float) ($scoreItem->course?->credit ?? 0)) > 0
            );

            if (!$hasValidCredits) {
                $missingExamTypes[] = [
                    'exam_type_id' => $examType->id,
                    'name' => $examTypeName,
                ];
            }
        }

        $hasAllExamTypes = empty($missingExamTypes);
        $reasons = [];

        if (!$hasAllExamTypes) {
            foreach ($missingExamTypes as $missing) {
                $reasons[] = new StudentRuleLogReasonDTO(
                    reason: sprintf("Missing or incomplete scores for required exam type: '%s'.", $missing['name']),
                    details: $missing
                );
            }
        } else {
            $reasons[] = new StudentRuleLogReasonDTO(
                reason: 'Student has submitted valid scores for all required exam types.',
                details: ['total_exam_types' => $examTypes->count()]
            );
        }

        return new StudentRuleLogDTO(
            rule: 'incomplete_exam_scores',
            passed: $hasAllExamTypes,
            reasons: $reasons,
            needsReview: !$hasAllExamTypes
        );
    }
}
