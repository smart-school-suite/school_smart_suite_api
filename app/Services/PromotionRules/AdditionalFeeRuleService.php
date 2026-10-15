<?php

namespace App\Services\PromotionRules;

use App\DTOs\Promotion\StudentRuleLogDTO;
use App\DTOs\Promotion\StudentRuleLogReasonDTO;
use Illuminate\Support\Collection;

class AdditionalFeeRuleService
{
    public function additionalFeeRule(
        Collection $additionalFees,
        Collection $promotionSettings
    ): StudentRuleLogDTO {
        $maxDebtSetting = $promotionSettings->first(
            fn($s) => $s->settingDefination?->key === 'promotion.max_additional_fee_debt'
        );

        $maxAllowedDebt = $maxDebtSetting ? (float) $maxDebtSetting->value : 0.0;

        $totalDebt = 0.0;
        $unpaidFeeDetails = [];

        foreach ($additionalFees as $fee) {
            $requiredAmount = (float) ($fee->amount ?? 0);
            $totalPaid = (float) $fee->additionalFeeTranctions->sum('amount');
            $remainingBalance = max(0.0, $requiredAmount - $totalPaid);

            if ($remainingBalance > 0.0) {
                $totalDebt += $remainingBalance;

                $feeName = $fee->feeCategory?->name
                    ?? $fee->reason
                    ?? "Fee #{$fee->id}";

                $failureReason = $totalPaid > 0
                    ? sprintf(
                        "Partially paid: '%s' requires %.2f, but only %.2f was paid (Outstanding: %.2f).",
                        $feeName,
                        $requiredAmount,
                        $totalPaid,
                        $remainingBalance
                    )
                    : sprintf(
                        "Unpaid: '%s' requires %.2f (No payment recorded).",
                        $feeName,
                        $requiredAmount
                    );

                $unpaidFeeDetails[] = [
                    'fee_id' => $fee->id,
                    'category' => $feeName,
                    'reason' => $fee->reason ?? 'Unspecified',
                    'required_amount' => $requiredAmount,
                    'paid_amount' => $totalPaid,
                    'balance' => $remainingBalance,
                    'status' => $totalPaid > 0 ? 'partially_paid' : 'unpaid',
                    'explanation' => $failureReason,
                ];
            }
        }

        $hasPassed = $totalDebt <= $maxAllowedDebt;
        $reasons = [];

        if ($hasPassed) {
            $message = $totalDebt === 0.0
                ? 'All additional fees have been settled in full.'
                : sprintf(
                    'Outstanding additional fee debt of %.2f across %d fee(s) is within the allowed limit of %.2f.',
                    $totalDebt,
                    count($unpaidFeeDetails),
                    $maxAllowedDebt
                );

            $reasons[] = new StudentRuleLogReasonDTO(
                reason: $message,
                details: [
                    'total_debt' => $totalDebt,
                    'max_allowed_debt' => $maxAllowedDebt,
                ],
            );
        } else {
            $excessDebt = $totalDebt - $maxAllowedDebt;

            $reasons[] = new StudentRuleLogReasonDTO(
                reason: sprintf(
                    'Total outstanding additional fee debt of %.2f exceeds the allowed limit of %.2f by %.2f.',
                    $totalDebt,
                    $maxAllowedDebt,
                    $excessDebt
                ),
                details: [
                    'total_debt' => $totalDebt,
                    'max_allowed_debt' => $maxAllowedDebt,
                    'excess_debt' => $excessDebt,
                ],
            );

            foreach ($unpaidFeeDetails as $unpaid) {
                $reasons[] = new StudentRuleLogReasonDTO(
                    reason: $unpaid['explanation'],
                    details: $unpaid,
                );
            }
        }

        return new StudentRuleLogDTO(
            rule: 'additional_fee',
            passed: $hasPassed,
            reasons: $reasons,
        );
    }
}
