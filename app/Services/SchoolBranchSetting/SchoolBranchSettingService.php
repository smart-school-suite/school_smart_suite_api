<?php

namespace App\Services\SchoolBranchSetting;

use App\Models\SchoolBranchSetting;
use App\SchoolBranchSetting\Resolvers\SettingUpdateResolver;
use Illuminate\Support\Facades\DB;

class SchoolBranchSettingService
{
    public function __construct(
        private SettingUpdateResolver $settingUpdateResolver
    ) {}

    public function update(
        object $currentSchool,
        array $updateData,
        mixed $authAdmin
    ): SchoolBranchSetting {
        return DB::transaction(function () use (
            $currentSchool,
            $updateData,
            $authAdmin
        ) {
            $setting = SchoolBranchSetting::query()
                ->where('school_branch_id', $currentSchool->id)
                ->with('settingDefination')
                ->lockForUpdate()
                ->findOrFail($updateData['setting_id']);

            $settingKey = $setting->settingDefination->key;

            $handler = $this->settingUpdateResolver
                ->resolve($settingKey);

            return $handler->handle(
                $setting,
                $updateData['value'],
                $currentSchool,
                $authAdmin
            );
        });
    }
    public function getSchoolBranchSetting(object $currentSchool)
    {
        $schoolBranchSettings = SchoolBranchSetting::where("school_branch_id", $currentSchool->id)
            ->with(['settingDefination.settingCategory'])
            ->get();

        $groupedSettings = $schoolBranchSettings->groupBy(function ($item) {
            return $item->settingDefination->settingCategory->name ?? 'Uncategorized';
        })->map(function ($group, $categoryName) {
            return [
                'category_id' => $group->first()->settingDefination->settingCategory->id ?? null,
                'category_name' => $categoryName,
                'setting' => $group->map(function ($setting) {
                    return [
                        'id' => $setting->id,
                        'data_type' => $setting->settingDefination->data_type,
                        'key' => $setting->settingDefination->key,
                        'category_key' => $setting->settingDefination->settingCategory->key,
                        'value' => $setting->value,
                        'name' => $setting->settingDefination->name,
                        'description' => $setting->settingDefination->description,
                    ];
                })->values()->toArray()
            ];
        })->values()->toArray();

        return $groupedSettings;
    }

    public function getSchoolBranchSettingDetails(object $currentSchool, string $schoolBranchSettingId)
    {
        $schoolBranchSettings = SchoolBranchSetting::where("school_branch_id", $currentSchool->id)
            ->with(['settingDefination.settingCategory'])
            ->find($schoolBranchSettingId);
        return $schoolBranchSettings;
    }
}
