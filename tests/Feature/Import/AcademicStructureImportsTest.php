<?php

namespace Tests\Feature\Import;

use App\Models\Hall;
use App\Models\Job\SystemJob;
use App\Models\Schooladmin;
use App\Models\Specialty;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/** Department, specialty, hall and specialty hall imports, end to end through their routes. */
class AcademicStructureImportsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('r2');

        DB::table('school_branches')->insert(['id' => 'branch-1', 'school_id' => 'school-1', 'name' => 'Main', 'abbreviation' => 'MN']);
        DB::table('school_admins')->insert([
            'id' => 'admin-1', 'first_name' => 'Fon', 'last_name' => 'Nathalie', 'name' => 'Fon Nathalie',
            'email' => 'fon@example.com', 'password' => 'x', 'username' => 'fnathalie', 'school_branch_id' => 'branch-1',
        ]);
        foreach (['lvl-1' => ['Level One', 200], 'lvl-2' => ['Level Two', 300]] as $id => [$name, $level]) {
            DB::table('levels')->insert(['id' => $id, 'name' => $name, 'level' => $level, 'program_name' => strtolower(str_replace(' ', '_', $name)), 'level_type_id' => 't']);
        }
        DB::table('departments')->insert(['id' => 'd-1', 'department_name' => 'Computing', 'school_branch_id' => 'branch-1']);
        DB::table('hall_types')->insert([
            ['id' => 'ht-1', 'name' => 'Lecture Hall', 'key' => 'lecture.hall'],
            ['id' => 'ht-2', 'name' => 'Exam Hall', 'key' => 'exam.hall'],
        ]);

        $admin = Schooladmin::find('admin-1');
        Sanctum::actingAs($admin);
        $this->actingAs($admin, 'schooladmin');
    }

    private function import(string $url, string $csv, array $standardFields, array $repeatableGroups = []): SystemJob
    {
        $response = $this->post($url, [
            'file' => UploadedFile::fake()->createWithContent('import.csv', $csv),
            'mapping' => ['standardFields' => $standardFields, 'repeatableGroups' => $repeatableGroups],
        ], ['Accept' => 'application/json'])->assertStatus(202);

        return SystemJob::findOrFail($response->json('data.id'));
    }

    private function summaries(SystemJob $job): array
    {
        return collect($job->result['error_groups'] ?? [])->pluck('summary')->all();
    }

    public function test_department_import(): void
    {
        $job = $this->import('/api/v1/department/import',
            "department_name,description\nBusiness,Business studies\ncomputing,Taken\nEngineering,\nBusiness,Again\n",
            ['department_name' => 'department_name', 'description' => 'description']);

        $this->assertSame('Department', $job->category->name);
        $this->assertSame(2, $job->successful_items);
        $this->assertTrue(DB::table('departments')->where('department_name', 'Engineering')->whereNull('description')->exists());
        $this->assertEqualsCanonicalizing([
            'Row 3: A department with this name already exists at this school.',
            'Row 5: Repeats a row higher up in the file; only the first one was imported.',
        ], $this->summaries($job));
    }

    public function test_specialty_import(): void
    {
        DB::table('specialties')->insert(['id' => 's-0', 'specialty_name' => 'Networking', 'registration_fee' => 0, 'school_fee' => 0, 'status' => 'active', 'school_branch_id' => 'branch-1', 'department_id' => 'd-1', 'level_id' => 'lvl-1']);

        $csv = "specialty_name,department_name,level,registration_fee,school_fee\n"
            . "Software Engineering,Computing,Level One,\"25,000\",150000\n"   // 2
            . "Software Engineering,computing,level two,,\n"                    // 3: fees from level (none: 0)
            . "Networking,Computing,Level One,0,0\n"                            // 4: exists
            . "Data Science,Science,Level One,0,0\n"                            // 5: no department
            . "Data Science,Computing,Level Nine,0,0\n"                         // 6: no level
            . "Cyber Security,Computing,Level One,free,0\n";                    // 7: invalid fee

        $job = $this->import('/api/v1/specialty/import', $csv, [
            'specialty_name' => 'specialty_name', 'department_name' => 'department_name', 'level' => 'level',
            'registration_fee' => 'registration_fee', 'school_fee' => 'school_fee',
        ]);

        $created = Specialty::where('specialty_name', 'Software Engineering')->orderBy('level_id')->get();
        $this->assertSame(['lvl-1', 'lvl-2'], $created->pluck('level_id')->all());
        $this->assertEquals(25000, $created[0]->registration_fee);
        $this->assertEquals(150000, $created[0]->school_fee);
        $this->assertSame(SystemJob::STATUS_COMPLETED_WITH_ISSUES, $job->status);
        $this->assertEqualsCanonicalizing([
            'Row 4: A specialty with this name already exists in this department at this level.',
            'Row 5: No department with this name at your school. Create the department first, or import departments before specialties.',
            "Row 6: Level not found in your school's level system. Use level names such as Level One, Level Two.",
            'Row 7: The registration fee field must be a number.',
        ], $this->summaries($job));
    }

    public function test_hall_import(): void
    {
        DB::table('halls')->insert(['id' => 'h-0', 'name' => 'Main Hall', 'capacity' => 100, 'school_branch_id' => 'branch-1']);

        $csv = "name,capacity,location,type_1,type_2\n"
            . "Hall A,120,Block A,Lecture Hall,exam\n"     // 2
            . "Hall B,60,,Lecture Hall|Exam Hall,\n"       // 3
            . "main hall,80,Block C,Lecture Hall,\n"       // 4: exists (names compare ignoring case)
            . "Hall C,lots,Block D,Lecture Hall,\n"        // 5: capacity
            . "Hall D,50,Block E,Gym,\n"                   // 6: type
            . "Hall E,50,Block F,,\n";                     // 7: no type

        $job = $this->import('/api/v1/hall/import', $csv,
            ['name' => 'name', 'capacity' => 'capacity', 'location' => 'location'],
            ['types' => [['type' => 'type_1'], ['type' => 'type_2']]]);

        $this->assertEqualsCanonicalizing(['ht-1', 'ht-2'], Hall::where('name', 'Hall A')->first()->types()->pluck('hall_types.id')->all());
        $this->assertEqualsCanonicalizing(['ht-1', 'ht-2'], Hall::where('name', 'Hall B')->first()->types()->pluck('hall_types.id')->all());
        $summaries = $this->summaries($job);
        $this->assertContains('Row 4: A hall with this name already exists at this school.', $summaries);
        $this->assertContains('Row 5: The capacity field must be an integer.', $summaries);
        $this->assertContains('Row 6: Hall type not found. Use Lecture Hall or Exam Hall.', $summaries);
        $this->assertContains('Row 7: At least one hall type is required (for example Lecture Hall or Exam Hall).', $summaries);
    }

    public function test_specialty_hall_import_assigns_halls_and_keeps_counts(): void
    {
        DB::table('specialties')->insert([
            ['id' => 's-1', 'specialty_name' => 'Software Engineering', 'registration_fee' => 0, 'school_fee' => 0, 'status' => 'active', 'school_branch_id' => 'branch-1', 'department_id' => 'd-1', 'level_id' => 'lvl-1'],
            ['id' => 's-2', 'specialty_name' => 'Software Engineering', 'registration_fee' => 0, 'school_fee' => 0, 'status' => 'active', 'school_branch_id' => 'branch-1', 'department_id' => 'd-1', 'level_id' => 'lvl-2'],
        ]);
        DB::table('halls')->insert([
            ['id' => 'h-1', 'name' => 'Hall A', 'capacity' => 100, 'school_branch_id' => 'branch-1'],
            ['id' => 'h-2', 'name' => 'Hall B', 'capacity' => 100, 'school_branch_id' => 'branch-1'],
        ]);

        $csv = "hall,specialty,level\n"
            . "Hall A,Software Engineering,Level One\n"   // 2
            . "Hall B,Software Engineering,Level One\n"   // 3
            . "Hall A,Software Engineering,Level One\n"   // 4: duplicate in file
            . "Hall A,Software Engineering,\n"            // 5: ambiguous
            . "Hall Z,Software Engineering,Level Two\n";  // 6: no hall

        $job = $this->import('/api/v1/specialty-hall/import', $csv, ['hall' => 'hall', 'specialty' => 'specialty', 'level' => 'level']);
        $again = $this->import('/api/v1/specialty-hall/import', "hall,specialty,level\nHall A,Software Engineering,Level One\n", ['hall' => 'hall', 'specialty' => 'specialty', 'level' => 'level']);

        $this->assertSame('specialty_hall_import', $job->type);
        $this->assertSame('Hall', $job->category->name);
        $this->assertSame(2, $job->successful_items);
        $this->assertSame(2, Specialty::find('s-1')->num_assigned_hall);
        $this->assertSame('assigned', Hall::find('h-1')->assignment_status);
        $this->assertEqualsCanonicalizing([
            'Row 4: Repeats a row higher up in the file; only the first one was imported.',
            'Row 5: Several specialties have this name at different levels. Add the level column to say which one.',
            'Row 6: No hall with this name at your school.',
        ], $this->summaries($job));
        $this->assertSame(['Row 2: The hall is already assigned to this specialty.'], $this->summaries($again));
    }
}
