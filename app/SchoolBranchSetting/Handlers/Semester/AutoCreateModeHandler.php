<?php

namespace App\SchoolBranchSetting\Handlers\Semester;

use App\Exceptions\AppException;
use App\Models\SchoolBranchSetting;
use App\SchoolBranchSetting\Contracts\SettingUpdateHandler;
use App\Services\Semester\SemesterAutoCreationSettings as Settings;
use Illuminate\Support\Facades\DB;
use Override;

/**
 * Automatic Semester Creation: disabled, same_specialty or across_specialties.
 * Keeps the two "across" switches in step: Within the Same Specialty turns
 * both off, and Across Specialties turns "Across Specialties" on when
 * neither switch is on yet. Disabled leaves them as they are (they are
 * ignored, and hidden, until creation is enabled again).
 */
class AutoCreateModeHandler implements SettingUpdateHandler
{
    #[Override]
    public function handle(
        SchoolBranchSetting $setting,
        mixed $value,
        object $currentSchool,
        mixed $authAdmin
    ): SchoolBranchSetting {
        if (!is_string($value) || !in_array($value, Settings::MODES, true)) {
            throw new AppException(
                "Automatic semester creation must be one of: " . implode(', ', Settings::MODES) . ".",
                400,
                "Invalid Automatic Semester Creation Option",
                "Choose Disabled, Within the Same Specialty or Across Specialties.",
                "value"
            );
        }

        DB::transaction(function () use ($setting, $value, $currentSchool) {
            $setting->update(['value' => $value]);

            $switches = Settings::settings($currentSchool->id, Settings::SWITCHES);

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
