<?php

namespace App\SchoolBranchSetting\Handlers\GradeScale;

use App\Exceptions\AppException;
use App\Models\SchoolBranchSetting;
use App\SchoolBranchSetting\Contracts\SettingUpdateHandler;
use Override;

class MaxGpaHandler implements SettingUpdateHandler
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
                "The maximum GPA must be a valid number.",
                400,
                "Invalid Maximum GPA Format",
                "The maximum GPA must be a valid number.",
                "value"
            );
        }

        $value = (float) $value;

        if ($value < 0) {
            throw new AppException(
                "The maximum GPA cannot be negative.",
                400,
                "Invalid Maximum GPA Threshold",
                "The maximum GPA cannot be negative.",
                "value"
            );
        }

        $setting->update(['value' => $value]);

        return $setting->fresh();
    }
}
