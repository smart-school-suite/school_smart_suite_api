<?php
namespace App\SchoolBranchSetting\Handlers\Graduation;

use App\Exceptions\AppException;
use App\Models\SchoolBranchSetting;
use App\SchoolBranchSetting\Contracts\SettingUpdateHandler;
use Override;

class GraduationGeneralBillingFee implements SettingUpdateHandler
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
                "The graduation general billing fee must be a valid number.",
                400,
                "Invalid Graduation General Billing Fee Format",
                "The graduation general billing fee must be a valid number.",
                "value"
            );
        }

        $value = (float) $value;

        if ($value < 0) {
            throw new AppException(
                "The graduation general billing fee cannot be negative.",
                400,
                "Invalid Graduation General Billing Fee Threshold",
                "The graduation general billing fee cannot be negative.",
                "value"
            );
        }

        $setting->update(['value' => $value]);

        return $setting->fresh();
    }
}
