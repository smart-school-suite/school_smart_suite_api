<?php

namespace App\SchoolBranchSetting\Handlers\Resit;

use App\Exceptions\AppException;
use App\Models\SchoolBranchSetting;
use App\SchoolBranchSetting\Contracts\SettingUpdateHandler;
use Override;

class ResitGeneralBillingHandler implements SettingUpdateHandler
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
                "The general billing setting must be a valid boolean.",
                400,
                "Invalid General Billing Format",
                "The general billing setting must be a valid boolean.",
                "value"
            );
        }

        $setting->update(['value' => $filteredValue]);

        $levelBilling = $this->getSettingByKey($currentSchool->id, "resitFee.levelBilling");

        if ($levelBilling) {
            $levelBilling->update(['value' => ! $filteredValue]);
        }

        return $setting->fresh();
    }

    private function getSettingByKey(string $schoolId, string $key)
    {
        return SchoolBranchSetting::where("school_branch_id", $schoolId)
            ->whereHas('settingDefination', fn($query) => $query->where("key", $key))
            ->first();
    }
}
