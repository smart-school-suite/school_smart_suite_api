<?php

namespace Tests\Unit\ExamTimetable;

use App\Constant\Constraint\ExamTimetable\Assignment\RequestedAssignment;
use App\Constant\Constraint\ExamTimetable\Course\CourseExamSession;
use App\Constant\Constraint\ExamTimetable\Course\CourseTimeRequest;
use App\Constant\Constraint\ExamTimetable\Course\RequiredJointCourseSession;
use App\Constant\Constraint\ExamTimetable\Invigilator\InvigilatorRequestedSlot;
use App\Constant\Constraint\ExamTimetable\Student\StudentDailyLoadRange;
use App\Constant\Violation\ExamTimetable\Course\RequiredJointCourse;
use App\Constant\Violation\ExamTimetable\Hall\HallBusy;
use App\Constant\Violation\ExamTimetable\Schedule\OperationalPeriod;
use App\Schedular\ExamTimetable\DTO\ResponseDTO;
use App\Schedular\ExamTimetable\Engine\SchedularEngine;
use App\Schedular\ExamTimetable\Solution\Catalog\ConstraintCatalog;
use App\Schedular\ExamTimetable\Solution\DTO\Issue;
use App\Schedular\ExamTimetable\Solution\Support\ExamPayload;
use App\Schedular\ExamTimetable\Solution\Support\RequestEdit;
use App\Schedular\ExamTimetable\Support\Time;
use App\Services\ExamTimetable\ApplyExamSolutionService;
use Illuminate\Contracts\Console\Kernel;
use Tests\TestCase;

class ExamSchedulerEngineTest extends TestCase
{
    public function createApplication()
    {
        $app = require __DIR__ . '/../../../bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    private function payload(array $overrides = []): array
    {
        return array_replace_recursive([
            'start_date' => '2026-12-07',
            'end_date' => '2026-12-09',
            'specialty_id' => 'spec-1',
            'candidate_count' => 120,
            'courses' => [
                ['course_id' => 'c1', 'course_name' => 'Algorithms'],
                ['course_id' => 'c2', 'course_name' => 'Databases'],
                ['course_id' => 'c3', 'course_name' => 'Networks'],
            ],
            'halls' => [
                ['hall_id' => 'h1', 'hall_name' => 'Hall A', 'capacity' => 80],
                ['hall_id' => 'h2', 'hall_name' => 'Hall B', 'capacity' => 60],
                ['hall_id' => 'h3', 'hall_name' => 'Hall C', 'capacity' => 150],
            ],
            'invigilators' => [
                ['invigilator_id' => 'i1', 'name' => 'Ann', 'course_taught' => ['c1']],
                ['invigilator_id' => 'i2', 'name' => 'Ben', 'course_taught' => []],
                ['invigilator_id' => 'i3', 'name' => 'Cat', 'course_taught' => []],
            ],
            'hall_busy_slots' => [],
            'invigilator_busy_slots' => [],
            'hard_constraints' => [
                'operational_period' => ['start_time' => '08:00', 'end_time' => '17:00'],
                'session_duration' => ['duration' => 120],
            ],
            'soft_constraints' => [],
        ], $overrides);
    }

    private function schedule(array $payload, bool $withSolutions = true): ResponseDTO
    {
        return app(SchedularEngine::class)->run($payload, $withSolutions);
    }

    private function sessions(ResponseDTO $response): array
    {
        return collect($response->timetable)->flatMap(fn($date) => $date['sessions'])->all();
    }

    private function diagnostic(ResponseDTO $response, string $severity, string $type): ?object
    {
        return $response->diagnostics[$severity]->first(fn($diagnostic) => $diagnostic->constraint_failed['type'] === $type);
    }

    private function paths(ResponseDTO $response): array
    {
        return collect($response->suggestions)->flatten(1)->all();
    }

    private function edit(ResponseDTO $response): RequestEdit
    {
        $subjects = [];
        foreach ([Issue::HARD, Issue::SOFT] as $severity) {
            foreach ($response->diagnostics[$severity] as $diagnostic) {
                $issue = Issue::fromDiagnostic($diagnostic, $severity);
                $subjects[$issue->id()] = $issue->subject;
                foreach ($issue->obstacles as $obstacle) {
                    $subjects[$obstacle->id] = $obstacle;
                }
            }
        }

        return new RequestEdit(app(ConstraintCatalog::class), $subjects);
    }

    private function applyPath(array $payload, array $path, RequestEdit $edit): array
    {
        $catalog = app(ConstraintCatalog::class);
        $request = ExamPayload::toRequest($payload);

        foreach ($path['steps'] as $step) {
            $request = $catalog->for($step['target']['type'])->applyToRequest($request, $step, $edit);
        }

        return ExamPayload::withRequest($payload, $request);
    }

    public function test_every_course_gets_one_session_with_enough_seats_and_an_invigilator_per_hall(): void
    {
        $response = $this->schedule($this->payload());

        $this->assertSame('optimal', $response->status);
        $sessions = $this->sessions($response);
        $this->assertEqualsCanonicalizing(['c1', 'c2', 'c3'], array_column($sessions, 'course_id'));

        foreach ($sessions as $session) {
            $this->assertSame(120, array_sum(array_column($session['halls'], 'candidate_count')));
            foreach ($session['halls'] as $hall) {
                $this->assertNotEmpty($hall['invigilators']);
                $this->assertLessThanOrEqual($hall['capacity'], $hall['candidate_count']);
            }
        }

        // Students never sit two exams at once.
        foreach (collect($sessions)->groupBy('date') as $daySessions) {
            $daySessions = $daySessions->values();
            for ($i = 0; $i < $daySessions->count(); $i++) {
                for ($j = $i + 1; $j < $daySessions->count(); $j++) {
                    $this->assertFalse(Time::overlaps(
                        Time::toMinutes($daySessions[$i]['start_time']), Time::toMinutes($daySessions[$i]['end_time']),
                        Time::toMinutes($daySessions[$j]['start_time']), Time::toMinutes($daySessions[$j]['end_time'])
                    ));
                }
            }
        }
    }

    public function test_course_teachers_do_not_invigilate_their_own_exam_when_others_are_free(): void
    {
        $sessions = $this->sessions($this->schedule($this->payload()));
        $algorithms = collect($sessions)->firstWhere('course_id', 'c1');

        $invigilators = collect($algorithms['halls'])->flatMap(fn($hall) => array_column($hall['invigilators'], 'invigilator_id'));
        $this->assertNotContains('i1', $invigilators);
    }

    public function test_halls_busy_at_an_overlapping_time_are_not_used_and_exactly_shared_halls_are(): void
    {
        $payload = $this->payload([
            'end_date' => '2026-12-07',
            'courses' => [['course_id' => 'c1', 'course_name' => 'Algorithms']],
            'hard_constraints' => ['operational_period' => ['start_time' => '08:00', 'end_time' => '10:00']],
            'hall_busy_slots' => [
                ['hall_id' => 'h3', 'date' => '2026-12-07', 'slots' => [['start_time' => '09:00', 'end_time' => '11:00', 'invigilators' => ['other'], 'candidate_groups' => [['candidate_count' => 10]]]]],
                ['hall_id' => 'h1', 'date' => '2026-12-07', 'slots' => [['start_time' => '08:00', 'end_time' => '10:00', 'invigilators' => ['other'], 'candidate_groups' => [['candidate_count' => 20]]]]],
            ],
        ]);

        $session = $this->sessions($this->schedule($payload))[0];
        $halls = collect($session['halls'])->keyBy('hall_id');

        $this->assertFalse($halls->has('h3'));
        $this->assertTrue($halls['h1']['shared']);
        $this->assertSame(60, $halls['h1']['candidate_count']);
        $this->assertSame(['other'], array_column($halls['h1']['invigilators'], 'invigilator_id'));
    }

    public function test_joint_course_is_fixed_at_its_time_and_a_joint_course_outside_hours_is_a_hard_failure(): void
    {
        $payload = $this->payload(['hard_constraints' => ['required_joint_course_periods' => [
            ['course_id' => 'c3', 'date' => '2026-12-08', 'start_time' => '09:00', 'end_time' => '11:00'],
        ]]]);

        $joint = collect($this->sessions($this->schedule($payload)))->firstWhere('course_id', 'c3');
        $this->assertSame(['joint', '2026-12-08', '09:00'], [$joint['type'], $joint['date'], $joint['start_time']]);

        $payload['hard_constraints']['required_joint_course_periods'][0]['start_time'] = '16:00';
        $payload['hard_constraints']['required_joint_course_periods'][0]['end_time'] = '18:00';
        $response = $this->schedule($payload);

        $this->assertSame('error', $response->status);
        $failure = $this->diagnostic($response, 'hard', RequiredJointCourseSession::KEY);
        $this->assertSame(OperationalPeriod::KEY, $failure->blockers[0]->type);
        $this->assertNull(collect($this->sessions($response))->firstWhere('course_id', 'c3'), 'A failed joint course is never placed elsewhere.');

        $extend = collect($this->paths($response))->first(fn($path) => in_array($failure->constraint_failed['id'], $path['issue_ids'], true));
        $this->assertTrue($extend['verified']);
        $this->assertStringContainsString('08:00 – 18:00', $extend['summary']);
    }

    public function test_course_time_request_is_pinned_and_a_blocked_request_gets_a_verified_move(): void
    {
        $payload = $this->payload([
            'hard_constraints' => ['required_joint_course_periods' => [
                ['course_id' => 'c3', 'date' => '2026-12-08', 'start_time' => '08:00', 'end_time' => '10:00'],
            ]],
            'soft_constraints' => [CourseTimeRequest::KEY => [
                ['course_id' => 'c1', 'date' => '2026-12-07', 'start_time' => '13:00', 'end_time' => '15:00'],
                ['course_id' => 'c2', 'date' => '2026-12-08', 'start_time' => '09:00', 'end_time' => '11:00'],
            ]],
        ]);

        $response = $this->schedule($payload);
        $this->assertSame('partial', $response->status);

        $c1 = collect($this->sessions($response))->firstWhere('course_id', 'c1');
        $this->assertSame(['2026-12-07', '13:00', CourseTimeRequest::KEY], [$c1['date'], $c1['start_time'], $c1['source']]);

        $failure = $this->diagnostic($response, 'soft', CourseTimeRequest::KEY);
        $this->assertSame('c2', $failure->constraint_failed['details']['course_id']);
        $this->assertSame(RequiredJointCourse::KEY, $failure->blockers[0]->type);

        $move = collect($this->paths($response))->first(fn($path) => $path['steps'][0]['action'] === 'move');
        $this->assertTrue($move['verified']);

        $fixed = $this->schedule($this->applyPath($payload, $move, $this->edit($response)), false);
        $this->assertSame('optimal', $fixed->status);
    }

    public function test_course_that_does_not_fit_is_a_hard_failure_with_a_verified_hours_fix(): void
    {
        $payload = $this->payload([
            'end_date' => '2026-12-07',
            'hard_constraints' => ['operational_period' => ['start_time' => '08:00', 'end_time' => '12:00']],
        ]);

        $response = $this->schedule($payload);

        $this->assertSame('error', $response->status);
        $this->assertCount(1, $response->unscheduled);
        $failure = $this->diagnostic($response, 'hard', CourseExamSession::KEY);
        $this->assertSame('no_free_time', $failure->blockers[0]->entity['reason']);

        $path = collect($this->paths($response))->firstWhere('verified', true);
        $this->assertNotNull($path);

        $patched = $this->applyPath($payload, $path, $this->edit($response));
        $this->assertSame('optimal', $this->schedule($patched, false)->status);
    }

    public function test_requested_assignment_in_a_busy_hall_offers_a_hall_swap_that_applies_to_the_request(): void
    {
        $payload = $this->payload([
            'hall_busy_slots' => [['hall_id' => 'h3', 'date' => '2026-12-07', 'start_time' => '08:00', 'end_time' => '12:00']],
            'soft_constraints' => [RequestedAssignment::KEY => [
                ['course_id' => 'c1', 'date' => '2026-12-07', 'start_time' => '09:00', 'end_time' => '11:00', 'hall_id' => 'h3'],
            ]],
        ]);
        $payload['halls'][] = ['hall_id' => 'h4', 'hall_name' => 'Hall D', 'capacity' => 200];

        $response = $this->schedule($payload);
        $failure = $this->diagnostic($response, 'soft', RequestedAssignment::KEY);
        $this->assertSame(HallBusy::KEY, $failure->blockers[0]->type);

        $swap = collect($this->paths($response))->first(fn($path) => $path['steps'][0]['action'] === 'replace');
        $this->assertTrue($swap['verified']);

        $request = ExamPayload::toRequest($this->applyPath($payload, $swap, $this->edit($response)));
        $this->assertSame('h4', $request[RequestedAssignment::KEY][0]['hall_id']);
    }

    public function test_daily_maximum_is_kept_when_possible_and_relaxed_only_when_needed(): void
    {
        $payload = $this->payload(['soft_constraints' => [StudentDailyLoadRange::KEY => ['min_sessions' => 1, 'max_sessions' => 1]]]);
        $response = $this->schedule($payload);

        $this->assertSame('optimal', $response->status);
        $this->assertCount(3, $response->timetable);

        $payload['end_date'] = '2026-12-08';
        $response = $this->schedule($payload);
        $this->assertSame('partial', $response->status);
        $failure = $this->diagnostic($response, 'soft', StudentDailyLoadRange::KEY);
        $this->assertSame('max', $failure->constraint_failed['details']['breach']);

        $relax = collect($this->paths($response))->first();
        $this->assertSame('relax', $relax['steps'][0]['action']);
        $this->assertTrue($relax['verified']);
    }

    public function test_requested_invigilator_is_used_for_an_exam_in_their_window(): void
    {
        $payload = $this->payload([
            'soft_constraints' => [
                CourseTimeRequest::KEY => [['course_id' => 'c2', 'date' => '2026-12-07', 'start_time' => '08:00', 'end_time' => '10:00']],
                InvigilatorRequestedSlot::KEY => [['invigilator_id' => 'i3', 'date' => '2026-12-07', 'start_time' => '08:00', 'end_time' => '10:00']],
            ],
        ]);

        $response = $this->schedule($payload);
        $c2 = collect($this->sessions($response))->firstWhere('course_id', 'c2');

        $this->assertContains('i3', collect($c2['halls'])->flatMap(fn($hall) => array_column($hall['invigilators'], 'invigilator_id'))->all());
        $this->assertNull($this->diagnostic($response, 'soft', InvigilatorRequestedSlot::KEY));
    }

    public function test_issue_ids_are_stable_between_runs(): void
    {
        $payload = $this->payload(['end_date' => '2026-12-07', 'hard_constraints' => ['operational_period' => ['end_time' => '12:00']]]);

        $first = $this->schedule($payload, false)->diagnostics['hard']->pluck('id')->all();
        $second = $this->schedule($payload, false)->diagnostics['hard']->pluck('id')->all();

        $this->assertNotEmpty($first);
        $this->assertSame($first, $second);
    }

    public function test_stored_solutions_apply_to_the_generate_request_like_the_apply_endpoint(): void
    {
        $request = [
            'exam_id' => 'exam-1',
            'operational_period' => ['start_time' => '08:00', 'end_time' => '12:00'],
            'session_duration' => ['duration' => 120],
        ];
        $payload = ExamPayload::withRequest($this->payload(['end_date' => '2026-12-07']), $request);
        $response = $this->schedule($payload);

        // What the job stores in Mongo and the services read back.
        $stored = json_decode(json_encode([
            'hard' => $response->diagnostics['hard'],
            'soft' => $response->diagnostics['soft'],
        ]), true);
        $paths = collect(json_decode(json_encode($response->suggestions), true))->flatten(1);

        $service = app(ApplyExamSolutionService::class);
        $path = $paths->firstWhere('verified', true);
        [$adjusted, $changes] = $service->assess($request, $path, $service->editFor($stored));

        $this->assertTrue(collect($changes)->every(fn($change) => $change['applied']));
        $this->assertSame('exam-1', $adjusted['exam_id']);
        $this->assertSame('2026-12-07', $adjusted['operational_period']['date_exceptions'][0]['date']);

        $manual = $paths->first(fn($path) => collect($path['steps'])->contains('action', 'review'));
        if ($manual) {
            [, $changes] = $service->assess($request, $manual, $service->editFor($stored));
            $this->assertFalse(collect($changes)->every(fn($change) => $change['applied']));
        }
    }
}
