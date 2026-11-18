<?php

namespace Tests\Feature\AcademicYear;

use App\Exceptions\AppException;
use App\Models\AcademicYear\SchoolAcademicYear;
use App\Services\AcademicYear\SchoolAcademicYearService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class UpdateSchoolAcademicYearTest extends TestCase
{
    use RefreshDatabase;

    private object $school;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-11-18 10:00:00');
        $this->school = (object) ['id' => 'branch-1'];

        foreach ([['sys-26', '2026/2027', 2026, 2027], ['sys-27', '2027/2028', 2027, 2028], ['sys-28', '2028/2029', 2028, 2029]] as [$id, $name, $start, $end]) {
            DB::table('system_academic_years')->insert(['id' => $id, 'name' => $name, 'year_start' => $start, 'year_end' => $end]);
        }
        DB::table('levels')->insert(['id' => 'lvl-1', 'name' => 'Level 100', 'level' => 100, 'program_name' => 'l1', 'level_type_id' => 't']);
        foreach (['swe-1', 'net-1'] as $id) {
            DB::table('specialties')->insert(['id' => $id, 'specialty_name' => $id, 'registration_fee' => 0, 'school_fee' => 0, 'status' => 'active', 'school_branch_id' => 'branch-1', 'department_id' => 'd', 'level_id' => 'lvl-1']);
        }

        // swe-1: one running year and one upcoming year.
        $this->year('ongoing', 'sys-26', '2026-09-01', '2027-07-31');
        $this->year('upcoming', 'sys-27', '2027-09-01', '2028-07-31');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function year(string $id, string $system, string $start, string $end, string $specialty = 'swe-1'): void
    {
        DB::table('school_academic_years')->insert(['id' => $id, 'specialty_id' => $specialty, 'school_branch_id' => 'branch-1', 'system_academic_year_id' => $system, 'start_date' => $start, 'end_date' => $end]);
    }

    private function update(string $id, array $data): array
    {
        return app(SchoolAcademicYearService::class)->updateSchoolAcademicYear($this->school, $id, $data);
    }

    private function rules(string $id): array
    {
        return app(SchoolAcademicYearService::class)->getUpdateRules($this->school, $id);
    }

    private function assertRefused(string $id, array $data, string $title, int $code = 422): void
    {
        try {
            $this->update($id, $data);
            $this->fail("The update was accepted: " . json_encode($data));
        } catch (AppException $e) {
            $this->assertSame($title, $e->getTitle(), $e->getDescription());
            $this->assertSame($code, $e->getCode());
        }
    }

    public function test_an_upcoming_year_can_change_its_dates_and_school_year(): void
    {
        $rules = $this->rules('upcoming');

        $this->assertSame('upcoming', $rules['status']);
        $this->assertTrue($rules['fields']['start_date']['editable']);
        $this->assertTrue($rules['fields']['end_date']['editable']);
        $this->assertTrue($rules['fields']['system_academic_year_id']['editable']);
        $this->assertFalse($rules['fields']['specialty_id']['editable']);
        $this->assertSame(['sys-27', 'sys-28'], array_column($rules['fields']['system_academic_year_id']['options'], 'id'), 'Its own year and unused ones; sys-26 belongs to the running year.');
        $this->assertSame('2027-08-01', $rules['fields']['start_date']['min'], 'Starts after the previous year ends.');

        $updated = $this->update('upcoming', ['start_date' => '2027-09-15', 'end_date' => '2028-06-30']);

        $this->assertSame(['2027-09-15', '2028-06-30'], [$updated['start_date'], $updated['end_date']]);
    }

    public function test_a_running_year_keeps_its_start_and_school_year_but_can_move_its_end(): void
    {
        $rules = $this->rules('ongoing');
        $this->assertSame('ongoing', $rules['status']);
        $this->assertFalse($rules['fields']['start_date']['editable']);
        $this->assertFalse($rules['fields']['system_academic_year_id']['editable']);
        $this->assertTrue($rules['fields']['end_date']['editable']);
        $this->assertSame('2027-08-31', $rules['fields']['end_date']['max'], 'Ends before the next year starts.');

        $this->assertRefused('ongoing', ['start_date' => '2026-09-15'], 'Start Date Cannot Change', 409);
        $this->assertRefused('ongoing', ['system_academic_year_id' => 'sys-28'], 'Academic Year Cannot Change', 409);
        $this->assertRefused('ongoing', ['end_date' => '2027-09-10'], 'Date Too Late');
        $this->assertRefused('ongoing', ['end_date' => '2026-11-17'], 'Date Too Early');

        $this->assertSame('2027-06-30', $this->update('ongoing', ['end_date' => '2027-06-30'])['end_date']);
    }

    public function test_an_ended_year_is_read_only(): void
    {
        $this->year('ended', 'sys-26', '2025-09-01', '2026-07-31', 'net-1');

        $rules = $this->rules('ended');
        $this->assertTrue($rules['read_only']);
        $this->assertFalse($rules['fields']['end_date']['editable']);
        $this->assertStringContainsString('ended on 31 Jul 2026', $rules['fields']['end_date']['reasons'][0]);

        $this->assertRefused('ended', ['end_date' => '2026-12-31'], 'Academic Year Is Read-Only', 409);
    }

    public function test_a_year_whose_students_are_being_promoted_is_read_only(): void
    {
        DB::table('promotion_runs')->insert(['id' => 'run-1', 'school_year_id' => 'ongoing', 'school_branch_id' => 'branch-1', 'status' => 'processing']);

        $this->assertTrue($this->rules('ongoing')['read_only']);
        $this->assertRefused('ongoing', ['end_date' => '2027-06-30'], 'Academic Year Is Read-Only', 409);
    }

    public function test_the_specialty_never_changes(): void
    {
        $this->assertRefused('upcoming', ['specialty_id' => 'net-1'], 'Specialty Cannot Change', 409);
        $this->assertStringContainsString('delete this academic year', $this->rules('upcoming')['fields']['specialty_id']['reasons'][0]);
    }

    public function test_semesters_exams_and_installments_stay_inside_the_year(): void
    {
        DB::table('school_semesters')->insert(['id' => 'sem-1', 'school_year_id' => 'upcoming', 'start_date' => '2027-10-01', 'end_date' => '2028-02-15', 'school_branch_id' => 'branch-1', 'semester_id' => 's']);
        DB::table('fee_schedules')->insert(['id' => 'fs-1', 'school_year_id' => 'upcoming', 'school_branch_id' => 'branch-1']);
        DB::table('fee_sched_installments')->insert(['id' => 'inst', 'due_date' => '2028-05-01', 'percentage' => 100, 'installment_id' => 'i', 'fee_schedule_id' => 'fs-1', 'school_branch_id' => 'branch-1']);

        $rules = $this->rules('upcoming');
        $this->assertSame('2027-10-01', $rules['fields']['start_date']['max']);
        $this->assertSame('2028-05-01', $rules['fields']['end_date']['min']);
        $this->assertFalse($rules['fields']['system_academic_year_id']['editable'], 'Semesters are created under this school year.');

        $this->assertRefused('upcoming', ['start_date' => '2027-10-05'], 'Date Too Late');
        $this->assertRefused('upcoming', ['end_date' => '2028-04-30'], 'Date Too Early');
    }

    public function test_changing_the_school_year_keeps_it_unique_and_the_dates_inside_it(): void
    {
        $this->year('net-upcoming', 'sys-27', '2027-09-01', '2028-07-31', 'net-1');
        $this->year('net-later', 'sys-28', '2028-09-01', '2029-07-31', 'net-1');

        $this->assertRefused('net-upcoming', ['system_academic_year_id' => 'sys-28'], 'Academic Year Already Exists', 409);
        $this->assertRefused('upcoming', ['system_academic_year_id' => 'sys-28'], 'Date Too Early');

        $updated = $this->update('upcoming', ['system_academic_year_id' => 'sys-28', 'start_date' => '2028-09-01', 'end_date' => '2029-07-31']);
        $this->assertSame('sys-28', $updated['system_academic_year_id']);
    }

    #[DataProvider('invalidDates')]
    public function test_invalid_date_ranges_are_refused(array $data, string $title): void
    {
        $this->assertRefused('upcoming', $data, $title);
    }

    public static function invalidDates(): array
    {
        return [
            'start after end' => [['start_date' => '2028-08-01'], 'Invalid Date Range'],
            'start in the past' => [['start_date' => '2026-11-01', 'end_date' => '2027-08-15'], 'Date Too Early'],
            'overlapping the previous year' => [['start_date' => '2027-07-15'], 'Date Too Early'],
            'outside the school year' => [['end_date' => '2029-01-10'], 'Date Too Late'],
            'no change' => [['start_date' => '2027-09-01'], 'No Changes'],
        ];
    }

    public function test_the_update_endpoint_returns_the_rules_after_the_change(): void
    {
        $updated = $this->update('upcoming', ['end_date' => '2028-06-30']);

        $this->assertSame('2028-06-30', $updated['update_rules']['end_date']);
        $this->assertSame('2028-06-30', SchoolAcademicYear::find('upcoming')->end_date->toDateString());
    }

    public function test_an_upcoming_year_can_still_be_deleted(): void
    {
        $this->assertTrue(app(SchoolAcademicYearService::class)->deleteSchoolAcademicYear($this->school, 'upcoming'));
        $this->assertNull(SchoolAcademicYear::find('upcoming'));
    }
}
