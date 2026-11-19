<?php

namespace Tests\Feature\Teacher;

use App\Exceptions\AppException;
use App\Models\Teacher;
use App\Services\Auth\Teacher\CreateTeacherService;
use App\Services\Teacher\TeacherService;
use App\Services\Teacher\TeacherSpecialtyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Database\Seeders\RoleSeeder;
use Tests\TestCase;

/** A teacher's allowed teaching levels decide which specialties they can be assigned to. */
class TeacherLevelsTest extends TestCase
{
    use RefreshDatabase;

    private object $school;

    protected function setUp(): void
    {
        parent::setUp();
        $this->school = (object) ['id' => 'branch-1'];
        (new RoleSeeder())->run();

        foreach (['lvl-1' => 100, 'lvl-2' => 200] as $id => $level) {
            DB::table('levels')->insert(['id' => $id, 'name' => "Level $level", 'level' => $level, 'program_name' => $id, 'level_type_id' => 't']);
        }
        DB::table('specialties')->insert(['id' => 'swe-2', 'specialty_name' => 'Software Engineering', 'registration_fee' => 0, 'school_fee' => 0, 'status' => 'active', 'school_branch_id' => 'branch-1', 'department_id' => 'd', 'level_id' => 'lvl-2']);
        DB::table('qualifications')->insert(['id' => 'q-1', 'name' => 'Master of Science', 'abbreviation' => 'MSc', 'level' => 'masters', 'status' => 'active']);
    }

    private function createTeacher(array $levels): Teacher
    {
        return app(CreateTeacherService::class)->createInstructor([
            'name' => 'Ada Lovelace', 'first_name' => 'Ada', 'last_name' => 'Lovelace', 'email' => 'ada@example.com',
            'gender_id' => 'g', 'phone' => '677123456',
            'allowed_level_ids' => $levels,
            'qualifications' => [['qualification_id' => 'q-1', 'field_of_study' => 'CS', 'institution' => 'UB', 'year' => '2020']],
        ], $this->school, (object) ['id' => 'admin']);
    }

    private function assignable(): array
    {
        return app(TeacherSpecialtyService::class)->getAssignableTeachers($this->school, 'swe-2')->all();
    }

    public function test_creating_a_teacher_saves_the_allowed_levels_sent(): void
    {
        $teacher = $this->createTeacher(['lvl-1', 'lvl-2']);

        $this->assertEqualsCanonicalizing(['lvl-1', 'lvl-2'], $teacher->levels()->pluck('levels.id')->all());
        $this->assertSame('branch-1', DB::table('teacher_levels')->where('teacher_id', $teacher->id)->value('school_branch_id'));
    }

    public function test_a_teacher_allowed_the_specialtys_level_is_assignable(): void
    {
        $this->createTeacher(['lvl-2']);

        $this->assertSame(['Ada Lovelace'], array_column($this->assignable(), 'name'));
    }

    public function test_updating_a_teacher_replaces_their_levels_and_qualifications(): void
    {
        $teacher = $this->createTeacher(['lvl-1']);

        $updated = app(TeacherService::class)->updateTeacher([
            'first_name' => 'Augusta',
            'address' => null,
            'allowed_level_ids' => ['lvl-2'],
            'qualifications' => [['qualification_id' => 'q-1', 'field_of_study' => 'Mathematics', 'institution' => 'UB', 'year' => '2021']],
        ], $teacher->id, $this->school, (object) ['id' => 'admin']);

        $this->assertSame('Augusta', $updated->first_name);
        $this->assertSame(['lvl-2'], $updated->levels->pluck('id')->all());
        $this->assertSame('Mathematics', $updated->qualifications->first()->pivot->field_of_study);
        $this->assertSame(['Ada Lovelace'], array_column($this->assignable(), 'name'), 'Now allowed Level 200.');
    }

    public function test_an_update_without_levels_keeps_them(): void
    {
        $teacher = $this->createTeacher(['lvl-2']);

        app(TeacherService::class)->updateTeacher(['last_name' => 'King'], $teacher->id, $this->school, (object) ['id' => 'admin']);

        $this->assertSame(['lvl-2'], $teacher->levels()->pluck('levels.id')->all());
    }

    public function test_the_error_says_why_no_teacher_is_assignable(): void
    {
        $this->createTeacher(['lvl-1']);

        try {
            $this->assignable();
            $this->fail('A teacher not allowed Level 200 was offered.');
        } catch (AppException $e) {
            $this->assertStringContainsString('No teacher at this school is allowed to teach Level 200', $e->getDescription());
        }
    }
}
