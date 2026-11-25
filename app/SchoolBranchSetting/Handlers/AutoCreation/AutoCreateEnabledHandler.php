<?php

namespace App\SchoolBranchSetting\Handlers\AutoCreation;

use App\Models\SchoolBranchSetting;
use App\SchoolBranchSetting\Contracts\SettingUpdateHandler;
use App\Services\AutoCreation\AutoCreationSettings as Settings;
use Illuminate\Support\Facades\DB;
use Override;

/**
 * The main switch of a group. Turning it on while none of the four scopes
 * is on selects the default scope, so an enabled group always has one.
 */
class AutoCreateEnabledHandler implements SettingUpdateHandler
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
        $enabled = $this->boolean($value);

        DB::transaction(function () use ($setting, $enabled, $currentSchool, $prefix) {
            $setting->update(['value' => $enabled]);

            if (!$enabled) {
                return;
            }

            $scopes = Settings::settings($currentSchool->id, $prefix, Settings::SCOPES);
            $noneOn = $scopes->every(fn($scope) => !filter_var($scope->value, FILTER_VALIDATE_BOOLEAN));

            if ($noneOn) {
                $scopes->get(Settings::DEFAULT_SCOPE)?->update(['value' => true]);
            }
        });

        return $setting->fresh();
    }
}
