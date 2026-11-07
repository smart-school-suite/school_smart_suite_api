<?php

namespace Tests\Unit\ExamTimetable;

use App\Schedular\ExamTimetable\DTO\DiagnosticDTO;
use App\Schedular\ExamTimetable\DTO\ResponseDTO;
use App\Schedular\ExamTimetable\Engine\SchedularEngine;
use App\Schedular\ExamTimetable\Solution\Support\RequestShape;
use App\Services\ExamTimetable\ExamApplySolutionService;
use Tests\TestCase;

/**
 * Solution paths: offered for what failed, verified by re-running the
 * scheduler, and applicable to the generation request.
 */
class ExamTimetableSolutionTest extends TestCase
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
                ['course_id' => 'c1', 'course_title' => 'Algorithms'],
                ['course_id' => 'c2', 'course_title' => 'Databases'],
                ['course_id' => 'c3', 'course_title' => 'Networks'],
            ],
            'halls' => [
                ['hall_id' => 'h-a', 'hall_name' => 'Hall A', 'capacity' => 30],
                ['hall_id' => 'h-b', 'hall_name' => 'Hall B', 'capacity' => 40],
            ],
            'invigilators' => [
                ['invigilator_id' => 'i1', 'name' => 'Ada', 'courses_taught' => ['c1']],
                ['invigilator_id' => 'i2', 'name' => 'Ben', 'courses_taught' => ['c2']],
                ['invigilator_id' => 'i3', 'name' => 'Cy', 'courses_taught' => []],
            ],
            'hard_constraints' => [
                'operational_period' => ['start_time' => '08:00', 'end_time' => '17:00', 'date_exclusions' => ['2026-12-10']],
                'session_duration' => ['duration' => 120],
            ],
            'soft_constraints' => [],
        ], $overrides);
    }

    private function generate(array $payload): ResponseDTO
    {
        return app(SchedularEngine::class)->run($payload);
    }

    private function paths(ResponseDTO $response): array
    {
        return collect($response->suggestions)->flatten(1)->all();
    }

    private function diagnostics(ResponseDTO $response): array
    {
        return [
            'hard' => $response->diagnostics['hard']->map(fn(DiagnosticDTO $d) => $d->toArray())->all(),
            'soft' => $response->diagnostics['soft']->map(fn(DiagnosticDTO $d) => $d->toArray())->all(),
        ];
    }

    /** Applies a path to the request the way the apply endpoint does, then generates again. */
    private function applyAndGenerate(array $payload, ResponseDTO $response, array $path): ResponseDTO
    {
        $service = app(ExamApplySolutionService::class);
        [$request, $changes] = $service->assess(RequestShape::fromInput($payload), $path, $service->editFor($this->diagnostics($response)));

        $this->assertTrue(collect($changes)->every(fn($change) => $change['applied']), json_encode($changes));

        return $this->generate(RequestShape::intoInput($payload, $request));
    }

    public function test_an_optimal_timetable_has_no_solutions(): void
    {
        $response = $this->generate($this->payload());

        $this->assertSame('optimal', $response->status);
        $this->assertSame([], $response->suggestions);
    }

    public function test_clashing_course_requests_get_verified_moves_that_work_when_applied(): void
    {
        $payload = $this->payload(['soft_constraints' => ['course_requested_slot' => [
            ['course_id' => 'c1', 'date' => '2026-12-08', 'start_time' => '09:00', 'end_time' => '11:00'],
            ['course_id' => 'c2', 'date' => '2026-12-08', 'start_time' => '10:00', 'end_time' => '12:00'],
        ]]]);
        $response = $this->generate($payload);
        $paths = $this->paths($response);

        $this->assertSame('partial', $response->status);
        $this->assertArrayHasKey('2026-12-08', $response->suggestions);
        $this->assertNotEmpty($paths);
        $this->assertTrue($paths[0]['verified']);
        $this->assertSame('move', $paths[0]['steps'][0]['action']);

        $fixed = $this->applyAndGenerate($payload, $response, $paths[0]);
        $this->assertSame('optimal', $fixed->status);
    }

    public function test_assignment_with_a_small_hall_and_the_course_teacher_gets_both_fixed(): void
    {
        $payload = $this->payload(['soft_constraints' => ['requested_assignment' => [[
            'course_id' => 'c1', 'date' => '2026-12-08', 'start_time' => '09:00', 'end_time' => '11:00',
            'hall_ids' => ['h-a'], 'invigilator_ids' => ['i1'],
        ]]]]);
        $response = $this->generate($payload);
        $best = $this->paths($response)[0];

        $this->assertTrue($best['verified']);
        $this->assertSame(['modify', 'replace'], array_column($best['steps'], 'action'));
        // The replacement never is the course's own teacher.
        $this->assertNotSame('i1', $best['steps'][1]['result']['invigilator_id']);

        $fixed = $this->applyAndGenerate($payload, $response, $best);
        $this->assertSame('optimal', $fixed->status);
        $session = collect($fixed->timetable)->flatMap(fn($day) => $day['slots'])->firstWhere('course_id', 'c1');
        $this->assertSame('requested_assignment', $session['source']);
    }

    public function test_exam_longer_than_every_exam_day_gets_shorter_exam_or_longer_day(): void
    {
        $payload = $this->payload(['hard_constraints' => ['session_duration' => [
            'course_exceptions' => [['course_id' => 'c3', 'duration' => 600]],
        ]]]);
        $response = $this->generate($payload);
        $paths = $this->paths($response);
        $scopes = collect($paths)->map(fn($path) => $path['steps'][0]['result']['scope'] ?? null)->all();

        $this->assertSame('error', $response->status);
        $this->assertArrayHasKey('general', $response->suggestions);
        $this->assertContains('course_duration', $scopes);
        $this->assertContains('date_hours', $scopes);
        $this->assertTrue(collect($paths)->every(fn($path) => $path['verified'] === true));

        foreach ($paths as $path) {
            $this->assertNotSame('error', $this->applyAndGenerate($payload, $response, $path)->status, $path['summary']);
        }
    }

    public function test_daily_maximum_breach_offers_a_higher_maximum_on_that_date(): void
    {
        $payload = $this->payload([
            'end_date' => '2026-12-08',
            'soft_constraints' => ['student_daily_load_range' => ['min_sessions' => 1, 'max_sessions' => 1]],
        ]);
        $response = $this->generate($payload);
        $best = $this->paths($response)[0];

        $this->assertSame('relax', $best['steps'][0]['action']);
        $this->assertSame(2, $best['steps'][0]['result']['max_sessions']);

        $this->assertSame('optimal', $this->applyAndGenerate($payload, $response, $best)->status);
    }

    public function test_course_only_its_teacher_could_invigilate_gets_manual_steps_and_never_allows_the_teacher(): void
    {
        $payload = $this->payload();
        $payload['invigilators'] = [['invigilator_id' => 'i1', 'name' => 'Ada', 'courses_taught' => ['c1']]];
        $payload['halls'] = [['hall_id' => 'h-big', 'hall_name' => 'Big', 'capacity' => 100]];
        $response = $this->generate($payload);
        $paths = $this->paths($response);

        $this->assertSame('error', $response->status);
        $this->assertNotEmpty($paths);
        foreach ($paths as $path) {
            // Nothing here can be checked automatically: someone has to assign another invigilator.
            $this->assertNull($path['verified']);
            $this->assertContains('review', array_column($path['steps'], 'action'));
        }
    }

    public function test_paths_proved_wrong_by_the_re_run_are_never_offered(): void
    {
        $payload = $this->payload();
        $payload['invigilators'] = [['invigilator_id' => 'i3', 'name' => 'Cy', 'courses_taught' => []]];
        $response = $this->generate($payload);

        foreach ($this->paths($response) as $path) {
            $this->assertNotFalse($path['verified'], $path['summary']);
        }
    }
}
