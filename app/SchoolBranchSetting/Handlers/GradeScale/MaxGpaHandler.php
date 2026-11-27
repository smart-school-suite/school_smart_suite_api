<?php

namespace App\SchoolBranchSetting\Handlers\GradeScale;

use App\Exceptions\AppException;
use App\Models\GradeScale\SchoolGradeScale;
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

        // Grade points never exceed the maximum: it cannot drop below points in use.
        $highest = SchoolGradeScale::where('school_branch_id', $currentSchool->id)->max('grade_points');
        if ($highest !== null && (float) $highest > $value + 0.00001) {
            throw new AppException(
                "Grade points up to {$highest} are configured; the maximum GPA cannot be lower.",
                422,
                "Grade Points Above This Maximum",
                "Some of your grade scales give up to " . number_format((float) $highest, 2) . " grade points. Lower those grade points first, or choose a maximum of at least " . number_format((float) $highest, 2) . ".",
                "value"
            );
        }

        $passing = SchoolBranchSetting::where('school_branch_id', $currentSchool->id)
            ->whereHas('settingDefination', fn($query) => $query->where('key', 'grade.passing_gpa'))
            ->value('value');
        if (is_numeric($passing) && (float) $passing > $value) {
            throw new AppException(
                "The maximum GPA cannot be lower than the minimum passing GPA ({$passing}).",
                422,
                "Below the Passing GPA",
                "The Maximum Attainable GPA cannot be lower than the Minimum Passing GPA of " . number_format((float) $passing, 2) . ". Lower the passing GPA first.",
                "value"
            );
        }

        $setting->update(['value' => $value]);

        return $setting->fresh();
    }
}
