<?php

namespace App\Services\PromotionRules;

use App\DTOs\Promotion\StudentRuleLogDTO;
use App\DTOs\Promotion\StudentRuleLogReasonDTO;
use Illuminate\Support\Collection;

class ResitRuleService
{
    public function carryOverRule(
        Collection $resits,
        Collection $promotionSettings
    ): StudentRuleLogDTO {
        $maxCarryOversSetting = $promotionSettings->first(
            fn($s) => $s->settingDefination?->key === "promotion.max_carry_overs"
        );
        $maxAllowedCarryOvers = $maxCarryOversSetting ? (int) $maxCarryOversSetting->value : 0;

        $carryOverCount = $resits->filter(fn($resit) => (bool) $resit->iscarry_over)->count();

        $hasPassed = $carryOverCount <= $maxAllowedCarryOvers;

        if ($hasPassed) {
            $message = $carryOverCount === 0
                ? 'Student passed resit promotion criteria with zero carry-overs.'
                : sprintf(
                    'Student passed resit promotion criteria with %d carry-over(s) (Maximum allowed: %d).',
                    $carryOverCount,
                    $maxAllowedCarryOvers
                );
        } else {
            $message = sprintf(
                'Student failed resit promotion criteria with %d carry-over(s), which exceeds the maximum limit of %d.',
                $carryOverCount,
                $maxAllowedCarryOvers
            );
        }

        $reasons = [
            new StudentRuleLogReasonDTO(
                reason: $message,
                details: [
                    'carry_over_count' => $carryOverCount,
                    'max_allowed_carry_overs' => $maxAllowedCarryOvers,
                ],
            ),
        ];

        return new StudentRuleLogDTO(
            rule: 'carry_over',
            passed: $hasPassed,
            reasons: $reasons,
        );
    }
}
