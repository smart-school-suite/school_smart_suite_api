<?php

namespace App\SchoolBranchSetting\Handlers\Level;

use App\Exceptions\AppException;
use App\Models\Educationlevels;
use App\Models\SchoolBranchSetting;
use App\SchoolBranchSetting\Contracts\SettingUpdateHandler;
use Override;

class LevelRegistrationFeeHandler implements SettingUpdateHandler
{
    #[Override]
    public function handle(
        SchoolBranchSetting $setting,
        mixed $value,
        object $currentSchool,
        mixed $authAdmin
    ): SchoolBranchSetting {
        if (!is_array($value)) {
            throw new AppException(
                "The level registration fees must be provided as an array.",
                400,
                "Invalid Level Registration Fee Format",
                "The level registration fees must be provided as an array.",
                "value"
            );
        }

        $valueCollection = collect($value);
        $providedLevelIds = $valueCollection->pluck('level_id')->filter()->unique()->values();

        $levelSettings = $this->getSettingByKey($currentSchool->id, "level.start");

        if (!$levelSettings || !isset($levelSettings->value)) {
            throw new AppException(
                "School level configuration is missing.",
                400,
                "Configuration Error",
                "School level configuration is missing.",
                "value"
            );
        }

        $requiredLevels = Educationlevels::whereHas("levelType", function ($query) use ($levelSettings) {
            $query->where("program_name", $levelSettings->value);
        })->get();

        $requiredLevelIds = $requiredLevels->pluck('id');

        $missingLevels = $requiredLevelIds->diff($providedLevelIds);

        if ($missingLevels->isNotEmpty()) {
            throw new AppException(
                "All education levels must be configured with a registration fee.",
                400,
                "Incomplete Level Configuration",
                "All education levels must be configured with a registration fee.",
                "value"
            );
        }

        foreach ($valueCollection as $item) {
            if (!isset($item['fee']) || !is_numeric($item['fee']) || (float) $item['fee'] < 0) {
                throw new AppException(
                    "Each level must have a valid non-negative registration fee.",
                    400,
                    "Invalid Fee Value",
                    "Each level must have a valid non-negative registration fee.",
                    "value"
                );
            }
        }

        $setting->update(['value' => $value]);

        return $setting->fresh();
    }

    private function getSettingByKey(string $schoolId, string $key)
    {
        return SchoolBranchSetting::where("school_branch_id", $schoolId)
            ->whereHas('settingDefination', fn($query) => $query->where("key", $key))
            ->first();
    }
}
