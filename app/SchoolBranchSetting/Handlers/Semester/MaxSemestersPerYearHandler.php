<?php

namespace App\SchoolBranchSetting\Handlers\Semester;

use App\Exceptions\AppException;
use App\Models\SchoolBranchSetting;
use App\SchoolBranchSetting\Contracts\SettingUpdateHandler;
use App\Services\Semester\SemesterLimit;
use Override;

class MaxSemestersPerYearHandler implements SettingUpdateHandler
{
    public function __construct(private SemesterLimit $limit) {}

    #[Override]
    public function handle(
        SchoolBranchSetting $setting,
        mixed $value,
        object $currentSchool,
        mixed $authAdmin
    ): SchoolBranchSetting {
        if (!is_numeric($value) || (float) $value !== (float) (int) $value) {
            throw new AppException(
                "The number of semesters must be a whole number.",
                400,
                "Invalid Number of Semesters",
                "Enter a whole number, like 2.",
                "value"
            );
        }

        $value = (int) $value;
        $available = $this->limit->available();

        if ($value < 1 || $value > $available) {
            throw new AppException(
                "The number of semesters must be between 1 and {$available}.",
                400,
                "Invalid Number of Semesters",
                "Pick a number between 1 and {$available}.",
                "value"
            );
        }

        $setting->update(['value' => $value]);

        return $setting->fresh();
    }
}
