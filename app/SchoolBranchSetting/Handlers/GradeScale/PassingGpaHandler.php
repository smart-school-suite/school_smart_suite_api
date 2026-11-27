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

        $maxGpa = app(\App\Services\Grade\GradeSchemeSettings::class)->maxGpa($currentSchool->id);
        if ((float) $value > $maxGpa + 0.00001) {
            throw new AppException(
                "The passing GPA cannot exceed the maximum attainable GPA ({$maxGpa}).",
                422,
                "Above the Maximum GPA",
                "The Minimum Passing GPA cannot be higher than the Maximum Attainable GPA of " . number_format($maxGpa, 2) . ".",
                "value"
            );
        }

        $setting->update(['value' => $value]);

        return $setting->fresh();
    }
}
