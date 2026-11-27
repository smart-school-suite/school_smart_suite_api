<?php

namespace App\SchoolBranchSetting\Handlers\Exam;

use App\Exceptions\AppException;
use App\Models\SchoolBranchSetting;
use App\SchoolBranchSetting\Contracts\SettingUpdateHandler;
use Override;

class AutoCreateExamHandler implements SettingUpdateHandler
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
                "This setting must be on or off (true or false).",
                400,
                "Invalid On/Off Value",
                "This setting must be on or off (true or false).",
                "value"
            );
        }

        $setting->update(['value' => $filteredValue]);

        return $setting->fresh();
    }
}
