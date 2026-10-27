<?php

namespace App\SchoolBranchSetting\Handlers\Promotion;

use App\Exceptions\AppException;
use App\Models\SchoolBranchSetting;
use App\SchoolBranchSetting\Contracts\SettingUpdateHandler;
use Override;

class ChargeStudentRegistrationFeeHandler implements SettingUpdateHandler
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
                "The student registration fee setting must be a valid boolean.",
                400,
                "Invalid Registration Fee Format",
                "The student registration fee setting must be a valid boolean.",
                "value"
            );
        }

        $setting->update(['value' => $filteredValue]);

        return $setting->fresh();
    }
}
