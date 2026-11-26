<?php

namespace Tests\Feature\Level;

use App\Exceptions\AppException;
use App\Models\Schooladmin;
use App\Models\Specialty;
use App\Services\Specialty\SpecialtyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * "Levels Offered by the School" (level.allowed_levels) and "Levels for
 * Automatic Specialty Creation" (specialties.auto_generate_for_levels).
 */
class OfferedLevelsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('school_branches')->insert(['id' => 'branch-1', 'school_id' => 'school-1', 'name' => 'Main', 'abbreviation' => 'MN']);
        DB::table('school_admins')->insert([
            'id' => 'admin-1', 'first_name' => 'Fon', 'last_name' => 'Nathalie', 'name' => 'Fon Nathalie',
            'email' => 'fon@example.com', 'password' => 'x', 'username' => 'fnathalie', 'school_branch_id' => 'branch-1',
        ]);
        DB::table('level_types')->insert([
            ['id' => 'type-100', 'name' => '100', 'program_name' => 'level_start_100', 'description' => '-'],
            ['id' => 'type-200', 'name' => '200', 'program_name' => 'level_start_200', 'description' => '-'],
        ]);
        foreach (['lvl-1' => 100, 'lvl-2' => 200, 'lvl-3' => 300, 'lvl-4' => 400] as $id => $level) {
            DB::table('levels')->insert(['id' => $id, 'name' => "Level $level", 'level' => $level, 'program_name' => $id, 'level_type_id' => 'type-100']);
        }
        DB::table('levels')->insert(['id' => 'other-1', 'name' => 'Year 1', 'level' => 200, 'program_name' => 'other-1', 'level_type_id' => 'type-200']);

        DB::table('setting_categories')->insert(['id' => 'cat', 'name' => 'Level Settings', 'key' => 'setting.category.level']);
        $this->setting('level.start', 'string', 'level_start_100');
        $this->setting('level.allowed_levels', 'json', ['lvl-1', 'lvl-2', 'lvl-3']);
        $this->setting('specialties.auto_generate_levels', 'boolean', true);
        $this->setting('specialties.auto_generate_for_levels', 'json', ['lvl-1', 'lvl-2', 'lvl-3', 'lvl-4']);

        $admin = Schooladmin::find('admin-1');
        Sanctum::actingAs($admin);
        $this->actingAs($admin, 'schooladmin');
    }

    private function setting(string $key, string $type, mixed $value): void
    {
        DB::table('setting_definations')->insert(['id' => "def-$key", 'name' => $key, 'key' => $key, 'data_type' => $type, 'setting_category_id' => 'cat', 'default_value' => json_encode($value)]);
        DB::table('school_branch_settings')->insert(['id' => "set-$key", 'school_branch_id' => 'branch-1', 'setting_defination_id' => "def-$key", 'value' => json_encode($value)]);
    }

    private function update(string $key, mixed $value)
    {
        return $this->patchJson('/api/v1/school-branch-setting/update', ['setting_id' => "set-$key", 'value' => $value]);
    }

    private function levelIds(string $query = ''): array
    {
        return collect($this->getJson("/api/v1/level{$query}")->assertOk()->json('data'))->pluck('id')->all();
    }

    private function createSpecialty(string $levelId): Specialty
    {
        return app(SpecialtyService::class)->createSpecialty(
            ['specialty_name' => 'Software Engineering', 'department_id' => 'dep', 'level_id' => $levelId],
            (object) ['id' => 'branch-1'],
            (object) ['id' => 'admin-1']
        );
    }

    public function test_the_level_list_shows_the_offered_levels_and_the_picker_sees_the_whole_system(): void
    {
        $this->assertEqualsCanonicalizing(['lvl-1', 'lvl-2', 'lvl-3'], $this->levelIds());
        $this->assertEqualsCanonicalizing(['lvl-1', 'lvl-2', 'lvl-3', 'lvl-4'], $this->levelIds('?scope=system'));
    }

    public function test_the_offered_levels_can_be_changed_but_not_emptied_or_set_outside_the_system(): void
    {
        $this->update('level.allowed_levels', ['lvl-2', 'lvl-4'])->assertOk();
        $this->assertEqualsCanonicalizing(['lvl-2', 'lvl-4'], $this->levelIds());

        $this->update('level.allowed_levels', [])->assertStatus(400)->assertJsonPath('errors.title', 'Select at Least One Level');
        $this->update('level.allowed_levels', ['other-1'])->assertStatus(400);
        $this->update('specialties.auto_generate_for_levels', [])->assertStatus(400);
    }

    public function test_a_specialty_cannot_be_created_at_a_level_the_school_does_not_offer(): void
    {
        try {
            $this->createSpecialty('lvl-4');
            $this->fail('Expected the level to be refused.');
        } catch (AppException $exception) {
            $this->assertSame('Level Not Offered', $exception->getTitle());
        }
        $this->assertSame(0, Specialty::count());
    }

    public function test_automatic_creation_uses_only_the_chosen_offered_levels(): void
    {
        $this->update('specialties.auto_generate_for_levels', ['lvl-1', 'lvl-4'])->assertOk();

        $this->createSpecialty('lvl-2');

        // lvl-2 (chosen level) + lvl-1 (chosen for auto creation); lvl-3 not chosen, lvl-4 not offered.
        $this->assertEqualsCanonicalizing(['lvl-1', 'lvl-2'], Specialty::pluck('level_id')->all());
    }

    public function test_a_saved_list_from_another_level_system_falls_back_to_all_levels(): void
    {
        DB::table('school_branch_settings')->where('id', 'set-level.allowed_levels')->update(['value' => json_encode(['other-1'])]);

        $this->assertEqualsCanonicalizing(['lvl-1', 'lvl-2', 'lvl-3', 'lvl-4'], $this->levelIds());
    }
}
