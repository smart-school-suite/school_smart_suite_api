<?php

namespace App\SchoolBranchSetting\Contracts;

use App\Models\SchoolBranchSetting;

interface SettingUpdateHandler
{
    public function handle(
        SchoolBranchSetting $setting,
        mixed $value,
        object $currentSchool,
        mixed $authAdmin
    ): SchoolBranchSetting;
}
