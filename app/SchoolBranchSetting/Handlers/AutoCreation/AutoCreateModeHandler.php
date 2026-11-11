<?php

namespace App\SchoolBranchSetting\Handlers\AutoCreation;

use App\Exceptions\AppException;
use App\Models\SchoolBranchSetting;
use App\SchoolBranchSetting\Contracts\SettingUpdateHandler;
use App\Services\AutoCreation\AutoCreationSettings as Settings;
use Illuminate\Support\Facades\DB;
use Override;

/**
 * Automatic Semester/Academic Year Creation: disabled, same_specialty or
 * across_specialties. Keeps the group's two "across" switches in step:
 * Within the Same Specialty turns both off, and Across Specialties turns
 * "Across Specialties" on when neither switch is on yet. Disabled leaves
 * them as they are (they are ignored until creation is enabled again).
 */
class AutoCreateModeHandler implements SettingUpdateHandler
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

        if (!is_string($value) || !in_array($value, Settings::MODES, true)) {
            throw new AppException(
                Settings::label($prefix) . " must be one of: " . implode(', ', Settings::MODES) . ".",
                400,
                "Invalid " . Settings::label($prefix) . " Option",
                "Choose Disabled, Within the Same Specialty or Across Specialties.",
                "value"
            );
        }

        DB::transaction(function () use ($setting, $value, $currentSchool, $prefix) {
            $setting->update(['value' => $value]);

            $switches = Settings::settings($currentSchool->id, $prefix, Settings::SWITCHES);

            if ($value === Settings::MODE_SAME_SPECIALTY) {
                $switches->each(fn($switch) => $switch->update(['value' => false]));
            }

            $noneOn = $switches->every(fn($switch) => !filter_var($switch->value, FILTER_VALIDATE_BOOLEAN));
            if ($value === Settings::MODE_ACROSS_SPECIALTIES && $noneOn) {
                $switches->get(Settings::ACROSS_SPECIALTIES)?->update(['value' => true]);
            }
        });

        return $setting->fresh();
    }
}
