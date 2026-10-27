<?php

namespace App\SchoolBranchSetting\Handlers\Election;

use App\SchoolBranchSetting\Contracts\SettingUpdateHandler;
use App\Models\SchoolBranchSetting;
use App\Exceptions\AppException;
use Override;

class ElectionVoteTieHighestGpaHandler implements SettingUpdateHandler
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
                "The setting must be a valid boolean.",
                400,
                "Invalid setting Format",
                "The  setting must be a valid boolean.",
                "value"
            );
        }

        $setting->update(['value' => $filteredValue]);

        return $setting->fresh();
    }
}
