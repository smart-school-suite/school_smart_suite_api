<?php

namespace Tests\Unit\ExamTimetable;

use App\Schedular\ExamTimetable\DTO\DiagnosticDTO;
use App\Schedular\ExamTimetable\DTO\ResponseDTO;
use App\Schedular\ExamTimetable\Engine\SchedularEngine;
use App\Schedular\ExamTimetable\Placement\Support\Time;
use Tests\TestCase;

/**
 * Runs the exam scheduler on in-memory input; no database needed.
 */
class ExamTimetableEngineTest extends TestCase
{
    private function payload(array $overrides = []): array
    {
        return array_replace_recursive([
            'exam_id' => 'exam-1',
            'specialty_id' => 'spec-1',
            'start_date' => '2026-12-07',
            'end_date' => '2026-12-11',
            'candidate_count' => 60,
            'courses' => [
                ['course_id' => 'c1', 'course_title' => 'Algorithms', 'course_code' => 'CS101'],
                ['course_id' => 'c2', 'course_title' => 'Databases', 'course_code' => 'CS102'],
                ['course_id' => 'c3', 'course_title' => 'Networks', 'course_code' => 'CS103'],
                ['course_id' => 'c4', 'course_title' => 'Statistics', 'course_code' => 'CS104'],
            ],
            'halls' => [
                ['hall_id' => 'h-small', 'hall_name' => 'Small', 'capacity' => 30],
                ['hall_id' => 'h-mid', 'hall_name' => 'Middle', 'capacity' => 40],
                ['hall_id' => 'h-big', 'hall_name' => 'Big', 'capacity' => 100],
            ],
            'invigilators' => [
                ['invigilator_id' => 'i1', 'name' => 'Ada', 'courses_taught' => ['c1']],
                ['invigilator_id' => 'i2', 'name' => 'Ben', 'courses_taught' => ['c2']],
                ['invigilator_id' => 'i3', 'name' => 'Cy', 'courses_taught' => []],
                ['invigilator_id' => 'i4', 'name' => 'Di', 'courses_taught' => ['c3', 'c4']],
            ],
            'hall_busy_periods' => [],
            'invigilator_busy_periods' => [],
            'hard_constraints' => [
                'operational_period' => ['start_time' => '08:00', 'end_time' => '17:00'],
                'session_duration' => ['duration' => 120],
            ],
            'soft_constraints' => [],
        ], $overrides);
    }

    private function generate(array $payload): ResponseDTO
    {
        return app(SchedularEngine::class)->run($payload);
    }

    /** @return array<int, array> every session with its date */
    private function sessions(ResponseDTO $response): array
    {
        $sessions = [];
        foreach ($response->timetable as $day) {
            foreach ($day['slots'] as $slot) {
                $sessions[] = [...$slot, 'date' => $day['date']];
            }
        }
        return $sessions;
    }

    private function keys(ResponseDTO $response, string $severity): array
    {
        return $response->diagnostics[$severity]->map(fn(DiagnosticDTO $d) => $d->constraint_failed['type'])->all();
    }

    private function blockerTypes(ResponseDTO $response, string $severity): array
    {
        return $response->diagnostics[$severity]
            ->flatMap(fn(DiagnosticDTO $d) => array_map(fn($b) => $b->type, $d->blockers))
            ->all();
    }

    private function assertValidTimetable(array $payload, ResponseDTO $response): void
    {
        $sessions = $this->sessions($response);
        $teaches = collect($payload['invigilators'])->mapWithKeys(fn($i) => [$i['invigilator_id'] => $i['courses_taught']]);

        $courseIds = array_column($sessions, 'course_id');
        $this->assertSame(count($courseIds), count(array_unique($courseIds)), 'a course was examined twice');

        foreach ($sessions as $session) {
            $this->assertGreaterThanOrEqual(
                $session['candidate_count'],
                array_sum(array_column($session['halls'], 'candidate_count')),
                "{$session['course_id']} does not seat every candidate"
            );

            foreach ($session['halls'] as $hall) {
                $this->assertLessThanOrEqual($hall['capacity'], $hall['candidate_count']);
                $this->assertNotEmpty($hall['invigilators'], "hall {$hall['hall_id']} has no invigilator");
                foreach ($hall['invigilators'] as $invigilator) {
                    $this->assertNotContains(
                        $session['course_id'],
                        $teaches[$invigilator['invigilator_id']] ?? [],
                        "{$invigilator['invigilator_id']} invigilates a course they teach"
                    );
                }
            }
        }

        // Same students: no two exams overlap. Nobody and no hall is in two places at once.
        foreach ($sessions as $i => $a) {
            foreach ($sessions as $j => $b) {
                if ($i >= $j || $a['date'] !== $b['date']) {
                    continue;
                }
                $overlap = Time::overlaps(
                    Time::toMinutes($a['start_time']), Time::toMinutes($a['end_time']),
                    Time::toMinutes($b['start_time']), Time::toMinutes($b['end_time'])
                );
                $this->assertFalse($overlap, "{$a['course_id']} and {$b['course_id']} overlap on {$a['date']}");
            }
        }
    }

    public function test_places_every_course_without_teachers_invigilating_their_own_course(): void
    {
        $payload = $this->payload();
        $response = $this->generate($payload);

        $this->assertSame('optimal', $response->status, json_encode($this->keys($response, 'hard')));
        $this->assertCount(4, $this->sessions($response));
        $this->assertSame([], $response->unplaced_courses);
        $this->assertValidTimetable($payload, $response);
    }

    public function test_spreads_exams_over_the_exam_period(): void
    {
        $response = $this->generate($this->payload());
        $dates = array_unique(array_column($this->sessions($response), 'date'));

        // Four courses over five days: one exam per day, first and last days used.
        $this->assertCount(4, $dates);
        $this->assertContains('2026-12-07', $dates);
        $this->assertContains('2026-12-11', $dates);
    }

    public function test_uses_the_smallest_hall_that_seats_everyone(): void
    {
        $response = $this->generate($this->payload());

        foreach ($this->sessions($response) as $session) {
            $this->assertSame(['h-big'], array_column($session['halls'], 'hall_id'));
        }
    }

    public function test_splits_candidates_over_halls_when_no_hall_is_big_enough(): void
    {
        $payload = $this->payload(['halls' => [
            ['hall_id' => 'h-small', 'hall_name' => 'Small', 'capacity' => 30],
            ['hall_id' => 'h-mid', 'hall_name' => 'Middle', 'capacity' => 40],
            ['hall_id' => 'h-big', 'hall_name' => 'Big', 'capacity' => 10],
        ]]);
        $response = $this->generate($payload);

        $this->assertSame('optimal', $response->status);
        $this->assertValidTimetable($payload, $response);
        foreach ($this->sessions($response) as $session) {
            $this->assertCount(2, $session['halls']);
            $this->assertSame(60, array_sum(array_column($session['halls'], 'candidate_count')));
        }
    }

    public function test_a_course_only_its_teachers_could_invigilate_is_not_placed(): void
    {
        $payload = $this->payload([
            'courses' => [['course_id' => 'c1', 'course_title' => 'Algorithms']],
            'invigilators' => [
                ['invigilator_id' => 'i1', 'name' => 'Ada', 'courses_taught' => ['c1']],
            ],
        ]);
        $payload['invigilators'] = [['invigilator_id' => 'i1', 'name' => 'Ada', 'courses_taught' => ['c1']]];
        $payload['courses'] = [['course_id' => 'c1', 'course_title' => 'Algorithms']];

        $response = $this->generate($payload);

        $this->assertSame('error', $response->status);
        $this->assertSame(['c1'], $response->unplaced_courses);
        $this->assertContains('course_placement', $this->keys($response, 'hard'));
        $this->assertContains('invigilator_teaches_course', $this->blockerTypes($response, 'hard'));
    }

    public function test_daily_maximum_is_kept_and_breaking_it_is_reported(): void
    {
        $payload = $this->payload([
            'end_date' => '2026-12-08',
            'soft_constraints' => ['student_daily_load_range' => ['min_sessions' => 1, 'max_sessions' => 1]],
        ]);
        $payload['courses'] = array_slice($payload['courses'], 0, 3);

        $response = $this->generate($payload);

        $this->assertSame('partial', $response->status);
        $this->assertCount(3, $this->sessions($response));
        $this->assertContains('student_daily_load_range', $this->keys($response, 'soft'));
        $this->assertValidTimetable($payload, $response);
    }

    public function test_daily_minimum_groups_exams_on_fewer_days(): void
    {
        $payload = $this->payload([
            'soft_constraints' => ['student_daily_load_range' => ['min_sessions' => 2, 'max_sessions' => 2]],
        ]);
        $response = $this->generate($payload);

        $this->assertSame('optimal', $response->status, json_encode($this->keys($response, 'soft')));
        $this->assertCount(2, array_unique(array_column($this->sessions($response), 'date')));
        $this->assertValidTimetable($payload, $response);
    }

    public function test_joint_course_is_placed_at_its_fixed_time(): void
    {
        $payload = $this->payload(['hard_constraints' => ['required_joint_course_periods' => [
            ['course_id' => 'c2', 'date' => '2026-12-09', 'start_time' => '10:00', 'end_time' => '12:00'],
        ]]]);
        $response = $this->generate($payload);

        $joint = collect($this->sessions($response))->firstWhere('course_id', 'c2');
        $this->assertSame('2026-12-09', $joint['date']);
        $this->assertSame('10:00', $joint['start_time']);
        $this->assertSame('joint', $joint['slot_type']);
        $this->assertValidTimetable($payload, $response);
    }

    public function test_joint_course_outside_opening_hours_is_a_hard_failure(): void
    {
        $response = $this->generate($this->payload(['hard_constraints' => ['required_joint_course_periods' => [
            ['course_id' => 'c2', 'date' => '2026-12-09', 'start_time' => '16:00', 'end_time' => '18:00'],
        ]]]));

        $this->assertSame('error', $response->status);
        $this->assertSame(['required_joint_course_periods'], $this->keys($response, 'hard'));
        $this->assertContains('operational_period_violation', $this->blockerTypes($response, 'hard'));
    }

    public function test_course_requested_slot_is_honoured(): void
    {
        $payload = $this->payload(['soft_constraints' => ['course_requested_slot' => [
            ['course_id' => 'c3', 'date' => '2026-12-10', 'start_time' => '13:00', 'end_time' => '15:00'],
        ]]]);
        $response = $this->generate($payload);

        $session = collect($this->sessions($response))->firstWhere('course_id', 'c3');
        $this->assertSame(['2026-12-10', '13:00'], [$session['date'], $session['start_time']]);
        $this->assertSame('course_requested_slot', $session['source']);
        $this->assertSame('optimal', $response->status);
    }

    public function test_course_request_clashing_with_a_joint_course_is_reported_and_placed_elsewhere(): void
    {
        $payload = $this->payload([
            'hard_constraints' => ['required_joint_course_periods' => [
                ['course_id' => 'c2', 'date' => '2026-12-09', 'start_time' => '10:00', 'end_time' => '12:00'],
            ]],
            'soft_constraints' => ['course_requested_slot' => [
                ['course_id' => 'c3', 'date' => '2026-12-09', 'start_time' => '11:00', 'end_time' => '13:00'],
            ]],
        ]);
        $response = $this->generate($payload);

        $this->assertSame('partial', $response->status);
        $this->assertSame(['course_requested_slot'], $this->keys($response, 'soft'));
        $this->assertContains('joint_course_period_violation', $this->blockerTypes($response, 'soft'));
        $this->assertNotNull(collect($this->sessions($response))->firstWhere('course_id', 'c3'));
        $this->assertValidTimetable($payload, $response);
    }

    public function test_requested_assignment_with_the_course_teacher_is_refused(): void
    {
        $payload = $this->payload(['soft_constraints' => ['requested_assignment' => [[
            'course_id' => 'c1', 'date' => '2026-12-08', 'start_time' => '08:00', 'end_time' => '10:00',
            'hall_ids' => ['h-big'], 'invigilator_ids' => ['i1'],
        ]]]]);
        $response = $this->generate($payload);

        $this->assertSame('partial', $response->status);
        $this->assertContains('invigilator_teaches_course', $this->blockerTypes($response, 'soft'));
        // The course is still examined, by placement, with someone else.
        $this->assertValidTimetable($payload, $response);
        $this->assertCount(4, $this->sessions($response));
    }

    public function test_requested_assignment_uses_the_requested_hall_and_invigilator(): void
    {
        $payload = $this->payload(['soft_constraints' => ['requested_assignment' => [[
            'course_id' => 'c1', 'date' => '2026-12-08', 'start_time' => '08:00', 'end_time' => '10:00',
            'hall_ids' => ['h-small', 'h-mid'], 'invigilator_ids' => ['i3'],
        ]]]]);
        $response = $this->generate($payload);
        $session = collect($this->sessions($response))->firstWhere('course_id', 'c1');

        $this->assertSame('optimal', $response->status);
        $this->assertSame(['h-small', 'h-mid'], array_column($session['halls'], 'hall_id'));
        $invigilators = collect($session['halls'])->flatMap(fn($hall) => array_column($hall['invigilators'], 'invigilator_id'))->all();
        $this->assertContains('i3', $invigilators);
        $this->assertValidTimetable($payload, $response);
    }

    public function test_halls_and_invigilators_busy_in_other_exams_are_not_used(): void
    {
        $busyAllWeek = fn(string $key, string $id) => array_map(fn(string $date) => [
            $key => $id, 'date' => $date, 'start_time' => '08:00', 'end_time' => '17:00',
        ], ['2026-12-07', '2026-12-08', '2026-12-09', '2026-12-10', '2026-12-11']);

        $payload = $this->payload();
        $payload['hall_busy_periods'] = $busyAllWeek('hall_id', 'h-big');
        $payload['invigilator_busy_periods'] = $busyAllWeek('invigilator_id', 'i3');
        $response = $this->generate($payload);

        $this->assertSame('optimal', $response->status);
        foreach ($this->sessions($response) as $session) {
            $this->assertNotContains('h-big', array_column($session['halls'], 'hall_id'));
            foreach ($session['halls'] as $hall) {
                $this->assertNotContains('i3', array_column($hall['invigilators'], 'invigilator_id'));
            }
        }
        $this->assertValidTimetable($payload, $response);
    }

    public function test_invigilator_request_adds_them_to_the_exam_at_that_time(): void
    {
        $payload = $this->payload(['soft_constraints' => [
            'course_requested_slot' => [['course_id' => 'c2', 'date' => '2026-12-08', 'start_time' => '09:00', 'end_time' => '11:00']],
            'invigilator_requested_slot' => [['invigilator_id' => 'i4', 'date' => '2026-12-08', 'start_time' => '09:00', 'end_time' => '11:00']],
        ]]);
        $response = $this->generate($payload);
        $session = collect($this->sessions($response))->firstWhere('course_id', 'c2');
        $invigilators = collect($session['halls'])->flatMap(fn($hall) => array_column($hall['invigilators'], 'invigilator_id'))->all();

        $this->assertContains('i4', $invigilators);
        $this->assertSame('optimal', $response->status);
    }

    public function test_invigilator_request_for_their_own_course_is_refused(): void
    {
        $payload = $this->payload(['soft_constraints' => [
            'course_requested_slot' => [['course_id' => 'c2', 'date' => '2026-12-08', 'start_time' => '09:00', 'end_time' => '11:00']],
            'invigilator_requested_slot' => [['invigilator_id' => 'i2', 'date' => '2026-12-08', 'start_time' => '09:00', 'end_time' => '11:00']],
        ]]);
        $response = $this->generate($payload);

        $this->assertSame('partial', $response->status);
        $this->assertSame(['invigilator_requested_slot'], $this->keys($response, 'soft'));
        $this->assertContains('invigilator_teaches_course', $this->blockerTypes($response, 'soft'));
        $this->assertValidTimetable($payload, $response);
    }

    public function test_hall_request_is_used_by_the_exam_at_that_time(): void
    {
        $payload = $this->payload(['soft_constraints' => [
            'course_requested_slot' => [['course_id' => 'c2', 'date' => '2026-12-08', 'start_time' => '09:00', 'end_time' => '11:00']],
            'hall_requested_slot' => [['hall_id' => 'h-mid', 'date' => '2026-12-08', 'start_time' => '09:00', 'end_time' => '11:00']],
        ]]);
        $response = $this->generate($payload);
        $session = collect($this->sessions($response))->firstWhere('course_id', 'c2');

        $this->assertContains('h-mid', array_column($session['halls'], 'hall_id'));
        $this->assertSame('optimal', $response->status);
        $this->assertValidTimetable($payload, $response);
    }

    public function test_one_invigilator_per_n_candidates(): void
    {
        $payload = $this->payload(['candidates_per_invigilator' => 30]);
        $response = $this->generate($payload);

        foreach ($this->sessions($response) as $session) {
            $this->assertCount(2, $session['halls'][0]['invigilators']);
        }
        $this->assertValidTimetable($payload, $response);
    }

    public function test_exam_longer_than_opening_hours_is_a_hard_failure(): void
    {
        $response = $this->generate($this->payload(['hard_constraints' => ['session_duration' => [
            'duration' => 120,
            'course_exceptions' => [['course_id' => 'c4', 'duration' => 600]],
        ]]]));

        $this->assertSame('error', $response->status);
        $this->assertSame(['session_duration'], $this->keys($response, 'hard'));
        $this->assertSame([], $this->sessions($response));
    }

    public function test_excluded_dates_and_date_hours_are_respected(): void
    {
        $payload = $this->payload(['hard_constraints' => ['operational_period' => [
            'start_time' => '08:00',
            'end_time' => '17:00',
            'date_exclusions' => ['2026-12-07', '2026-12-08'],
            'date_exceptions' => [['date' => '2026-12-09', 'start_time' => '13:00', 'end_time' => '17:00']],
        ]]]);
        $payload['courses'] = array_slice($payload['courses'], 0, 3);
        $response = $this->generate($payload);

        $sessions = $this->sessions($response);
        $this->assertSame([], array_values(array_intersect(['2026-12-07', '2026-12-08'], array_column($sessions, 'date'))));
        foreach ($sessions as $session) {
            if ($session['date'] === '2026-12-09') {
                $this->assertGreaterThanOrEqual('13:00', $session['start_time']);
            }
        }
        $this->assertSame('optimal', $response->status);
    }
}
