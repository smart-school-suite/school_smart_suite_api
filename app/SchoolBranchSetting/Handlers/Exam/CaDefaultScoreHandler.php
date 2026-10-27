<?php

namespace App\SchoolBranchSetting\Handlers\Exam;

use App\Exceptions\AppException;
use App\Models\SchoolBranchSetting;
use App\SchoolBranchSetting\Contracts\SettingUpdateHandler;
use Override;

class CaDefaultScoreHandler implements SettingUpdateHandler
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
                "The CA default score must be a valid number.",
                400,
                "Invalid CA Default Score Format",
                "The CA default score must be a valid number.",
                "value"
            );
        }

        $value = (float) $value;

        if ($value < 0) {
            throw new AppException(
                "The CA default score cannot be negative.",
                400,
                "Invalid CA Default Score Threshold",
                "The CA default score cannot be negative.",
                "value"
            );
        }

        $setting->update(['value' => $value]);

        return $setting->fresh();
    }
}
