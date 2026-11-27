<?php

namespace App\SchoolBranchSetting\Handlers\Level;

use App\Exceptions\AppException;
use App\Models\SchoolBranchSetting;
use App\SchoolBranchSetting\Contracts\SettingUpdateHandler;
use App\SchoolBranchSetting\Handlers\AutoCreation\AutoCreateLevelsHandler;
use Override;

/**
 * "Levels Offered by the School", "Levels for Automatic Specialty Creation"
 * and "Levels for Automatic Exam Creation": a list of level ids of the school's level system, with at
 * least one level (a school offers at least one level).
 */
class LevelListHandler implements SettingUpdateHandler
{
    #[Override]
    public function handle(
        SchoolBranchSetting $setting,
        mixed $value,
        object $currentSchool,
        mixed $authAdmin
    ): SchoolBranchSetting {
        if (is_array($value) && count(array_filter($value)) === 0) {
            throw new AppException(
                "At least one level must be selected.",
                400,
                "Select at Least One Level",
                "Choose at least one level. To stop creating records automatically, turn off the setting's main switch instead.",
                "value"
            );
        }

        // Same checks as the other level lists: ids of the school's level system.
        return app(AutoCreateLevelsHandler::class)->handle($setting, $value, $currentSchool, $authAdmin);
    }
}
