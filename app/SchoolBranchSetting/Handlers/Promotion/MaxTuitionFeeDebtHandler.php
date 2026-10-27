<?php

namespace App\SchoolBranchSetting\Handlers\Promotion;

use App\Exceptions\AppException;
use App\Models\SchoolBranchSetting;
use App\SchoolBranchSetting\Contracts\SettingUpdateHandler;
use Override;

class MaxTuitionFeeDebtHandler implements SettingUpdateHandler
{
    #[Override]
    public function handle(
        SchoolBranchSetting $setting,
        mixed $value,
        object $currentSchool,
        mixed $authAdmin
    ): SchoolBranchSetting {
        if (!is_numeric($value)) {
            throw new AppException(
                "The maximum tuition debt must be a valid number.",
                400,
                "Invalid Tuition Debt Format",
                "The maximum tuition debt must be a valid number.",
                "value"
            );
        }

        $value = (float) $value;

        if ($value < 0) {
            throw new AppException(
                "The maximum tuition debt cannot be negative.",
                400,
                "Invalid Tuition Debt Threshold",
                "The maximum tuition debt cannot be negative.",
                "value"
            );
        }

        $setting->update(['value' => $value]);

        return $setting->fresh();
    }
}
