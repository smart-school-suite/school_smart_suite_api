<?php

namespace Tests\Unit\ExamTimetable;

use App\Constant\Violation\ExamTimetable\Student\CandidateBusy;
use App\Schedular\ExamTimetable\DTO\DiagnosticDTO;
use App\Schedular\ExamTimetable\DTO\ResponseDTO;
use App\Schedular\ExamTimetable\Engine\SchedularEngine;
use App\Schedular\ExamTimetable\Placement\Support\Time;
use Tests\TestCase;

/**
 * Courses that list their own candidates (resit exams): only exams sharing a
 * candidate clash, the daily load is per candidate, and candidates' other
 * exams are respected.
 */
class CandidateGroupSchedulingTest extends TestCase
{
    private function payload(array $overrides = []): array
    {
        return array_replace([
            'exam_id' => 'resit-1',
            'start_date' => '2026-12-07',
            'end_date' => '2026-12-07',
            'candidate_count' => 6,
            'courses' => [
                ['course_id' => 'c1', 'course_title' => 'Algorithms', 'candidate_count' => 2, 'student_ids' => ['s1', 's2']],
                ['course_id' => 'c2', 'course_title' => 'Databases', 'candidate_count' => 2, 'student_ids' => ['s3', 's4']],
                ['course_id' => 'c3', 'course_title' => 'Networks', 'candidate_count' => 2, 'student_ids' => ['s1', 's5']],
            ],
            'halls' => [
                ['hall_id' => 'h1', 'hall_name' => 'One', 'capacity' => 10],
                ['hall_id' => 'h2', 'hall_name' => 'Two', 'capacity' => 10],
            ],
            'invigilators' => [
                ['invigilator_id' => 'i1', 'name' => 'Ada', 'courses_taught' => []],
                ['invigilator_id' => 'i2', 'name' => 'Ben', 'courses_taught' => []],
            ],
            'hall_busy_periods' => [],
            'invigilator_busy_periods' => [],
            'hard_constraints' => [
                'operational_period' => ['start_time' => '08:00', 'end_time' => '12:00'],
                'session_duration' => ['duration' => 120],
            ],
            'soft_constraints' => [],
        ], $overrides);
    }

    private function generate(array $payload): ResponseDTO
    {
        return app(SchedularEngine::class)->run($payload);
    }

    private function sessions(ResponseDTO $response): array
    {
        $sessions = [];
        foreach ($response->timetable as $day) {
            foreach ($day['slots'] as $slot) {
                $sessions[$slot['course_id']] = [...$slot, 'date' => $day['date']];
            }
        }
        return $sessions;
    }

    private function overlap(array $a, array $b): bool
    {
        return $a['date'] === $b['date'] && Time::overlaps(
            Time::toMinutes($a['start_time']), Time::toMinutes($a['end_time']),
            Time::toMinutes($b['start_time']), Time::toMinutes($b['end_time'])
        );
    }

    public function test_exams_without_shared_candidates_run_side_by_side(): void
    {
        // One day with room for two 2-hour exams: three courses only fit because c2 shares nobody.
        $response = $this->generate($this->payload());
        $sessions = $this->sessions($response);

        $this->assertSame('optimal', $response->status);
        $this->assertCount(3, $sessions);
        $this->assertFalse($this->overlap($sessions['c1'], $sessions['c3']), 'c1 and c3 share s1');

        // No hall or invigilator is in two places at once.
        $courses = array_values($sessions);
        foreach ($courses as $i => $a) {
            foreach (array_slice($courses, $i + 1) as $b) {
                if (!$this->overlap($a, $b)) {
                    continue;
                }
                $this->assertEmpty(array_intersect(array_column($a['halls'], 'hall_id'), array_column($b['halls'], 'hall_id')));
                $people = fn($s) => collect($s['halls'])->flatMap(fn($h) => array_column($h['invigilators'], 'invigilator_id'))->all();
                $this->assertEmpty(array_intersect($people($a), $people($b)));
            }
        }
    }

    public function test_without_student_ids_every_exam_still_clashes(): void
    {
        $payload = $this->payload();
        $payload['courses'] = array_map(fn($c) => array_diff_key($c, ['student_ids' => true]), $payload['courses']);

        $response = $this->generate($payload);

        // Only two 2-hour exams fit in the day when all candidates are the same.
        $this->assertSame('error', $response->status);
        $this->assertCount(2, $this->sessions($response));
    }

    public function test_candidates_sitting_another_exam_are_not_double_booked(): void
    {
        $response = $this->generate($this->payload([
            'courses' => [
                ['course_id' => 'c1', 'course_title' => 'Algorithms', 'candidate_count' => 2, 'student_ids' => ['s1', 's2']],
            ],
            'candidate_busy_periods' => [
                ['student_id' => 's2', 'date' => '2026-12-07', 'start_time' => '08:00', 'end_time' => '10:00', 'course_id' => 'other', 'source' => 'other_exam'],
            ],
        ]));

        $session = $this->sessions($response)['c1'];
        $this->assertGreaterThanOrEqual(Time::toMinutes('10:00'), Time::toMinutes($session['start_time']));
    }

    public function test_a_course_whose_candidates_are_always_busy_reports_why(): void
    {
        $response = $this->generate($this->payload([
            'courses' => [
                ['course_id' => 'c1', 'course_title' => 'Algorithms', 'candidate_count' => 2, 'student_ids' => ['s1', 's2']],
            ],
            'candidate_busy_periods' => [
                ['student_id' => 's1', 'date' => '2026-12-07', 'start_time' => '08:00', 'end_time' => '12:00', 'source' => 'other_exam'],
            ],
        ]));

        $this->assertSame('error', $response->status);
        $blockers = $response->diagnostics['hard']
            ->flatMap(fn(DiagnosticDTO $d) => array_map(fn($b) => $b->type, $d->blockers))
            ->all();
        $this->assertContains(CandidateBusy::KEY, $blockers);

        // The solution planner explains it and never crashes on the new key.
        $labels = json_encode($response->suggestions);
        $this->assertStringContainsString('candidates', strtolower($labels));
    }

    public function test_daily_load_is_counted_per_candidate(): void
    {
        $response = $this->generate($this->payload([
            'end_date' => '2026-12-08',
            'soft_constraints' => ['student_daily_load_range' => ['max_sessions' => 1]],
        ]));
        $sessions = $this->sessions($response);

        $this->assertSame('optimal', $response->status);
        // s1 sits c1 and c3: on different days. c2 can share a day with either.
        $this->assertNotSame($sessions['c1']['date'], $sessions['c3']['date']);
        $this->assertEmpty($response->diagnostics['soft']);
    }

    public function test_a_requested_time_clashing_for_shared_candidates_is_refused(): void
    {
        $response = $this->generate($this->payload([
            'soft_constraints' => ['course_requested_slot' => [
                ['course_id' => 'c1', 'date' => '2026-12-07', 'start_time' => '08:00', 'end_time' => '10:00'],
                ['course_id' => 'c3', 'date' => '2026-12-07', 'start_time' => '09:00', 'end_time' => '11:00'],
            ]],
            'hard_constraints' => [
                'operational_period' => ['start_time' => '08:00', 'end_time' => '14:00'],
                'session_duration' => ['duration' => 120],
            ],
        ]));
        $sessions = $this->sessions($response);

        $this->assertCount(3, $sessions);
        $this->assertFalse($this->overlap($sessions['c1'], $sessions['c3']));
        $this->assertNotEmpty($response->diagnostics['soft']);
    }
}
