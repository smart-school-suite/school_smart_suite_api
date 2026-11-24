<?php

namespace Tests\Feature\Semester;

use App\Exceptions\AppException;
use App\Models\SchoolSemester;
use App\Services\Semester\SchoolSemesterService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\SetsSemesterLimit;
use Tests\TestCase;

class UpdateSchoolSemesterTest extends TestCase
{
    use RefreshDatabase;
    use SetsSemesterLimit;

    private object $school;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();
        Carbon::setTestNow('2026-11-18 10:00:00');
        $this->school = (object) ['id' => 'branch-1'];

        DB::table('system_academic_years')->insert(['id' => 'sys-26', 'name' => '2026/2027', 'year_start' => 2026, 'year_end' => 2027]);
        foreach ([['first', 'First Semester', 1], ['second', 'Second Semester', 2], ['third', 'Third Semester', 3]] as [$id, $name, $count]) {
            DB::table('semesters')->insert(['id' => $id, 'name' => $name, 'count' => $count, 'program_name' => 'HND']);
        }
        foreach (['swe', 'net'] as $id) {
            DB::table('specialties')->insert(['id' => $id, 'specialty_name' => $id, 'registration_fee' => 0, 'school_fee' => 0, 'status' => 'active', 'school_branch_id' => 'branch-1', 'department_id' => 'd', 'level_id' => 'lvl']);
        }
        DB::table('school_academic_years')->insert(['id' => 'sy-swe', 'start_date' => '2026-09-01', 'end_date' => '2027-07-31', 'specialty_id' => 'swe', 'system_academic_year_id' => 'sys-26', 'school_branch_id' => 'branch-1']);
        DB::table('school_academic_years')->insert(['id' => 'sy-net', 'start_date' => '2026-09-01', 'end_date' => '2027-07-31', 'specialty_id' => 'net', 'system_academic_year_id' => 'sys-26', 'school_branch_id' => 'branch-1']);

        $this->setSemesterLimit(3);

        // swe: a running first semester and an upcoming second one.
        $this->semester('running', 'first', '2026-09-01', '2027-01-31');
        $this->semester('upcoming', 'second', '2027-02-15', '2027-06-30');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function semester(string $id, string $type, string $start, string $end, string $year = 'sy-swe'): void
    {
        DB::table('school_semesters')->insert(['id' => $id, 'semester_id' => $type, 'school_year_id' => $year, 'start_date' => $start, 'end_date' => $end, 'school_branch_id' => 'branch-1']);
    }

    private function service(): SchoolSemesterService
    {
        return app(SchoolSemesterService::class);
    }

    private function update(string $id, array $data): array
    {
        return $this->service()->updateSchoolSemester($data, $this->school, $id, (object) ['id' => 'admin']);
    }

    private function assertRefused(string $id, array $data, string $title, int $code = 422): void
    {
        try {
            $this->update($id, $data);
            $this->fail('The update was accepted: ' . json_encode($data));
        } catch (AppException $e) {
            $this->assertSame($title, $e->getTitle(), $e->getDescription());
            $this->assertSame($code, $e->getCode());
        }
    }

    public function test_an_upcoming_semester_can_change_its_dates_and_which_semester_it_is(): void
    {
        $rules = $this->service()->getUpdateRules($this->school, 'upcoming');

        $this->assertSame('upcoming', $rules['status']);
        $this->assertTrue($rules['fields']['start_date']['editable']);
        $this->assertTrue($rules['fields']['semester_id']['editable']);
        $this->assertFalse($rules['fields']['school_year_id']['editable']);
        $this->assertSame(['second', 'third'], array_column($rules['fields']['semester_id']['options'], 'id'), 'First Semester is taken by the running semester.');
        $this->assertSame('2027-02-01', $rules['fields']['start_date']['min'], 'Starts after the First Semester ends.');
        $this->assertSame('2027-07-31', $rules['fields']['end_date']['max'], 'Ends with the academic year.');

        $updated = $this->update('upcoming', ['start_date' => '2027-02-20', 'end_date' => '2027-07-15', 'semester_id' => 'third']);

        $this->assertSame(['2027-02-20', '2027-07-15', 'third'], [$updated['start_date'], $updated['end_date'], $updated['semester_id']]);
    }

    public function test_a_running_semester_keeps_its_start_and_type_but_can_move_its_end(): void
    {
        $rules = $this->service()->getUpdateRules($this->school, 'running');

        $this->assertFalse($rules['fields']['start_date']['editable']);
        $this->assertFalse($rules['fields']['semester_id']['editable']);
        $this->assertTrue($rules['fields']['end_date']['editable']);
        $this->assertSame('2027-02-14', $rules['fields']['end_date']['max'], 'Ends before the Second Semester starts.');

        $this->assertRefused('running', ['start_date' => '2026-09-05'], 'Start Date Cannot Change', 409);
        $this->assertRefused('running', ['semester_id' => 'third'], 'Semester Cannot Change', 409);
        $this->assertRefused('running', ['end_date' => '2027-02-20'], 'Date Too Late');
        $this->assertSame('2027-02-10', $this->update('running', ['end_date' => '2027-02-10'])['end_date']);
    }

    public function test_an_ended_semester_is_read_only(): void
    {
        $this->semester('ended', 'first', '2026-02-01', '2026-06-30', 'sy-net');

        $rules = $this->service()->getUpdateRules($this->school, 'ended');
        $this->assertTrue($rules['read_only']);
        $this->assertRefused('ended', ['end_date' => '2026-12-31'], 'Semester Is Read-Only', 409);
    }

    public function test_a_semester_whose_students_are_being_promoted_is_read_only(): void
    {
        DB::table('promotion_runs')->insert(['id' => 'run', 'school_year_id' => 'sy-swe', 'school_branch_id' => 'branch-1', 'status' => 'completed']);

        $this->assertTrue($this->service()->getUpdateRules($this->school, 'upcoming')['read_only']);
        $this->assertRefused('upcoming', ['end_date' => '2027-06-01'], 'Semester Is Read-Only', 409);
    }

    public function test_the_academic_year_never_changes(): void
    {
        $this->assertRefused('upcoming', ['school_year_id' => 'sy-net'], 'Academic Year Cannot Change', 409);
    }

    public function test_exams_timetables_and_joint_courses_lock_which_semester_it_is(): void
    {
        DB::table('exam_types')->insert(['id' => 'ca-2', 'exam_name' => 'CA', 'semester' => 'second', 'semester_id' => 'second', 'type' => 'ca', 'program_name' => 'ca']);
        DB::table('exams')->insert(['id' => 'exam-1', 'school_year_id' => 'sy-swe', 'school_branch_id' => 'branch-1', 'exam_type_id' => 'ca-2']);

        $rules = $this->service()->getUpdateRules($this->school, 'upcoming');
        $this->assertFalse($rules['fields']['semester_id']['editable']);
        $this->assertStringContainsString('1 exam', $rules['fields']['semester_id']['reasons'][0]);
        $this->assertSame(1, $rules['dependents']['exams']);
        $this->assertRefused('upcoming', ['semester_id' => 'third'], 'Semester Cannot Change', 409);
    }

    #[DataProvider('invalidChanges')]
    public function test_invalid_changes_are_refused(array $data, string $title, int $code = 422): void
    {
        $this->assertRefused('upcoming', $data, $title, $code);
    }

    public static function invalidChanges(): array
    {
        return [
            'start after end' => [['start_date' => '2027-07-01'], 'Invalid Date Range'],
            'overlapping the first semester' => [['start_date' => '2027-01-20'], 'Date Too Early'],
            'past the academic year' => [['end_date' => '2027-08-15'], 'Date Too Late'],
            'semester already in the year' => [['semester_id' => 'first'], 'Semester Already Exists', 409],
            'no change' => [['end_date' => '2027-06-30'], 'No Changes'],
        ];
    }

    public function test_bulk_updates_follow_the_same_rules_all_or_nothing(): void
    {
        try {
            $this->service()->bulkUpdateSchoolSemester([
                ['school_semester_id' => 'running', 'end_date' => '2027-02-05'],
                ['school_semester_id' => 'upcoming', 'end_date' => '2027-08-30'],
            ], $this->school, null);
            $this->fail('The batch was accepted.');
        } catch (AppException $e) {
            $this->assertStringStartsWith('Second Semester:', $e->getDescription());
        }
        $this->assertSame('2027-01-31', substr(SchoolSemester::find('running')->end_date, 0, 10), 'Nothing in the batch was saved.');

        $this->service()->bulkUpdateSchoolSemester([
            ['school_semester_id' => 'running', 'end_date' => '2027-02-05'],
            ['school_semester_id' => 'upcoming', 'end_date' => '2027-06-30'],
        ], $this->school, null);
        $this->assertSame('2027-02-05', substr(SchoolSemester::find('running')->end_date, 0, 10));
    }

    public function test_the_semester_list_includes_created_and_updated_timestamps(): void
    {
        DB::table('school_semesters')->update(['created_at' => '2026-08-01 09:00:00', 'updated_at' => '2026-08-02 10:30:00']);

        $listed = $this->service()->getSchoolSemesters($this->school);

        $this->assertCount(2, $listed);
        foreach ($listed as $semester) {
            $this->assertNotNull($semester['created_at'], 'created_at was not selected.');
            $this->assertSame('2026-08-02', $semester['updated_at']->toDateString());
        }
    }

    public function test_a_semester_beyond_the_schools_limit_is_not_offered_or_accepted(): void
    {
        $this->setSemesterLimit(2);

        $rules = $this->service()->getUpdateRules($this->school, 'upcoming');

        $this->assertSame(['second'], array_column($rules['fields']['semester_id']['options'], 'id'));
        $this->assertRefused('upcoming', ['semester_id' => 'third'], 'Semester Limit Reached');
    }
}
