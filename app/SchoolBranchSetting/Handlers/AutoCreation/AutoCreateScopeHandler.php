<?php

namespace App\SchoolBranchSetting\Handlers\AutoCreation;

use App\Exceptions\AppException;
use App\Models\SchoolBranchSetting;
use App\SchoolBranchSetting\Contracts\SettingUpdateHandler;
use App\Services\AutoCreation\AutoCreationSettings as Settings;
use Illuminate\Support\Facades\DB;
use Override;

/**
 * The four scopes of a group: Across All Levels, Across the Same Specialty,
 * Across All Levels in the Department and Across the Same Specialty in the
 * Department. Only one can be on: turning one on turns the other three off.
 * While the group is enabled, the scope that is on cannot simply be turned
 * off; another one is chosen instead.
 */
class AutoCreateScopeHandler implements SettingUpdateHandler
{
    use AutoCreationPrefix;
    use AutoCreationBoolean;

    #[Override]
    public function handle(
        SchoolBranchSetting $setting,
        mixed $value,
        object $currentSchool,
        mixed $authAdmin
    ): SchoolBranchSetting {
        $prefix = $this->prefixOf($setting);
        $on = $this->boolean($value);
        $label = Settings::label($prefix);
        $active = Settings::forBranch($currentSchool->id, $prefix)->active;

        if ($on && !$active) {
            throw new AppException(
                "{$label} is off.",
                400,
                "{$label} Is Off",
                "Turn on {$label} before choosing where records are created.",
                "value"
            );
        }

        if (!$on && $active && filter_var($setting->value, FILTER_VALIDATE_BOOLEAN)) {
            throw new AppException(
                "One option must stay on while {$label} is on.",
                400,
                "Pick Another Option",
                "Turn on another option instead, or turn off {$label}.",
                "value"
            );
        }

        DB::transaction(function () use ($setting, $on, $currentSchool, $prefix) {
            $setting->update(['value' => $on]);

            if (!$on) {
                return;
            }

            Settings::settings($currentSchool->id, $prefix, Settings::SCOPES)
                ->reject(fn($scope) => $scope->id === $setting->id)
                ->each(fn($scope) => $scope->update(['value' => false]));
        });

        return $setting->fresh();
    }
}
