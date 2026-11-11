<?php

namespace Tests\Feature\Promotion;

use App\Models\SchoolBranchSetting;
use App\Services\Promotion\StudentPromotionRecordService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class NextPromotionSpecialtyTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_finds_the_same_specialty_at_the_next_level_in_the_same_department(): void
    {
        DB::table('setting_categories')->insert(['id' => 'cat', 'name' => 'Level Settings', 'key' => 'setting.category.level']);
        DB::table('setting_definations')->insert(['id' => 'def-start', 'name' => 'Start', 'key' => 'level.start', 'data_type' => 'string', 'setting_category_id' => 'cat']);
        SchoolBranchSetting::create(['school_branch_id' => 'branch-1', 'setting_defination_id' => 'def-start', 'value' => 'level_start_100']);
        DB::table('level_types')->insert(['id' => 'type-100', 'name' => '100-Level Start', 'program_name' => 'level_start_100', 'description' => '-']);
        foreach (['lvl-1' => 100, 'lvl-2' => 200] as $id => $level) {
            DB::table('levels')->insert(['id' => $id, 'name' => "Level $level", 'level' => $level, 'program_name' => $id, 'level_type_id' => 'type-100']);
        }
        foreach ([
            ['swe-1', 'computing', 'lvl-1'],
            ['other-dept-swe-2', 'business', 'lvl-2'],
            ['swe-2', 'computing', 'lvl-2'],
        ] as [$id, $department, $level]) {
            DB::table('specialties')->insert(['id' => $id, 'specialty_name' => 'Software Engineering', 'registration_fee' => 0, 'school_fee' => 0, 'status' => 'active', 'school_branch_id' => 'branch-1', 'department_id' => $department, 'level_id' => $level]);
        }

        $next = app(StudentPromotionRecordService::class)->getNextPromotionSpecialty((object) ['id' => 'branch-1'], 'swe-1');

        $this->assertSame('swe-2', $next?->id);
    }
}
