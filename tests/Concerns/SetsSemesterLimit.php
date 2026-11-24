<?php

namespace Tests\Concerns;

use App\Models\SchoolBranchSetting;
use App\Services\Semester\SemesterLimit;
use Illuminate\Support\Facades\DB;

trait SetsSemesterLimit
{
    protected function setSemesterLimit(int $limit, string $branchId = 'branch-1'): void
    {
        if (!DB::table('setting_categories')->where('id', 'cat-sem-limit')->exists()) {
            DB::table('setting_categories')->insert(['id' => 'cat-sem-limit', 'name' => 'Semester Limit', 'key' => 'setting.category.semester.limit']);
            DB::table('setting_definations')->insert(['id' => 'def-sem-limit', 'name' => 'Number of Semesters', 'key' => SemesterLimit::KEY, 'data_type' => 'integer', 'default_value' => json_encode(2), 'setting_category_id' => 'cat-sem-limit']);
        }

        SchoolBranchSetting::updateOrCreate(
            ['school_branch_id' => $branchId, 'setting_defination_id' => 'def-sem-limit'],
            ['value' => $limit]
        );
    }
}
