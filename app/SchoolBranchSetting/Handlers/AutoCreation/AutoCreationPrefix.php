<?php

namespace App\SchoolBranchSetting\Handlers\AutoCreation;

use App\Models\SchoolBranchSetting;
use App\Services\AutoCreation\AutoCreationSettings;
use LogicException;

/** Resolves which automatic creation group (semester, academic year) a setting row belongs to. */
trait AutoCreationPrefix
{
    private function prefixOf(SchoolBranchSetting $setting): string
    {
        $setting->loadMissing('settingDefination');
        $prefix = AutoCreationSettings::prefixOf($setting->settingDefination?->key);

        if ($prefix === null) {
            throw new LogicException("Setting {$setting->id} is not an automatic creation setting.");
        }

        return $prefix;
    }
}
