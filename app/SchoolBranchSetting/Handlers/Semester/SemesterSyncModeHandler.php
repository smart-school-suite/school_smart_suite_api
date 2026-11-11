<?php

namespace App\SchoolBranchSetting\Handlers\Semester;

use App\Exceptions\AppException;
use App\Models\SchoolBranchSetting;
use App\SchoolBranchSetting\Contracts\SettingUpdateHandler;
use App\Services\Semester\SemesterSyncSettings;
use Override;

class SemesterSyncModeHandler implements SettingUpdateHandler
{
    #[Override]
    public function handle(
        SchoolBranchSetting $setting,
        mixed $value,
        object $currentSchool,
        mixed $authAdmin
    ): SchoolBranchSetting {
        if (!is_string($value) || !in_array($value, SemesterSyncSettings::MODES, true)) {
            throw new AppException(
                "The semester synchronization mode must be one of: " . implode(', ', SemesterSyncSettings::MODES) . ".",
                400,
                "Invalid Semester Synchronization Mode",
                "Choose Disabled, Same Programme or Across Programmes.",
                "value"
            );
        }

        $setting->update(['value' => $value]);

        return $setting->fresh();
    }
}
