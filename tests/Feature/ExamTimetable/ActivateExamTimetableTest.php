<?php

namespace Tests\Feature\ExamTimetable;

use App\Exceptions\AppException;
use App\Exceptions\ExamTimetable\ExamTimetableActivationConflict;
use App\Models\ExamTimetable\ExamInvigilator;
use App\Models\ExamTimetable\ExamSessionHall;
use App\Models\ExamTimetable\ExamSessionInvigilator;
use App\Models\ExamTimetable\ExamTimetableSlot;
use App\Models\ExamTimetable\ExamTimetableVersion;
use App\Models\Teacher;
use App\Schedular\ExamTimetable\Engine\SchedularEngine;
use App\Services\ExamTimetable\ActivateExamTimetableService;
use App\Services\ExamTimetable\ExamTimetableVersionService;
use App\Services\ExamTimetable\Support\ExamActiveCommitments;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Activation writes a generated exam timetable to the exam timetable tables,
 * and the halls and invigilators it uses become busy for the next exam.
 * The Mongo document a version is generated into is replaced by the
 * timetables kept in $generated.
 */
class ActivateExamTimetableTest extends TestCase
{
    use RefreshDatabase;

    private object $school;
    private array $generated = [];
    private ActivateExamTimetableService $activation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->school = (object) ['id' => 'branch-1'];
        DB::table('school_branches')->insert(['id' => 'branch-1', 'name' => 'Main', 'abbreviation' => 'M', 'school_id' => 'school-1']);

        foreach ([['h-a', 'Hall A', 30], ['h-b', 'Hall B', 40], ['h-big', 'Big Hall', 100]] as [$id, $name, $capacity]) {
            DB::table('halls')->insert(['id' => $id, 'name' => $name, 'capacity' => $capacity, 'school_branch_id' => 'branch-1']);
        }
        foreach (['c1', 'c2', 'c3', 'd1', 'd2'] as $course) {
            DB::table('courses')->insert(['id' => $course, 'course_code' => strtoupper($course), 'course_title' => "Course {$course}", 'credit' => 3, 'school_branch_id' => 'branch-1', 'semester_id' => 'sem-1']);
        }

        $generated = &$this->generated;
        $this->activation = new class($generated) extends ActivateExamTimetableService {
            public function __construct(private array &$generated) {}

            protected function generatedTimetable(object $currentSchool, ExamTimetableVersion $version): array
            {
                return $this->generated[$version->id];
            }
        };
    }

    private function exam(string $id, string $endDate = '2099-12-31'): void
    {
        DB::table('exams')->insert(['id' => $id, 'school_branch_id' => 'branch-1', 'exam_type_id' => 'type-1', 'school_year_id' => 'year-1', 'start_date' => '2099-12-01', 'end_date' => $endDate]);
    }

    /** Teachers become invigilators of the exam; returns invigilator id => teacher id. */
    private function invigilators(string $examId, array $teacherIds): array
    {
        $ids = [];
        foreach ($teacherIds as $teacherId) {
            $invigilator = ExamInvigilator::create([
                'school_branch_id' => 'branch-1',
                'exam_id' => $examId,
                'actorable_id' => $teacherId,
                'actorable_type' => Teacher::class,
            ]);
            $ids[$invigilator->id] = $teacherId;
        }
        return $ids;
    }

    private function input(string $examId, array $courses, array $invigilators, array $busy = []): array
    {
        return [
            'exam_id' => $examId,
            'specialty_id' => "spec-{$examId}",
            'start_date' => '2099-12-07',
            'end_date' => '2099-12-08',
            'candidate_count' => 60,
            'courses' => array_map(fn($id) => ['course_id' => $id, 'course_title' => "Course {$id}"], $courses),
            'halls' => [
                ['hall_id' => 'h-a', 'hall_name' => 'Hall A', 'capacity' => 30],
                ['hall_id' => 'h-b', 'hall_name' => 'Hall B', 'capacity' => 40],
                ['hall_id' => 'h-big', 'hall_name' => 'Big Hall', 'capacity' => 100],
            ],
            'invigilators' => array_map(fn($id) => ['invigilator_id' => $id, 'name' => $id, 'courses_taught' => []], array_keys($invigilators)),
            'hall_busy_periods' => $busy['hall_busy_periods'] ?? [],
            'invigilator_busy_periods' => $busy['invigilator_busy_periods'] ?? [],
            'hard_constraints' => [
                'operational_period' => ['start_time' => '08:00', 'end_time' => '12:00'],
                'session_duration' => ['duration' => 120],
            ],
            'soft_constraints' => [],
        ];
    }

    /** Generates a version the way the job does, keeping what it would store. */
    private function generateVersion(string $examId, array $input): ExamTimetableVersion
    {
        $response = app(SchedularEngine::class)->run($input, withSolutions: false);
        $version = ExamTimetableVersion::createNext('branch-1', $examId, $response->status);
        $this->generated[$version->id] = ['timetable_slots' => $response->timetable, 'scheduler_input' => $input];
        return $version;
    }

    public function test_activation_writes_slots_halls_and_invigilators(): void
    {
        $this->exam('exam-a');
        $invigilators = $this->invigilators('exam-a', ['t1', 't2']);
        $version = $this->generateVersion('exam-a', $this->input('exam-a', ['c1', 'c2'], $invigilators));

        $result = $this->activation->activate($this->school, $version->id);

        $this->assertSame(2, $result['session_count']);
        $this->assertSame(2, ExamTimetableSlot::where('version_id', $version->id)->count());
        $this->assertSame(60, (int) ExamSessionHall::sum('candidate_count') / 2);
        $this->assertSame(0, ExamSessionInvigilator::whereNull('exam_session_hall_id')->count());
        $this->assertGreaterThan(0, ExamSessionInvigilator::count());
        // The version with slots is the active one; there is no separate active record.
        $this->assertSame($version->id, $this->activation->activeFor($this->school, 'exam-a')['active_version']['id']);
        $this->assertSame([$version->id], ExamTimetableVersion::where('exam_id', 'exam-a')->active()->pluck('id')->all());
    }

    public function test_activating_another_version_replaces_the_first(): void
    {
        $this->exam('exam-a');
        $invigilators = $this->invigilators('exam-a', ['t1', 't2']);
        $first = $this->generateVersion('exam-a', $this->input('exam-a', ['c1', 'c2'], $invigilators));
        $second = $this->generateVersion('exam-a', $this->input('exam-a', ['c1', 'c2', 'c3'], $invigilators));

        $this->activation->activate($this->school, $first->id);
        $result = $this->activation->activate($this->school, $second->id);

        $this->assertSame($first->id, $result['replaced']['id']);
        $this->assertSame(0, ExamTimetableSlot::where('version_id', $first->id)->count());
        $this->assertSame(3, ExamTimetableSlot::where('version_id', $second->id)->count());
        $this->assertSame([$second->id], ExamTimetableVersion::where('exam_id', 'exam-a')->active()->pluck('id')->all());
        $this->assertSame($second->id, $this->activation->activeFor($this->school, 'exam-a')['active_version']['id']);
    }

    public function test_active_exam_makes_its_halls_and_invigilators_busy_for_the_next_exam(): void
    {
        $this->exam('exam-a');
        $this->exam('exam-b');
        $a = $this->invigilators('exam-a', ['t1', 't2']);
        $b = $this->invigilators('exam-b', ['t1', 't3']);
        $this->activation->activate($this->school, $this->generateVersion('exam-a', $this->input('exam-a', ['c1', 'c2'], $a))->id);

        $busy = app(ExamActiveCommitments::class)->busyPeriodsForGeneration(
            'branch-1', 'exam-b', '2099-12-07', '2099-12-08', ['h-a', 'h-b', 'h-big'],
            ExamInvigilator::where('exam_id', 'exam-b')->get(),
        );

        $this->assertNotEmpty($busy['hall_busy_periods']);
        // Teacher t1 invigilates exam A, so exam B sees its own invigilator id for t1 as busy.
        $t1InB = array_search('t1', $b, true);
        $this->assertNotEmpty($busy['invigilator_busy_periods']);
        $this->assertSame([$t1InB], array_values(array_unique(array_column($busy['invigilator_busy_periods'], 'invigilator_id'))));

        $versionB = $this->generateVersion('exam-b', $this->input('exam-b', ['d1', 'd2'], $b, $busy));
        $check = $this->activation->check($this->school, $versionB->id);

        $this->assertTrue($check['can_activate'], json_encode($check['conflicts']));
        $this->activation->activate($this->school, $versionB->id);
        $this->assertSame(2, ExamTimetableVersion::active()->count());
    }

    public function test_clash_with_another_active_exam_blocks_activation(): void
    {
        $this->exam('exam-a');
        $this->exam('exam-b');
        $a = $this->invigilators('exam-a', ['t1', 't2']);
        $b = $this->invigilators('exam-b', ['t1', 't2']);
        $this->activation->activate($this->school, $this->generateVersion('exam-a', $this->input('exam-a', ['c1', 'c2'], $a))->id);

        // Generated without knowing about exam A, so it takes the same halls and teachers.
        $versionB = $this->generateVersion('exam-b', $this->input('exam-b', ['d1', 'd2'], $b));
        $check = $this->activation->check($this->school, $versionB->id);

        $this->assertFalse($check['can_activate']);
        $this->assertContains('invigilator', array_column($check['conflicts'], 'type'));

        $this->expectException(ExamTimetableActivationConflict::class);
        $this->activation->activate($this->school, $versionB->id);
    }

    public function test_incomplete_or_ended_exam_timetables_cannot_be_activated(): void
    {
        $this->exam('exam-a');
        $invigilators = $this->invigilators('exam-a', ['t1']);
        $input = $this->input('exam-a', ['c1'], $invigilators);
        $input['hard_constraints']['session_duration']['duration'] = 600;
        $failed = $this->generateVersion('exam-a', $input);

        try {
            $this->activation->activate($this->school, $failed->id);
            $this->fail('An incomplete timetable was activated.');
        } catch (AppException $exception) {
            $this->assertSame('Timetable Not Ready', $exception->getTitle());
        }

        $this->exam('exam-old', '2020-01-01');
        $old = $this->generateVersion('exam-old', $this->input('exam-old', ['c1'], $this->invigilators('exam-old', ['t1'])));

        $this->expectExceptionMessage('has ended');
        $this->activation->activate($this->school, $old->id);
    }

    public function test_the_active_version_cannot_be_deleted_but_a_replaced_one_can(): void
    {
        $this->exam('exam-a');
        $invigilators = $this->invigilators('exam-a', ['t1', 't2']);
        $first = $this->generateVersion('exam-a', $this->input('exam-a', ['c1', 'c2'], $invigilators));
        $second = $this->generateVersion('exam-a', $this->input('exam-a', ['c1', 'c2'], $invigilators));
        $versions = app(ExamTimetableVersionService::class);

        $this->activation->activate($this->school, $first->id);
        try {
            $versions->deleteVersion($this->school, $first->id);
            $this->fail('The active version was deleted.');
        } catch (AppException $exception) {
            $this->assertSame('Version In Use', $exception->getTitle());
        }

        $this->activation->activate($this->school, $second->id);
        $versions->deleteVersion($this->school, $first->id);
        $this->assertNull(ExamTimetableVersion::find($first->id));
        $this->assertSame($second->id, $this->activation->activeFor($this->school, 'exam-a')['active_version']['id']);
    }
}
