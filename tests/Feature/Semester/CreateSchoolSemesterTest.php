<?php

namespace Tests\Feature\Semester;

use App\Exceptions\AppException;
use App\Models\InstructorAvailability;
use App\Models\SchoolSemester;
use App\Notifications\TeacherNewSemesterAvailable;
use App\Services\Semester\SchoolSemesterService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CreateSchoolSemesterTest extends TestCase
{
    use RefreshDatabase;

    private object $school;
    private SchoolSemesterService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();

        $this->school = (object) ['id' => 'branch-1'];
        $this->service = app(SchoolSemesterService::class);

        DB::table('school_branches')->insert(['id' => 'branch-1', 'name' => 'Main', 'abbreviation' => 'M', 'school_id' => 'school-1']);
        DB::table('system_academic_years')->insert(['id' => 'year-2099', 'name' => '2099/2100', 'year_start' => 2099, 'year_end' => 2100]);
        DB::table('semesters')->insert(['id' => 'sem-1', 'name' => 'First Semester', 'count' => 1, 'program_name' => 'HND']);
        DB::table('specialties')->insert(['id' => 'swe', 'specialty_name' => 'Software Engineering', 'registration_fee' => 0, 'school_fee' => 0, 'status' => 'active', 'school_branch_id' => 'branch-1', 'department_id' => 'dep', 'level_id' => 'lvl']);
        DB::table('school_academic_years')->insert(['id' => 'sy-swe', 'start_date' => '2099-09-01', 'end_date' => '2100-06-30', 'specialty_id' => 'swe', 'system_academic_year_id' => 'year-2099', 'school_branch_id' => 'branch-1']);
        DB::table('teachers')->insert(['id' => 't-1', 'name' => 'Ada Teacher', 'first_name' => 'Ada', 'last_name' => 'Teacher', 'password' => 'x', 'username' => 'ada', 'email' => 'ada@example.com', 'gender_id' => 'g-1', 'school_branch_id' => 'branch-1']);
        DB::table('teacher_specialty_preferences')->insert(['id' => 'tsp-1', 'school_branch_id' => 'branch-1', 'teacher_id' => 't-1', 'specialty_id' => 'swe']);
    }

    private function data(array $overrides = []): array
    {
        return array_merge([
            'start_date' => '2099-09-15',
            'end_date' => '2100-01-31',
            'school_year_id' => 'sy-swe',
            'semester_id' => 'sem-1',
        ], $overrides);
    }

    public function test_it_creates_the_semester_and_opens_teacher_availability(): void
    {
        $semester = $this->service->createSchoolSemester($this->data(), $this->school, null);

        $this->assertDatabaseHas('school_semesters', ['id' => $semester->id, 'school_year_id' => 'sy-swe', 'semester_id' => 'sem-1']);
        $this->assertSame(1, InstructorAvailability::where('school_semester_id', $semester->id)->where('teacher_id', 't-1')->count());
        Notification::assertCount(1);
    }

    public function test_a_duplicate_semester_is_rejected_with_409(): void
    {
        $this->service->createSchoolSemester($this->data(), $this->school, null);

        try {
            $this->service->createSchoolSemester($this->data(), $this->school, null);
            $this->fail('Expected a duplicate semester to be rejected.');
        } catch (AppException $e) {
            $this->assertSame(409, $e->getCode());
        }
        $this->assertSame(1, SchoolSemester::count());
    }

    public function test_dates_outside_the_school_year_are_rejected_with_400(): void
    {
        $this->expectException(AppException::class);
        $this->expectExceptionCode(400);

        $this->service->createSchoolSemester($this->data(['end_date' => '2100-08-01']), $this->school, null);
    }

    public function test_an_unknown_school_year_is_rejected_with_404(): void
    {
        $this->expectException(AppException::class);
        $this->expectExceptionCode(404);

        $this->service->createSchoolSemester($this->data(['school_year_id' => 'missing']), $this->school, null);
    }
}
