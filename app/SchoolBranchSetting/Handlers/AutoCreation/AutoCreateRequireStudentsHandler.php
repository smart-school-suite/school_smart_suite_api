<?php

namespace App\SchoolBranchSetting\Handlers\AutoCreation;

use App\Exceptions\AppException;
use App\Models\SchoolBranchSetting;
use App\SchoolBranchSetting\Contracts\SettingUpdateHandler;
use App\Services\AutoCreation\AutoCreationSettings as Settings;
use Override;

/**
 * Only Include Specialties with Enrolled Students, for a group. Like the
 * other dependent settings it can only be turned on while the group's
 * automatic creation is enabled; turning it off is always allowed.
 */
class AutoCreateRequireStudentsHandler implements SettingUpdateHandler
{
    use AutoCreationPrefix;

    #[Override]
    public function handle(
        SchoolBranchSetting $setting,
        mixed $value,
        object $currentSchool,
        mixed $authAdmin
    ): SchoolBranchSetting {
        $prefix = $this->prefixOf($setting);
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

        if ($filteredValue && !Settings::forBranch($currentSchool->id, $prefix)->enabled()) {
            throw new AppException(
                Settings::label($prefix) . " is disabled.",
                400,
                Settings::label($prefix) . " Disabled",
                "Enable " . Settings::label($prefix) . " before restricting it to specialties with enrolled students.",
                "value"
            );
        }

        $setting->update(['value' => $filteredValue]);

        return $setting->fresh();
    }
}
