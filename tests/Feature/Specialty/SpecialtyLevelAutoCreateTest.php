<?php

namespace Tests\Feature\Specialty;

use App\Exceptions\AppException;
use App\Models\Specialty;
use App\Services\Specialty\SpecialtyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** "Automatically Create Specialties Across All Levels" (specialties.auto_generate_levels). */
class SpecialtyLevelAutoCreateTest extends TestCase
{
    use RefreshDatabase;

    private object $school;

    protected function setUp(): void
    {
        parent::setUp();
        $this->school = (object) ['id' => 'branch-1'];

        DB::table('level_types')->insert(['id' => 'type-100', 'name' => '100', 'program_name' => 'level_start_100', 'description' => '-']);
        DB::table('level_types')->insert(['id' => 'type-200', 'name' => '200', 'program_name' => 'level_start_200', 'description' => '-']);
        foreach (['lvl-1' => 100, 'lvl-2' => 200, 'lvl-3' => 300] as $id => $level) {
            DB::table('levels')->insert(['id' => $id, 'name' => "Level $level", 'level' => $level, 'program_name' => $id, 'level_type_id' => 'type-100']);
        }
        // A level of another level system, never auto-created here.
        DB::table('levels')->insert(['id' => 'other-2', 'name' => 'Year 2', 'level' => 200, 'program_name' => 'other-2', 'level_type_id' => 'type-200']);

        DB::table('setting_categories')->insert(['id' => 'cat', 'name' => 'Level Settings', 'key' => 'setting.category.level']);
        $this->setting('level.start', 'string', 'level_start_100');
        $this->setting('specialties.auto_generate_levels', 'boolean', true);
        $this->setting('level.registration_fees', 'json', [['level_id' => 'lvl-1', 'fee' => 10000], ['level_id' => 'lvl-2', 'fee' => 20000], ['level_id' => 'lvl-3', 'fee' => 30000]]);
        $this->setting('level.tuition_fees', 'json', [['level_id' => 'lvl-1', 'fee' => 100000], ['level_id' => 'lvl-2', 'fee' => 200000], ['level_id' => 'lvl-3', 'fee' => 300000]]);
    }

    private function setting(string $key, string $type, mixed $value, bool $branchRow = true): void
    {
        DB::table('setting_definations')->insert(['id' => "def-$key", 'name' => $key, 'key' => $key, 'data_type' => $type, 'setting_category_id' => 'cat', 'default_value' => json_encode($value)]);
        if ($branchRow) {
            DB::table('school_branch_settings')->insert(['id' => "set-$key", 'school_branch_id' => 'branch-1', 'setting_defination_id' => "def-$key", 'value' => json_encode($value)]);
        }
    }

    private function create(array $data): Specialty
    {
        return app(SpecialtyService::class)->createSpecialty($data + ['specialty_name' => 'Software Engineering', 'department_id' => 'dep'], $this->school, (object) ['id' => 'admin']);
    }

    /** [level_id => [registration_fee, school_fee]] of the Software Engineering specialties. */
    private function created(): array
    {
        return Specialty::where('specialty_name', 'Software Engineering')->orderBy('level_id')->get()
            ->mapWithKeys(fn($s) => [$s->level_id => [(float) $s->registration_fee, (float) $s->school_fee]])
            ->all();
    }

    public function test_creating_at_one_level_creates_it_at_every_other_level_with_that_levels_fees(): void
    {
        $specialty = $this->create(['level_id' => 'lvl-2', 'registration_fee' => 25000, 'school_fee' => 250000]);

        $this->assertSame([
            'lvl-1' => [10000.0, 100000.0],
            'lvl-2' => [25000.0, 250000.0],
            'lvl-3' => [30000.0, 300000.0],
        ], $this->created(), 'The chosen level keeps the fees given; the others take their own level fees.');
        $this->assertSame('lvl-2', $specialty->level_id);
        $this->assertSame(['Level 100', 'Level 300'], array_column($specialty->created_for_levels, 'name'));
    }

    public function test_the_chosen_level_takes_its_level_fees_when_none_are_given(): void
    {
        $this->create(['level_id' => 'lvl-1']);

        $this->assertSame([10000.0, 100000.0], $this->created()['lvl-1']);
    }

    public function test_levels_where_the_specialty_already_exists_are_left_alone(): void
    {
        DB::table('specialties')->insert(['id' => 'existing', 'specialty_name' => 'Software Engineering', 'registration_fee' => 1, 'school_fee' => 2, 'status' => 'active', 'school_branch_id' => 'branch-1', 'department_id' => 'dep', 'level_id' => 'lvl-1']);

        $specialty = $this->create(['level_id' => 'lvl-2']);

        $this->assertSame(['Level 300'], array_column($specialty->created_for_levels, 'name'));
        $this->assertSame([1.0, 2.0], $this->created()['lvl-1']);
        $this->assertSame(3, Specialty::count());
    }

    public function test_the_same_name_in_another_department_is_a_different_specialty(): void
    {
        DB::table('specialties')->insert(['id' => 'elsewhere', 'specialty_name' => 'Software Engineering', 'registration_fee' => 0, 'school_fee' => 0, 'status' => 'active', 'school_branch_id' => 'branch-1', 'department_id' => 'other-dep', 'level_id' => 'lvl-1']);

        $this->create(['level_id' => 'lvl-2']);

        $this->assertSame(3, Specialty::where('department_id', 'dep')->count());
    }

    public function test_only_the_chosen_level_is_created_when_the_setting_is_off(): void
    {
        DB::table('school_branch_settings')->where('id', 'set-specialties.auto_generate_levels')->update(['value' => json_encode(false)]);

        $specialty = $this->create(['level_id' => 'lvl-2']);

        $this->assertSame(['lvl-2'], array_keys($this->created()));
        $this->assertSame([], $specialty->created_for_levels);
    }

    public function test_a_branch_without_the_setting_uses_its_default(): void
    {
        DB::table('school_branch_settings')->where('id', 'set-specialties.auto_generate_levels')->delete();

        $this->create(['level_id' => 'lvl-3']);

        $this->assertSame(['lvl-1', 'lvl-2', 'lvl-3'], array_keys($this->created()), 'The setting defaults to on.');
    }

    public function test_a_level_outside_the_schools_level_system_is_refused(): void
    {
        // Specialties are only created at levels the school offers (level.allowed_levels).
        try {
            $this->create(['level_id' => 'other-2']);
            $this->fail('Expected the level to be refused.');
        } catch (AppException $exception) {
            $this->assertSame('Level Not Offered', $exception->getTitle());
        }

        $this->assertSame([], $this->created());
    }

    public function test_a_duplicate_at_the_chosen_level_creates_nothing(): void
    {
        DB::table('specialties')->insert(['id' => 'existing', 'specialty_name' => 'Software Engineering', 'registration_fee' => 0, 'school_fee' => 0, 'status' => 'active', 'school_branch_id' => 'branch-1', 'department_id' => 'dep', 'level_id' => 'lvl-2']);

        try {
            $this->create(['level_id' => 'lvl-2']);
            $this->fail('The duplicate was accepted.');
        } catch (AppException $e) {
            $this->assertSame(409, $e->getCode());
        }

        $this->assertSame(1, Specialty::count());
    }
}
