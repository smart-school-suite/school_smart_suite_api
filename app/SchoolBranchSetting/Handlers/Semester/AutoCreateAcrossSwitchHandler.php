<?php

namespace App\SchoolBranchSetting\Handlers\Semester;

use App\Exceptions\AppException;
use App\Models\SchoolBranchSetting;
use App\SchoolBranchSetting\Contracts\SettingUpdateHandler;
use App\Services\Semester\SemesterAutoCreationSettings as Settings;
use Illuminate\Support\Facades\DB;
use Override;

/**
 * "Across Specialties" and "Across All Specialties' Levels". Independent
 * switches: both may be on. They can only be turned on while automatic
 * semester creation is enabled, and keep the mode in step: turning one on
 * selects Across Specialties, turning the last one off selects Within the
 * Same Specialty.
 */
class AutoCreateAcrossSwitchHandler implements SettingUpdateHandler
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
                "This automatic semester creation setting must be a valid boolean.",
                400,
                "Invalid Automatic Semester Creation Format",
                "This automatic semester creation setting must be a valid boolean.",
                "value"
            );
        }

        $settings = Settings::settings($currentSchool->id);
        $mode = $settings->get(Settings::MODE);

        if ($filteredValue && (!$mode || $mode->value === Settings::MODE_DISABLED)) {
            throw new AppException(
                "Automatic semester creation is disabled.",
                400,
                "Automatic Semester Creation Disabled",
                "Enable Automatic Semester Creation before choosing how semesters extend to other specialties.",
                "value"
            );
        }

        DB::transaction(function () use ($setting, $filteredValue, $settings, $mode) {
            $setting->update(['value' => $filteredValue]);

            if ($filteredValue) {
                $mode->update(['value' => Settings::MODE_ACROSS_SPECIALTIES]);
                return;
            }

            $key = $setting->settingDefination?->key;
            $otherKey = $key === Settings::ACROSS_SPECIALTIES ? Settings::ACROSS_ALL_LEVELS : Settings::ACROSS_SPECIALTIES;
            $otherOn = filter_var($settings->get($otherKey)?->value, FILTER_VALIDATE_BOOLEAN);

            if (!$otherOn && $mode?->value === Settings::MODE_ACROSS_SPECIALTIES) {
                $mode->update(['value' => Settings::MODE_SAME_SPECIALTY]);
            }
        });

        return $setting->fresh();
    }
}
