<?php

namespace App\SchoolBranchSetting\Handlers\GradeScale;

use App\Exceptions\AppException;
use App\Models\SchoolBranchSetting;
use App\SchoolBranchSetting\Contracts\SettingUpdateHandler;
use Override;

class PassingGpaHandler implements SettingUpdateHandler
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
                "The passing GPA must be a valid number.",
                400,
                "Invalid Passing GPA Format",
                "The passing GPA must be a valid number.",
                "value"
            );
        }

        $value = (float) $value;

        if ($value < 0) {
            throw new AppException(
                "The passing GPA cannot be negative.",
                400,
                "Invalid Passing GPA Threshold",
                "The passing GPA cannot be negative.",
                "value"
            );
        }

        $setting->update(['value' => $value]);

        return $setting->fresh();
    }
}
