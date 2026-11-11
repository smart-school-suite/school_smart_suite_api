<?php

namespace App\SchoolBranchSetting\Handlers\Semester;

use App\Exceptions\AppException;
use App\Models\SchoolBranchSetting;
use App\SchoolBranchSetting\Contracts\SettingUpdateHandler;
use App\Services\Semester\SemesterAutoCreationSettings as Settings;
use Override;

/**
 * Only Include Specialties with Enrolled Students. Like the other dependent
 * settings it can only be turned on while automatic semester creation is
 * enabled; turning it off is always allowed.
 */
class AutoCreateRequireStudentsHandler implements SettingUpdateHandler
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
                "The enrolled students setting must be a valid boolean.",
                400,
                "Invalid Enrolled Students Setting Format",
                "The enrolled students setting must be a valid boolean.",
                "value"
            );
        }

        if ($filteredValue && !Settings::forBranch($currentSchool->id)->enabled()) {
            throw new AppException(
                "Automatic semester creation is disabled.",
                400,
                "Automatic Semester Creation Disabled",
                "Enable Automatic Semester Creation before restricting it to specialties with enrolled students.",
                "value"
            );
        }

        $setting->update(['value' => $filteredValue]);

        return $setting->fresh();
    }
}
