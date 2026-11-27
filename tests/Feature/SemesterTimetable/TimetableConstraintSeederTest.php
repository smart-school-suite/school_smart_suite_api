<?php

namespace Tests\Feature\SemesterTimetable;

use App\Models\Schooladmin;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\TimetableConstraintSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use ReflectionClass;
use Tests\TestCase;

/** The constraint endpoints returned [] because the constraints were never seeded on deploy. */
class TimetableConstraintSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_deploy_seeder_runs_the_constraint_seeder(): void
    {
        $source = file_get_contents((new ReflectionClass(DatabaseSeeder::class))->getFileName());

        $this->assertStringContainsString('$this->call(TimetableConstraintSeeder::class);', $source);
    }

    public function test_seeding_twice_fills_the_endpoints_without_duplicates(): void
    {
        (new TimetableConstraintSeeder())->run();
        $counts = $this->counts();
        (new TimetableConstraintSeeder())->run();

        $this->assertSame($counts, $this->counts(), 'A second run adds nothing.');
        $this->assertGreaterThan(0, $counts['sem_constraints']);
        $this->assertGreaterThan(0, $counts['sem_blockers']);
        $this->assertSame(0, DB::table('sem_constraints')->whereNull('constraint_type_id')->orWhereNull('constraint_category_id')->count(), 'Every constraint has its type and category.');
        $this->assertSame(0, DB::table('sem_blockers')->whereNull('sem_blocker_category_id')->count(), 'Every blocker has its category.');

        DB::table('school_branches')->insert(['id' => 'branch-1', 'school_id' => 'school-1', 'name' => 'Main', 'abbreviation' => 'MN']);
        DB::table('school_admins')->insert([
            'id' => 'admin-1', 'first_name' => 'Fon', 'last_name' => 'Nathalie', 'name' => 'Fon Nathalie',
            'email' => 'fon@example.com', 'password' => 'x', 'username' => 'fnathalie', 'school_branch_id' => 'branch-1',
        ]);
        Sanctum::actingAs(Schooladmin::find('admin-1'));

        $categories = $this->getJson('/api/v1/semester-timetable/constraints/category')->assertOk()->json('data');
        $this->assertNotEmpty($categories);
        $this->assertNotEmpty($categories[0]['constraints']);
        $this->assertNotEmpty($this->getJson('/api/v1/semester-timetable/constraints/all')->assertOk()->json('data'));
    }

    private function counts(): array
    {
        return collect(['constraint_types', 'constraint_categories', 'sem_constraints', 'sem_blockers', 'sem_blocker_categories'])
            ->mapWithKeys(fn($table) => [$table => DB::table($table)->count()])
            ->all();
    }
}
