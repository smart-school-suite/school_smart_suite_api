<?php

namespace App\SchoolBranchSetting\Handlers\Semester;

use App\Exceptions\AppException;
use App\Models\SchoolBranchSetting;
use App\SchoolBranchSetting\Contracts\SettingUpdateHandler;
use App\Services\Semester\SemesterAutoCreateSetting;
use Override;

/**
 * Handles both "auto create semester for other levels" settings. The two
 * modes are mutually exclusive: switching one on switches the other off.
 * Both may be off, which turns automatic creation off entirely.
 */
class AutoCreateSemesterHandler implements SettingUpdateHandler
{
    #[Override]
    public function handle(
        SchoolBranchSetting $setting,
        mixed $value,
        object $currentSchool,
        mixed $authAdmin
    ): SchoolBranchSetting {
        $filteredValue = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        if ($filteredValue === null) {
            throw new AppException(
                "The automatic semester creation setting must be a valid boolean.",
                400,
                "Invalid Automatic Semester Creation Format",
                "The automatic semester creation setting must be a valid boolean.",
                "value"
            );
        }

        if ($filteredValue) {
            $otherKey = $this->otherKey($setting->settingDefination?->key);

            if ($otherKey) {
                SchoolBranchSetting::where('school_branch_id', $currentSchool->id)
                    ->whereHas('settingDefination', fn($query) => $query->where('key', $otherKey))
                    ->update(['value' => json_encode(false)]);
            }
        }

        $setting->update(['value' => $filteredValue]);

        return $setting->fresh();
    }

    private function otherKey(?string $key): ?string
    {
        return match ($key) {
            SemesterAutoCreateSetting::ALL_LEVELS => SemesterAutoCreateSetting::LEVELS_WITH_STUDENTS,
            SemesterAutoCreateSetting::LEVELS_WITH_STUDENTS => SemesterAutoCreateSetting::ALL_LEVELS,
            default => null,
        };
    }
}
