<?php

namespace App\SchoolBranchSetting\Handlers\AutoCreation;

use App\Exceptions\AppException;
use App\Models\SchoolBranchSetting;
use App\SchoolBranchSetting\Contracts\SettingUpdateHandler;
use App\Services\AutoCreation\AutoCreationSettings as Settings;
use Illuminate\Support\Facades\DB;
use Override;

/**
 * "Across Specialties" and "Across All Specialties' Levels" of a group.
 * Independent switches: both may be on. They can only be turned on while
 * the group's automatic creation is enabled, and keep its mode in step:
 * turning one on selects Across Specialties, turning the last one off
 * selects Within the Same Specialty.
 */
class AutoCreateAcrossSwitchHandler implements SettingUpdateHandler
{
    use AutoCreationPrefix;

    #[Override]
    public function handle(
        SchoolBranchSetting $setting,
        mixed $value,
        object $currentSchool,
        mixed $authAdmin
    ): SchoolBranchSetting {
        $prefix = $this->prefixOf($setting);
        $filteredValue = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        if ($filteredValue === null) {
            throw new AppException(
                "This automatic creation setting must be a valid boolean.",
                400,
                "Invalid Automatic Creation Format",
                "This automatic creation setting must be a valid boolean.",
                "value"
            );
        }

        $settings = Settings::settings($currentSchool->id, $prefix);
        $mode = $settings->get(Settings::MODE);

        if ($filteredValue && (!$mode || $mode->value === Settings::MODE_DISABLED)) {
            throw new AppException(
                Settings::label($prefix) . " is disabled.",
                400,
                Settings::label($prefix) . " Disabled",
                "Enable " . Settings::label($prefix) . " before choosing how records extend to other specialties.",
                "value"
            );
        }

        DB::transaction(function () use ($setting, $filteredValue, $settings, $mode, $prefix) {
            $setting->update(['value' => $filteredValue]);

            if ($filteredValue) {
                $mode->update(['value' => Settings::MODE_ACROSS_SPECIALTIES]);
                return;
            }

            $name = substr($setting->settingDefination->key, strlen($prefix));
            $otherName = $name === Settings::ACROSS_SPECIALTIES ? Settings::ACROSS_ALL_LEVELS : Settings::ACROSS_SPECIALTIES;
            $otherOn = filter_var($settings->get($otherName)?->value, FILTER_VALIDATE_BOOLEAN);

            if (!$otherOn && $mode?->value === Settings::MODE_ACROSS_SPECIALTIES) {
                $mode->update(['value' => Settings::MODE_SAME_SPECIALTY]);
            }
        });

        return $setting->fresh();
    }
}
