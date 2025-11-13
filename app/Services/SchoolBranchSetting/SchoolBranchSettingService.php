<?php

namespace App\Services\SchoolBranchSetting;

use App\Models\SchoolBranchSetting;

class SchoolBranchSettingService
{
    public function getSchoolBranchSetting($currentSchool)
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

    public function getSchoolBranchSettingDetails($currentSchool, $schoolBranchSettingId){
         $schoolBranchSettings = SchoolBranchSetting::where("school_branch_id", $currentSchool->id)
            ->with(['settingDefination.settingCategory'])
            ->find($schoolBranchSettingId);
        return $schoolBranchSettings;
    }
}
