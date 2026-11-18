<?php

namespace Tests\Feature\Exam;

use App\Exceptions\AppException;
use App\Models\Exam\Exam;
use App\Services\Exam\ExamService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class UpdateExamRulesTest extends TestCase
{
    use RefreshDatabase;

    private object $school;

    protected function setUp(): void
    {
        parent::setUp();
        Bus::fake();
        Event::fake();
        Carbon::setTestNow('2026-11-18 10:00:00');
        $this->school = (object) ['id' => 'branch-1'];

        DB::table('system_academic_years')->insert(['id' => 'sys-26', 'name' => '2026/2027', 'year_start' => 2026, 'year_end' => 2027]);
        foreach ([['first', 'First Semester', 1], ['second', 'Second Semester', 2]] as [$id, $name, $count]) {
            DB::table('semesters')->insert(['id' => $id, 'name' => $name, 'count' => $count, 'program_name' => 'HND']);
        }
        foreach ([['ca-1', 'First CA', 'first', 'ca', 'first'], ['exam-1', 'First Semester Exam', 'first', 'exam', 'first'], ['ca-2', 'Second CA', 'second', 'ca', 'second'], ['exam-2', 'Second Semester Exam', 'second', 'exam', 'second']] as [$id, $name, $semester, $type, $semesterId]) {
            DB::table('exam_types')->insert(['id' => $id, 'exam_name' => $name, 'semester' => $semester, 'type' => $type, 'program_name' => $id, 'semester_id' => $semesterId]);
        }
        foreach (['swe', 'net'] as $id) {
            DB::table('specialties')->insert(['id' => $id, 'specialty_name' => $id, 'registration_fee' => 0, 'school_fee' => 0, 'status' => 'active', 'school_branch_id' => 'branch-1', 'department_id' => 'd', 'level_id' => 'lvl']);
            DB::table('school_academic_years')->insert(['id' => "sy-$id", 'start_date' => '2026-09-01', 'end_date' => '2027-07-31', 'specialty_id' => $id, 'system_academic_year_id' => 'sys-26', 'school_branch_id' => 'branch-1']);
        }
        DB::table('school_semesters')->insert(['id' => 'sem-1', 'semester_id' => 'first', 'school_year_id' => 'sy-swe', 'start_date' => '2026-09-01', 'end_date' => '2027-01-31', 'school_branch_id' => 'branch-1']);
        DB::table('school_semesters')->insert(['id' => 'sem-2', 'semester_id' => 'second', 'school_year_id' => 'sy-swe', 'start_date' => '2027-02-15', 'end_date' => '2027-06-30', 'school_branch_id' => 'branch-1']);

        // swe: an upcoming CA (30) and its semester exam (70 + 30); net: a running CA.
        $this->exam('ca', 'ca-1', 'sy-swe', '2026-12-01', '2026-12-05', 30);
        $this->exam('final', 'exam-1', 'sy-swe', '2027-01-10', '2027-01-20', 100);
        $this->exam('running', 'ca-1', 'sy-net', '2026-11-10', '2026-11-25', 20);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function exam(string $id, string $type, string $year, string $start, string $end, float $max): void
    {
        DB::table('exams')->insert(['id' => $id, 'exam_type_id' => $type, 'school_year_id' => $year, 'start_date' => $start, 'end_date' => $end, 'max_score' => $max, 'school_branch_id' => 'branch-1']);
    }

    private function rules(string $id): array
    {
        return app(ExamService::class)->getUpdateRules($this->school, $id);
    }

    private function update(string $id, array $data): Exam
    {
        return app(ExamService::class)->updateExam($id, $this->school, $data, (object) ['id' => 'admin']);
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

    private function slot(string $examId, string $date): void
    {
        DB::table('exam_timetable_slots')->insert(['id' => "slot-$date", 'date' => $date, 'start_time' => '08:00', 'end_time' => '10:00', 'exam_id' => $examId, 'course_id' => 'c', 'version_id' => 'v', 'school_branch_id' => 'branch-1']);
    }

    public function test_an_upcoming_exam_shows_its_window_score_split_and_exam_type_options(): void
    {
        $rules = $this->rules('final');

        $this->assertSame('upcoming', $rules['status']);
        $this->assertSame(['source' => 'semester', 'start_date' => '2026-09-01', 'end_date' => '2027-01-31'], array_intersect_key($rules['window'], array_flip(['source', 'start_date', 'end_date'])));
        $this->assertSame([30.0, 70.0], [$rules['ca_max_score'], $rules['exam_portion']]);
        $this->assertTrue($rules['fields']['start_date']['editable']);
        $this->assertSame('2026-11-18', $rules['fields']['start_date']['min'], 'Not in the past.');
        $this->assertSame('2027-01-31', $rules['fields']['end_date']['max'], 'Inside the First Semester.');
        $this->assertFalse($rules['fields']['school_year_id']['editable']);
        $this->assertSame(['ca-2'], array_column($rules['fields']['exam_type_id']['options'], 'id'), 'ca-1 is taken; the Second Semester Exam needs its CA first.');
        $this->assertSame('2027-02-15', $rules['fields']['exam_type_id']['options'][0]['window']['start_date']);
    }

    public function test_dates_stay_inside_the_semester_and_out_of_the_past(): void
    {
        $this->assertRefused('final', ['end_date' => '2027-02-05'], 'Date Too Late');
        $this->assertRefused('final', ['start_date' => '2026-11-01'], 'Date Too Early');
        $this->assertRefused('final', ['start_date' => '2027-01-21'], 'Invalid Date Range');

        $updated = $this->update('final', ['start_date' => '2027-01-12', 'end_date' => '2027-01-25']);
        $this->assertSame(['2027-01-12', '2027-01-25'], [substr($updated->start_date, 0, 10), substr($updated->end_date, 0, 10)]);
    }

    public function test_timetabled_papers_stay_inside_the_exam_and_lock_its_type(): void
    {
        $this->slot('final', '2027-01-12');
        $this->slot('final', '2027-01-18');

        $rules = $this->rules('final');
        $this->assertSame('2027-01-12', $rules['fields']['start_date']['max']);
        $this->assertSame('2027-01-18', $rules['fields']['end_date']['min']);
        $this->assertFalse($rules['fields']['exam_type_id']['editable']);
        $this->assertStringContainsString('2 timetabled papers', $rules['fields']['exam_type_id']['reasons'][0]);

        $this->assertRefused('final', ['start_date' => '2027-01-13'], 'Date Too Late');
        $this->assertRefused('final', ['end_date' => '2027-01-17'], 'Date Too Early');
        $this->assertRefused('final', ['exam_type_id' => 'ca-2'], 'Exam Type Cannot Change', 409);
    }

    public function test_a_running_exam_keeps_its_start_and_type_but_can_move_its_end(): void
    {
        $rules = $this->rules('running');
        $this->assertSame('ongoing', $rules['status']);
        $this->assertSame('academic_year', $rules['window']['source'], 'net has no semesters set up.');
        $this->assertFalse($rules['fields']['start_date']['editable']);
        $this->assertFalse($rules['fields']['exam_type_id']['editable']);

        $this->assertRefused('running', ['start_date' => '2026-11-12'], 'Start Date Cannot Change', 409);
        $this->assertRefused('running', ['end_date' => '2026-11-17'], 'Date Too Early');
        $this->assertSame('2026-11-28', substr($this->update('running', ['end_date' => '2026-11-28'])->end_date, 0, 10));
    }

    public function test_published_results_make_the_exam_read_only(): void
    {
        DB::table('exam_result_publications')->insert(['id' => 'pub', 'exam_id' => 'final', 'school_branch_id' => 'branch-1', 'published_at' => now()]);

        $this->assertTrue($this->rules('final')['read_only']);
        $this->assertRefused('final', ['end_date' => '2027-01-25'], 'Exam Is Read-Only', 409);
    }

    public function test_the_academic_year_never_changes(): void
    {
        $this->assertRefused('final', ['school_year_id' => 'sy-net'], 'Academic Year Cannot Change', 409);
        $this->assertStringContainsString('delete this exam', $this->rules('final')['fields']['school_year_id']['reasons'][0]);
    }

    public function test_a_cas_max_score_locks_once_its_semester_exam_is_evaluated(): void
    {
        DB::table('exam_scores')->insert(['id' => 'score', 'score' => 50, 'exam_id' => 'final', 'school_branch_id' => 'branch-1', 'candidate_id' => 'cand', 'course_id' => 'c', 'grade_id' => 'g']);

        $this->assertFalse($this->rules('final')['fields']['max_score']['editable']);
        $this->assertStringContainsString('First Semester Exam', $this->rules('ca')['fields']['max_score']['reasons'][0]);
        $this->assertRefused('ca', ['max_score' => 25], 'Max Score Cannot Change', 409);
        $this->assertRefused('final', ['max_score' => 60], 'Max Score Cannot Change', 409);
        $this->assertSame('2026-12-08', substr($this->update('ca', ['end_date' => '2026-12-08'])->end_date, 0, 10), 'Dates can still change.');
    }

    public function test_a_semester_exam_edits_its_own_part_of_the_max_score(): void
    {
        $this->assertSame(90.0, (float) $this->update('final', ['max_score' => 60])->max_score, '60 + the CA\'s 30.');
        $this->assertRefused('final', ['max_score' => 60], 'No Changes');
    }

    public function test_bulk_updates_skip_exams_that_do_not_change(): void
    {
        $result = app(ExamService::class)->bulkUpdateExam([
            ['exam_id' => 'ca', 'start_date' => '2026-12-01'],
            ['exam_id' => 'final', 'end_date' => '2027-01-22'],
        ], $this->school, (object) ['id' => 'admin']);

        $this->assertCount(2, $result);
        $this->assertSame('2027-01-22', substr(Exam::find('final')->end_date, 0, 10));
    }

    #[DataProvider('refusedTypeChanges')]
    public function test_exam_type_changes_keep_the_exam_unique_and_valid(string $type, string $title): void
    {
        $this->assertRefused('final', ['exam_type_id' => $type], $title, 409);
    }

    public static function refusedTypeChanges(): array
    {
        return [
            'already used in the year' => ['ca-1', 'Exam Type Unavailable'],
            'semester exam without its CA' => ['exam-2', 'Exam Type Unavailable'],
        ];
    }
}
