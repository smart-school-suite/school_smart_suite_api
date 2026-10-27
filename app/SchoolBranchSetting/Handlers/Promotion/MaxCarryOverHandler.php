<?php

namespace App\SchoolBranchSetting\Handlers\Promotion;

use App\Exceptions\AppException;
use App\Models\SchoolBranchSetting;
use App\SchoolBranchSetting\Contracts\SettingUpdateHandler;
use Override;

class MaxCarryOverHandler implements SettingUpdateHandler
{
    #[Override]
    public function handle(
        SchoolBranchSetting $setting,
        mixed $value,
        object $currentSchool,
        mixed $authAdmin
    ): SchoolBranchSetting {
        if (!is_numeric($value) || (float) $value !== (float) (int) $value) {
            throw new AppException(
                "The maximum number of carry overs must be a valid integer.",
                400,
                "Invalid Carry Over Format",
                "The maximum number of carry overs must be a valid integer.",
                "value"
            );
        }

        $value = (int) $value;

        if ($value < 0) {
            throw new AppException(
                "The maximum number of carry overs cannot be negative.",
                400,
                "Invalid Carry Over Threshold",
                "The maximum number of carry overs cannot be negative.",
                "value"
            );
        }

        $setting->update(['value' => $value]);

        return $setting->fresh();
    }
}
