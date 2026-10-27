<?php

namespace App\SchoolBranchSetting\Handlers\Level;

use App\Exceptions\AppException;
use App\Models\LevelType;
use App\Models\SchoolBranchSetting;
use App\SchoolBranchSetting\Contracts\SettingUpdateHandler;
use Override;

class LevelStartHandler implements SettingUpdateHandler
{
    #[Override]
    public function handle(
        SchoolBranchSetting $setting,
        mixed $value,
        object $currentSchool,
        mixed $authAdmin
    ): SchoolBranchSetting {
        if (!is_string($value) || trim($value) === '') {
            throw new AppException(
                "The level start program name must be a valid string.",
                400,
                "Invalid Level Start Format",
                "The level start program name must be a valid string.",
                "value"
            );
        }

        $levelType = LevelType::where('program_name', $value)->first();

        if (!$levelType) {
            throw new AppException(
                "The specified program name does not exist.",
                400,
                "Level Type Not Found",
                "The specified program name does not exist.",
                "value"
            );
        }

        $setting->update([
            'value' => $levelType->program_name,
        ]);

        return $setting->fresh();
    }
}
