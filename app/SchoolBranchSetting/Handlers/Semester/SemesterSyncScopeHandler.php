<?php

namespace App\SchoolBranchSetting\Handlers\Semester;

use App\Exceptions\AppException;
use App\Models\SchoolBranchSetting;
use App\SchoolBranchSetting\Contracts\SettingUpdateHandler;
use App\Services\Semester\SemesterSyncSettings;
use Override;

class SemesterSyncScopeHandler implements SettingUpdateHandler
{
    #[Override]
    public function handle(
        SchoolBranchSetting $setting,
        mixed $value,
        object $currentSchool,
        mixed $authAdmin
    ): SchoolBranchSetting {
        if (!is_string($value) || !in_array($value, SemesterSyncSettings::SCOPES, true)) {
            throw new AppException(
                "The semester synchronization scope must be one of: " . implode(', ', SemesterSyncSettings::SCOPES) . ".",
                400,
                "Invalid Semester Synchronization Scope",
                "Choose Same Specialty, Across Specialties or Entire Institution.",
                "value"
            );
        }

        $setting->update(['value' => $value]);

        return $setting->fresh();
    }
}
