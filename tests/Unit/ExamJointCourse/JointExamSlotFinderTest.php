<?php

namespace Tests\Unit\ExamJointCourse;

use App\Services\ExamJointCourse\Support\JointExamSlotFinder;
use PHPUnit\Framework\TestCase;

class JointExamSlotFinderTest extends TestCase
{
    private function input(array $overrides = []): array
    {
        return array_replace([
            'dates' => ['2099-12-07' => [480, 720], '2099-12-08' => [480, 720]], // 08:00 – 12:00
            'student_busy' => [],
            'halls' => [
                ['hall_id' => 'big', 'hall_name' => 'Big', 'capacity' => 100, 'busy' => []],
                ['hall_id' => 'small', 'hall_name' => 'Small', 'capacity' => 30, 'busy' => []],
            ],
            'invigilators' => [
                ['key' => 'p1', 'name' => 'Ada', 'teaches' => false, 'busy' => []],
                ['key' => 'p2', 'name' => 'Ben', 'teaches' => true, 'busy' => []],
            ],
            'candidates' => 90,
        ], $overrides);
    }

    public function test_offers_every_time_that_fits_the_window(): void
    {
        $options = (new JointExamSlotFinder($this->input()))->options(120);

        // 08:00, 08:30, 09:00, 09:30, 10:00 on two dates.
        $this->assertCount(10, $options);
        $this->assertSame(['2099-12-07', '08:00', '10:00'], [$options[0]['date'], $options[0]['start_time'], $options[0]['end_time']]);
    }

    public function test_never_offers_a_time_the_students_already_sit_an_exam(): void
    {
        $options = (new JointExamSlotFinder($this->input([
            'student_busy' => ['2099-12-07' => [['start' => 540, 'end' => 660, 'label' => 'Algorithms']]], // 09:00 – 11:00
        ])))->options(120);

        foreach ($options as $option) {
            $this->assertNotSame('2099-12-07', $option['date']);
        }
        // The date without other exams is preferred.
        $this->assertSame('2099-12-08', $options[0]['date']);
    }

    public function test_needs_seats_for_every_linked_specialty_together(): void
    {
        $finder = new JointExamSlotFinder($this->input([
            'halls' => [['hall_id' => 'big', 'hall_name' => 'Big', 'capacity' => 100, 'busy' => [
                '2099-12-07' => [['start' => 480, 'end' => 600, 'seats' => 50, 'shareable' => true]],
            ]]],
        ]));

        $check = $finder->check('2099-12-07', 480, 600);
        $this->assertFalse($check['ok']);
        $this->assertSame('seats', $check['problems'][0]['type']);
        $this->assertTrue($finder->check('2099-12-08', 480, 600)['ok']);
    }

    public function test_teachers_of_the_course_do_not_count_as_invigilators(): void
    {
        $finder = new JointExamSlotFinder($this->input([
            'invigilators' => [['key' => 'p2', 'name' => 'Ben', 'teaches' => true, 'busy' => []]],
        ]));

        $check = $finder->check('2099-12-07', 480, 600);
        $this->assertFalse($check['ok']);
        $this->assertSame('invigilators', $check['problems'][0]['type']);
        $this->assertStringContainsString('teach the course', $check['problems'][0]['message']);
        $this->assertSame([], $finder->options(120));
    }

    public function test_dates_outside_the_shared_exam_period_are_refused(): void
    {
        $check = (new JointExamSlotFinder($this->input()))->check('2099-12-09', 480, 600);

        $this->assertFalse($check['ok']);
        $this->assertSame('date', $check['problems'][0]['type']);
    }
}
