<?php

namespace App\Services\School;

use App\Models\GradesCategory;
use App\Models\SchoolBranchSetting;
use App\Models\SchoolGradesConfig;
use App\Models\SettingDefination;
use Illuminate\Support\Str;

/** Gives a new school branch its settings and grade configurations. */
class SchoolBranchProvisioner
{
    public function provision(string $schoolBranchId): void
    {
        foreach (SettingDefination::all() as $definition) {
            SchoolBranchSetting::create([
                'school_branch_id' => $schoolBranchId,
                'setting_defination_id' => $definition->id,
                'value' => $definition->default_value,
            ]);
        }

        $configs = GradesCategory::all()->map(fn ($category) => [
            'id' => (string) Str::uuid(),
            'school_branch_id' => $schoolBranchId,
            'grades_category_id' => $category->id,
            'created_at' => now(),
            'updated_at' => now(),
        ])->all();

        if ($configs) {
            SchoolGradesConfig::insert($configs);
        }
    }
}
