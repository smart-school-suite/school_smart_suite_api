<?php

namespace App\SchoolBranchSetting\Handlers\AdditionalFee;

use App\Exceptions\AppException;
use App\Models\SchoolBranchSetting;
use App\SchoolBranchSetting\Contracts\SettingUpdateHandler;
use App\Services\AdditionalFee\AdditionalFeeDueDate;
use Override;

/** "Default Due Period" (additionalFee.default_due_days): whole days, 0 to MAX_DAYS. */
class DefaultDueDaysHandler implements SettingUpdateHandler
{
    #[Override]
    public function handle(
        SchoolBranchSetting $setting,
        mixed $value,
        object $currentSchool,
        mixed $authAdmin
    ): SchoolBranchSetting {
        if (!is_numeric($value) || (float) $value !== (float) (int) $value) {
            throw new AppException(
                "The default due period must be a whole number of days.",
                400,
                "Invalid Due Period",
                "Enter the number of days from the billing date to the due date, e.g. 10.",
                "value"
            );
        }

        $days = (int) $value;
        if ($days < 0 || $days > AdditionalFeeDueDate::MAX_DAYS) {
            throw new AppException(
                "The default due period must be between 0 and " . AdditionalFeeDueDate::MAX_DAYS . " days.",
                400,
                "Invalid Due Period",
                "Use 0 to make additional fees due on the day they are billed, up to " . AdditionalFeeDueDate::MAX_DAYS . " days.",
                "value"
            );
        }

        $setting->update(['value' => $days]);

        return $setting->fresh();
    }
}
