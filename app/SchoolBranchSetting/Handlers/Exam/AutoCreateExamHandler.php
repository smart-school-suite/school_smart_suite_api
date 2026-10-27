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
                "The auto create exam setting must be a valid boolean.",
                400,
                "Invalid Auto Create Exam Format",
                "The auto create exam setting must be a valid boolean.",
                "value"
            );
        }

        $setting->update(['value' => $filteredValue]);

        return $setting->fresh();
    }
}
