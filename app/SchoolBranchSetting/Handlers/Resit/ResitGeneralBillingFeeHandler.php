<?php

namespace App\SchoolBranchSetting\Handlers\Resit;

use App\Exceptions\AppException;
use App\Models\SchoolBranchSetting;
use App\SchoolBranchSetting\Contracts\SettingUpdateHandler;
use Override;

class ResitGeneralBillingFeeHandler implements SettingUpdateHandler
{
    #[Override]
    public function handle(
        SchoolBranchSetting $setting,
        mixed $value,
        object $currentSchool,
        mixed $authAdmin
    ): SchoolBranchSetting {
        if (!is_numeric($value)) {
            throw new AppException(
                "The general billing fee must be a valid number.",
                400,
                "Invalid General Billing Fee Format",
                "The general billing fee must be a valid number.",
                "value"
            );
        }

        $value = (float) $value;

        if ($value < 0) {
            throw new AppException(
                "The general billing fee cannot be negative.",
                400,
                "Invalid General Billing Fee Threshold",
                "The general billing fee cannot be negative.",
                "value"
            );
        }

        $setting->update(['value' => $value]);

        return $setting->fresh();
    }
}
